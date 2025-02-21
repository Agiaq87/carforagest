<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'carforagest/classes/FileUtils.php';
require_once _PS_MODULE_DIR_ . 'carforagest/classes/ManufacturerImporter.php';

class CarforaGestAdminController extends ModuleAdminController
{
    private array $modalities = ['reset', 'db', 'csv']; // Questo individua se importare con il CSV o con IL DB
    private int $currentModalities = 0;
    private array $step = ['dashboard', 'importer', 'progress'];
    private int $currentStep = 0;
    private array $arguments = ['Reset', /*'Fornitori',*/ 'Marchi', 'Prodotti'];
    private int $currentArgument = 0;
    private array $maxColumnsInFile;
    private array $extractedData = array();
    private FileUtils $fileUtils;
    private ManufacturerImporter $manufacturerImporter;
    public function __construct()
    {
        $this->bootstrap = true;

        // Rimuovi i pulsanti di default
        $this->show_toolbar = false;
        $this->list_no_link = true;

        // Rimuovi la lista predefinita se presente
        $this->list_no_link = true;
        $this->table = false;
        // Classi di utilità
        $this->fileUtils = new FileUtils();
        $this->languages = Language::getLanguages(false);
        $this->shop = Shop::getShops(true);
        $this->db = Db::getInstance();
        $this->manufacturerImporter = new ManufacturerImporter($this->languages, $this->shop);

        $this->maxColumnsInFile = [
            $this->arguments[0] => 0,
            $this->arguments[1] => 0, // Fornitori
            $this->arguments[2] => 5, // Marchi
            $this->arguments[3] => 12
        ];
        //
        parent::__construct();
    }

    public function initContent()
    {
        print_r(['Step' => $this->currentStep, 'MODE' => $this->currentModalities, 'ARG' =>$this->currentArgument]);
        switch ($this->currentStep) {
            default: {
                $this->content = $this->displayDefaultButtons();
                break;
            }
            case 1: {
                $this->chooseImporter(); // QUi si carica il file
                break;
            }
            case 2: {
                $this->content = $this->displayProgress(); // Il file è stato caricato, estraggo i dati e preparo il tutto per i listener
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
        // Vai dalla dashboard alla sezione di import
        if (Tools::isSubmit(NEXT_STEP_BUTTON)) {
            $this->currentArgument = array_search(Tools::getValue('import_argument'), $this->arguments);
            $this->currentModalities = array_search(Tools::getValue('import_modality'), $this->modalities);
            $this->currentStep = array_search(Tools::getValue('import_step'), $this->step);
            print_r($this->currentArgument);
            print_r($this->currentModalities);
            print_r($this->currentStep);
            $this->currentStep+=1;
        }

        // pROCEDI CON I LISTENER
        if (Tools::isSubmit(START_IMPORT)) {
            print_r('start_import');
            $result = $this->importManufacturers($this->extractedData);
            $this->handleResult($result);
            $this->reset();
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

    private function displayDefaultButtons(): string
    {
        //print_r("DISPLAY_DEFAULT_FORM");
        $this->context->smarty->assign([
            'url' => $this->context->link->getAdminLink('CarforaGestAdmin'),
            'token' => $this->token,
            'selection' => [$this->arguments[1], $this->arguments[2], $this->arguments[3]],
            'nextState' => NEXT_STEP_BUTTON,
            'step' => $this->step[0],
        ]);

        return $this->context->smarty->fetch('module:carforagest/views/templates/admin/import_buttons.tpl');
    }

    private function displayCsvForm(): string
    {
        $sql = '';
        switch ($this->currentArgument) {
            case 1: { // Fornitori
                break;
            }
            case 2: { // Marchi
                $sql = SQL_MANUFACTURER;
                break;
            }
            case 3: { // Prodotti
                break;
            }
        }
        $this->context->smarty->assign([
            'url' => $this->context->link->getAdminLink('CarforaGestAdmin'),
            'token' => $this->token,
            'argument' => $this->arguments[$this->currentArgument],
            'mode' => $this->modalities[$this->currentModalities],
            'step' => $this->step[$this->currentStep],
            'sql' => $sql,
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

    private function displayProgress(): string
    {
        $mode = '';
        //print_r("DISPLAY_PROGRESS");

        $this->context->smarty->assign([
            'url' => $this->context->link->getAdminLink('CarforaGestAdmin'),
            'token' => $this->token,
            'mode' => $this->modalities[$this->currentModalities],
            'argument' => $this->arguments[$this->currentArgument],
            'step' => $this->step[$this->currentStep],
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
        $result = $this->manufacturerImporter->importManufacturers($this->extractedData);
        $this->handleResult($result);
    }

    private function reset()
    {
        $this->currentStep = 0;
        $this->currentModalities = 0;
        $this->currentArgument = 0;
    }

    private function chooseImporter()
    {
        switch ($this->currentModalities) {
            // DB
            case 1: {
                $this->content = $this->displayDbForm();
                break;
            }
            // CSV
            case 2: {
                $this->content = $this->displayCsvForm();
                break;
            }
            default: {
                $this->reset();
                $this->content = $this->displayDefaultButtons();
                break;
            }
        }
    }

    private function chooseActionInProgress()
    {
        if ($this->currentModalities === $this->modalities[1]) {
            // DB
        } else {
            $this->handleFileImport();
            if (empty($this->extractedData)) {
                $this->handleResult(new CarforaGestResult(false, "Nessun file caricato", null));
                return;
            }

        }
    }
}