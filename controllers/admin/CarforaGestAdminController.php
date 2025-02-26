<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'carforagest/classes/AjaxInfo.php';
require_once _PS_MODULE_DIR_ . 'carforagest/classes/FileUtils.php';
require_once _PS_MODULE_DIR_ . 'carforagest/classes/ManufacturerImporter.php';
require_once _PS_MODULE_DIR_ . 'carforagest/classes/CategoryImporter.php';
require_once _PS_MODULE_DIR_ . 'carforagest/classes/CacheManager.php';

class CarforaGestAdminController extends ModuleAdminController
{
    private array $modalities = ['reset', 'db', 'csv']; // Questo individua se importare con il CSV o con IL DB
    private int $currentModalities = 0;
    private array $step = ['dashboard', 'importer', 'progress'];
    private int $currentStep = 0;
    private array $arguments = ['Reset', 'Marchi', 'Prodotti', 'Categorie'];
    private int $currentArgument = 0;
    private array $maxColumnsInFile;
    private array $extractedData = array();
    private FileUtils $fileUtils;
    private ManufacturerImporter $manufacturerImporter;
    private CategoryImporter $categoryImporter;
    private AjaxInfo $lastAjaxInfo;
    private CacheManager $cacheManager;

    public function __construct()
    {
        $this->bootstrap = true;

        // Rimuovi i pulsanti di default
        $this->show_toolbar = false;
        $this->list_no_link = true;

        // Rimuovi la lista predefinita se presente
        $this->list_no_link = true;
        $this->table = false;
        $this->lastAjaxInfo = new AjaxInfo(
            'CarforaGest',
            0,
            0,
            true
        );
        // Classi di utilità
        $this->fileUtils = new FileUtils();
        $this->languages = Language::getLanguages(false);
        $this->shop = Shop::getShops(true);
        $this->manufacturerImporter = new ManufacturerImporter($this->languages, $this->shop);
        $this->categoryImporter = new CategoryImporter($this->languages, $this->shop);
        $this->cacheManager = new CacheManager();

        $this->maxColumnsInFile = [
            $this->arguments[0] => 0,
            $this->arguments[1] => 5, // Marchi
            $this->arguments[2] => 12, // Prodotti
            $this->arguments[3] => 8, // Categorie
        ];
        //
        parent::__construct();
    }

    public function initContent()
    {
        print_r(['Step' => $this->currentStep, 'MODE' => $this->currentModalities, 'ARG' =>$this->currentArgument]);

        $url = $this->context->link->getAdminLink('CarforaGestAdmin');
        $token = $this->token;

        switch ($this->currentStep) {
            default: {
                $this->content = $this->displayDefaultButtons($url, $token);
                break;
            }
            case 1: { // QUi si carica il content con le informazioni da mostrare
                $this->chooseImporterContentFromArgument($url, $token);
                break;
            }
            case 2: {
                $this->content = $this->displayProgress($url, $token); // Il file è stato caricato, estraggo i dati e procedo con l'inserimento
                $this->chooseActionInProgress();
                break;
            }
        }

        parent::initContent();
    }

    public function postProcess()
    {
        // Gestisci i stati
        // Ritorna alla dashboard sempre
        if (Tools::isSubmit(DASHBOARD_BUTTON)) {
            $this->reset();
        }

        if (Tools::isSubmit(AJAX_CHECK)) {
            $this->handleAjax();
        }

        // Vai dalla dashboard alla sezione di import
        if (Tools::isSubmit(NEXT_STEP_BUTTON)) {
            $this->currentArgument = array_search(Tools::getValue('import_argument'), $this->arguments);
            $this->currentModalities = array_search(Tools::getValue('import_modality'), $this->modalities);
            $this->currentStep = array_search(Tools::getValue('import_step'), $this->step);
            print_r($this->currentArgument);
            print_r($this->currentModalities);
            print_r($this->currentStep);
            if ($this->currentStep >= 2) {
                $this->currentStep = 0;
            } else {
                $this->currentStep++;
            }
        }

        return parent::postProcess();
    }

    private function handleResult(CarforaGestResult $result)
    {
        $message = $this->l($result->message);
        if (!$result-> status) {
            $this->errors[] = $message;
            PrestaShopLogger::addLog(
                $message,
                3
            );
        } else {
            $this->confirmations[] = $message;
        }
    }

    private function displayDefaultButtons($url, $token): string
    {
        //print_r("DISPLAY_DEFAULT_FORM");
        $this->context->smarty->assign([
            'url' => $url,
            'token' => $token,
            'selection' => [$this->arguments[1], $this->arguments[2], $this->arguments[3]],
            'nextState' => NEXT_STEP_BUTTON,
            'step' => $this->step[0],
        ]);

        return $this->context->smarty->fetch('module:carforagest/views/templates/admin/import_buttons.tpl');
    }

    private function displayCsvForm($sql, $panelHeading, $steps, $url, $token): string
    {
        $this->context->smarty->assign([
            'url' => $url,
            'token' => $token,
            'argument' => $this->arguments[$this->currentArgument],
            'mode' => $this->modalities[$this->currentModalities],
            'step' => $this->step[$this->currentStep],
            'warningSteps' => $steps,
            'sql' => $sql,
            'panelHeading' => $panelHeading,
            'nextState' => NEXT_STEP_BUTTON
        ]);

        return $this->context->smarty->fetch('module:carforagest/views/templates/admin/import_csv.tpl');
    }

    private function displayDbForm(): string
    {
        //print_r("DISPLAY_DB_FORM");
        // Qui implementa la logica per il form DB
        // ...
        return '';
    }

