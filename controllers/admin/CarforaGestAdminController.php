<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'carforagest/classes/FileUtils.php';
require_once _PS_MODULE_DIR_ . 'carforagest/classes/ManufacturerImporter.php';
require_once _PS_MODULE_DIR_ . 'carforagest/classes/Consumer.php';
require_once _PS_MODULE_DIR_ . 'carforagest/classes/CarforaGestState.php';

class CarforaGestAdminController extends ModuleAdminController implements Consumer
{
    private AjaxInfo $lastAjaxInfo;
    private CarforaGestState $state;
    private array $extractedData = array();
    private ManufacturerImporter $manufacturerImporter;
    private FileUtils $fileUtils;
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
        $this->state = new CarforaGestState(); // Lo stato è settato a reset
        $this->manufacturerImporter = new ManufacturerImporter(Language::getLanguages(false));
        $this->manufacturerImporter->setListener([$this, 'handleMessage']);
        $this->fileUtils = new FileUtils();

        $this->lastAjaxInfo = new AjaxInfo(new CarforaGestResult(true, "OK", null), 1, 1);

        //
        parent::__construct();
    }

    public function initContent()
    {
        switch ($this->state->getModalityForSwitch()) {
            case 0: {
                $this->content = $this->displayDefaultButtons();
                break;
            }
            case 1: {
                $this->content = $this->displayDbForm($this->state->getCurrentArgument());
                break;
            }
            case 2: {
                $this->content = $this->displayCsvForm($this->state->getCurrentArgument());
                break;
            }
            case 3: {
                $this->content = $this->displayProgress($this->state->getCurrentArgument());
                $this->handleFileImport(
                    $this->state->getCurrentArgument()
                );
                break;
            }
        }
        /*switch ($this->currentSelectedArgument) {
            case $this->selectionArgument[0]: { // "CSV"
                $this->content = $this->displayCsvForm();
                break;
            }
            case $this->selectionArgument[1]: { // "DB"
                $this->content = $this->displayDbForm();
                break;
            }
            case $this->selectionArgument[2]: { // "FILE"
                $this->content = $this->displayProgress();
                $this->handleFileImport(5, $this->selectionArgument[3]);
                print_r($this->extractedData);
                break;
            }
            default: {
                $this->content = $this->displayDefaultButtons();
                break;
            }
        }*/

        parent::initContent();
    }

    public function postProcess()
    {
        // Gestisci i stati
        // Ritorna alla dashboard sempre
        if (Tools::isSubmit($this->state->getResetState())) {
            $this->state->resetState();
        }
        // Vai dalla dashboard alla selezione
        if (Tools::isSubmit('csv-or-db-chooser')) { // Viene dalla pagina principale
            $this->state->detectState(
                Tools::getValue('import_modality'),
                Tools::getValue('import_modality')
            );
            print_r($this->state->toArray());
        }
        /*// Va alla pagina di import csv
        if (Tools::isSubmit('submit_carforagest_csv')) {
            $this->currentSelectedArgument = $this->selectionArgument[0];
        }
        // Va alla pagina import db
        if (Tools::isSubmit('submit_carforagest_db')) {
            $this->currentSelectedArgument = $this->selectionArgument[1];
        }
        // Va alla pagina di import con la progress bar
        if (Tools::isSubmit('csv_upload')) {
            print_r("submit csv catched");
            $this->currentSelectedArgument = $this->selectionArgument[2];
        }
        // Va alla pagina principale
        if (Tools::isSubmit('submit_carforagest_home')) {
            $this->currentSelectedArgument = null;
        }*/

        // Operazioni con i listener
        if (Tools::isSubmit(SUBMIT_NAME_CSV_MANUFACTURERS_UPLOAD)) {
            if (empty($this->extractedData)) {
                $this->handleResult(new CarforaGestResult(false, "Nessun file caricato", null));
                return;
            }
            $this->manufacturerImporter->importManufacturers($this->extractedData);
        }

        // AJAX
        if (Tools::isSubmit('ajaxCheck')) {
            $this->handleAjax();
        }


        parent::postProcess();
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
        print_r("DISPLAY_DEFAULT_FORM");
        $this->context->smarty->assign([
            'csv' => $this->selectionArgument[0],
            'db' => $this->selectionArgument[1],
            'url' => $this->module->getPathUri(),
            'token' => $this->token,
            'selection' => $this->selectionArgument,
            'previous_button' => ''
        ]);

        return $this->context->smarty->fetch('module:carforagest/views/templates/admin/import_buttons.tpl');
    }

    private function displayCsvForm(): string
    {
        print_r("DISPLAY_CSV_FORM");
        $this->context->smarty->assign([
            'url' => $this->module->getPathUri(),
            'token' => $this->token
        ]);

        return $this->context->smarty->fetch('module:carforagest/views/templates/admin/import_csv.tpl');
    }

    private function displayDbForm(): string
    {
        print_r("DISPLAY_DB_FORM");
        // Qui implementa la logica per il form DB
        // ...
        return '';
    }

    private function displayProgress(): string
    {
        $mode = '';
        $nextButton = '';
        $cancelButton = '';
        print_r("DISPLAY_PROGRESS");

        switch ($this->currentSelectedArgument) {
            case $this->selectionArgument[2]: {
                $mode = 'csv';
                $nextButton = 'Carica i dati estratti dal file CSV';
                $submitName = SUBMIT_NAME_CSV_MANUFACTURERS_UPLOAD;
                $cancelButton = 'Annulla';
                break;
            }
            case $this->selectionArgument[3]: {
                $mode = 'manufacturers';
                break;
            }
        }

        $this->context->smarty->assign([
            'url' => $this->module->getPathUri(),
            'token' => $this->token,
            'mode' => $mode,
            'submit_name' => $submitName,
            'next_button' => $nextButton,
            'cancel_button' => $cancelButton,
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
     * Gestisce i valori da passare con AJAX
     * @param array $info
     * @return void
     */
    public function handleMessage(AjaxInfo $info)
    {
        $this->lastAjaxInfo = $info;
    }

    public function handleAjax()
    {
        die($this->lastAjaxInfo->toJson());
    }

    public function handleFileImport(string $nextStep)
    {
        $result = $this->fileUtils->extractData(
            $this->state->getMaxNumOfColumnsOfFileForArgument()
        );

        if (!$result->status) {
            return $result;
        }

        $this->currentSelectedArgument = $nextStep; // "MANUFACTURERS_CSV"
        $this->extractedData = $result->data;
        print_r($this->extractedData);
        $result = $this->manufacturerImporter->importManufacturers($this->extractedData);
        $this->handleResult($result);
    }
}