<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'carforagest/classes/FileUtils.php';

class CarforaGestAdminController extends ModuleAdminController
{
    public function __construct()
    {
        $this->selection = ["CSV", "DB"];
        $this->selected = null;
        $this->bootstrap = true;

        // Rimuovi i pulsanti di default
        $this->show_toolbar = false;
        $this->list_no_link = true;

        // Rimuovi la lista predefinita se presente
        $this->list_no_link = true;
        $this->table = false;
        parent::__construct();
    }

    public function initContent()
    {
        if ($this->selected === $this->selection[0]) {
            $this->content = $this->displayCsvForm();
        } else if ($this->selected === $this->selection[1]) {
            $this->content = $this->displayDbForm();
        } else {
            $this->content = $this->displayDefaultButtons();
        }

        parent::initContent();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submit_carforagest_csv')) {
            $this->selected = $this->selection[0];
        }

        if (Tools::isSubmit('submit_carforagest_db')) {
            $this->selected = $this->selection[1];
        }

        if (Tools::isSubmit('submit_carforagest_home')) {
            $this->selected = null;
        }

        if (Tools::isSubmit('return')) {
            $this->selected = null;
        }

        if (Tools::isSubmit('csv_upload')) {
            print_r("submit csv catched");
            print_r($_FILES['csv_file']);
            $this->handleResult($this->processCsvImport($_FILES['csv_file']));
        }

        parent::postProcess();
    }

    private function handleResult(array $result)
    {
        if (!$result['status']) {
            $this->errors[] = $this->l($result['error']);
        } else {
            $this->confirmations[] = $this->l($result['message']);
        }
    }

    private function displayDefaultButtons(): string
    {
        print_r("DISPLAY_DEFAULT_FORM");
        $this->context->smarty->assign([
            'csv' => $this->selection[0],
            'db' => $this->selection[1],
            'url' => $this->module->getPathUri(),
            'token' => $this->token
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

    private function processCsvImport(array $file, string $separator = ',', bool $haveHeader = false): array
    {
        if (FileUtils::checkFileExists($file)) {
            return[
                'status' => false,
                'error' => 'Nessun file caricato'
            ];
        }

        if (FileUtils::checkUploadOk($file)) {
            return[
                'status' => false,
                'error' => 'Errore nel caricamento del file'
            ];
        }

        if (FileUtils::checkFileExtension($file, 'csv')) {
            return [
                'status' => false,
                'error' => 'Estensione del file caricato non valida'
            ];
        }

        $file = $file['tmp_name'];
        if (!($handle = fopen($file, 'r'))) {
            return [
                'status' => false,
                'error' => 'Impossibile caricare il file'
            ];
        }

        // Salta l'intestazione se presente
        if ($haveHeader) {
            $headers = fgetcsv($handle);
        }

        while (($data = fgetcsv($handle)) !== false) {
            // Assumiamo che il CSV abbia le colonne: name,active
            $name = isset($data[2]) ? $data[2] : '';
            $active = isset($data[1]) ? (int)$data[1] : 1;

            // Controlla se il manufacturer esiste già
            $manufacturerId = Manufacturer::getIdByName($name);

            if (!$manufacturerId) {
                // Crea nuovo manufacturer
                $manufacturer = new Manufacturer();
                $manufacturer->name = $name;
                //$manufacturer->description = $description;
                $manufacturer->active = $active;

                // Imposta il nome per tutte le lingue del negozio
                $languages = Language::getLanguages(false);
                foreach ($languages as $language) {
                    $manufacturer->name[$language['id_lang']] = $name;
                    //$manufacturer->description[$language['id_lang']] = $description;
                }

                try {
                    print_r($manufacturer);
                    $manufacturer->add();

                    // Genera URL rewrite per tutte le lingue
                    foreach ($languages as $language) {
                        $link_rewrite = Tools::link_rewrite($name);
                        $manufacturer->link_rewrite[$language['id_lang']] = $link_rewrite;
                    }

                    $manufacturer->save();
                } catch (Exception $e) {
                    PrestaShopLogger::addLog(
                        'Error importing manufacturer: ' . $e->getMessage(),
                        3
                    );
                    continue;
                }
            }
        }

        fclose($handle);
        return ['status' => true, 'message' => 'Import dei marchi caricato con successo'];
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
}