    private function displayProgress($url, $token): string
    {
        $sql = SQL_PRODUCT;

        $this->context->smarty->assign([
            'url' => $url,
            'token' => $token,
            'mode' => $this->modalities[$this->currentModalities],
            'argument' => $this->arguments[$this->currentArgument],
            'step' => $this->step[$this->currentStep],
            'data' => json_encode($this->extractedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'baseUrl' => $_SERVER['HTTP_HOST'],
            'apiKey' => $this->cacheManager->retrieveApiKey(),
            'nextState' => NEXT_STEP_BUTTON
        ]);

        return $this->context->smarty->fetch('module:carforagest/views/templates/admin/import_progress.tpl');
    }

    private function processDbImport()
    {
        try {
            // Salva le configurazioni
            Configuration::updateValue('BRAND_IMPORT_DB_HOST', Tools::getValue('db_host'));
            Configuration::updateValue('BRAND_IMPORT_DB_PORT', Tools::getValue('db_port'));
            Configuration::updateValue('BRAND_IMPORT_DB_NAME', Tools::getValue('db_name'));
            Configuration::updateValue('BRAND_IMPORT_DB_USER', Tools::getValue('db_user'));
            Configuration::updateValue('BRAND_IMPORT_DB_PASS', Tools::getValue('db_pass'));
            Configuration::updateValue('BRAND_IMPORT_DB_QUERY', Tools::getValue('db_query'));

            // Esegui l'importazione
            $importer = new BrandImporter();
            $result = $importer->importFromDb([
                'host' => Tools::getValue('db_host'),
                'port' => Tools::getValue('db_port'),
                'name' => Tools::getValue('db_name'),
                'user' => Tools::getValue('db_user'),
                'pass' => Tools::getValue('db_pass'),
                'query' => Tools::getValue('db_query')
            ]);

            if ($result) {
                $this->confirmations[] = $this->l('Importazione completata con successo');
            } else {
                $this->errors = array_merge($this->errors, $importer->getErrors());
            }
        } catch (Exception $e) {
            $this->errors[] = $this->l('Errore durante l\'importazione: ') . $e->getMessage();
        }
    }

    /**
     * Esaurisce il file leggendolo e restituendo un array dei dati estratti
     * @return CarforaGestResult|void
     */

    public function handleFileImport()
    {
        $result = $this->fileUtils->extractData(
            $this->maxColumnsInFile[$this->arguments[$this->currentArgument]],
        );

        if (!$result->status) {
            return $result;
        }

        $this->extractedData = $result->data;
        print_r($this->extractedData);
    }

    /**
     * Gestisce le chiamate Ajax restituendo i valori
     * @return void
     */
    public function handleAjax()
    {
        die (json_encode($this->lastAjaxInfo->toArray()));
    }

    private function reset()
    {
        $this->currentStep = 0;
        $this->currentModalities = 0;
        $this->currentArgument = 0;
    }

    /**
     * Questo metodo si occupa solamente di definire i parametri per il content in base alla modalità registrata
     * NOTA BENE: Questo metodo riguarda solamente il secondo step, ovvero l'import DB o CSV
     * @return void
     */
    private function chooseImporterContentFromArgument($url, $token)
    {
        switch ($this->modalities[$this->currentModalities]) {
            // DB
            case $this->modalities[1]: {    // DB
                $this->content = $this->displayDbForm();
                break;
            }
            // CSV
            case $this->modalities[2]: {
                $sql = '';
                $panelHeading = '';
                $steps = array();
                switch ($this->currentArgument) {
                    case 1: { // Marchi
                        $sql = SQL_MANUFACTURER;
                        $panelHeading = MANUFACTURER_PANEL_HEADING_CSV_IMPORT;
                        $steps = MANUFACTURER_WARNING_STEPS;
                        break;
                    }
                    case 2: { // Prodotti
                        $sql = SQL_PRODUCT;
                        $panelHeading = PRODUCT_PANEL_HEADING_CSV_IMPORT;
                        $steps = PRODUCT_WARNING_STEPS;
                        break;
                    }
                    case 3: { // Categorie
                        $sql = SQL_CATEGORY;
                        $panelHeading = CATEGORY_PANEL_HEADING_CSV_IMPORT;
                        $steps = CATEGORY_WARNING_STEPS;
                        break;
                    }
                }
                $this->content = $this->displayCsvForm($sql, $panelHeading, $steps, $url, $token);
                break;
            }
            case $this->modalities[3]: {

            }
            default: {
                $this->reset();
                $this->content = $this->displayDefaultButtons($url, $token);
                break;
            }
        }
    }

    /**
     * Una volta caricato il file CSV, in questo metodo procedo a leggerlo e inserire i dati nel DB
     *  con gli appositi ObjectModel.
     * @return void
     */
    private function chooseActionInProgress()
    {
        if ($this->currentModalities === $this->modalities[1]) {
            // DB
        } else {
            $this->handleFileImport(); // Gestisce in automatico

            if (empty($this->extractedData)) {
                $this->handleResult(new CarforaGestResult(false, "Nessun file caricato", null));
                return;
            }

            /*switch ($this->arguments[$this->currentArgument]) {
                case $this->arguments[1]: { // Marchi
                    $result = $this->manufacturerImporter->importManufacturers($this->extractedData);
                    $this->handleResult($result);
                    break;
                }
                case $this->arguments[2]: { // Prodotti
                    // TODO
                    break;
                }
                case $this->arguments[3]: { // Categorie
                    $result = $this->categoryImporter->importCategories($this->extractedData);
                    $this->handleResult($result);
                    break;
                }
            }*/
        }
    }
}