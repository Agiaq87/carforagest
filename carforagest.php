<?php
/**
 * @license MIT License
 * @author Alessandro Giaquinto
 * @copyright Alessandro Giaquinto 2024
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once 'classes/constants.php';

class carforagest extends Module
{
    public function __construct()
    {
        $this->name = 'carforagest';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Alessandro Giaquinto';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => '8.9',
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('CarforaGest');
        $this->description = $this->l('Gestisce la comunicazione e la sincronizzazione con il gestionale carforagest');
        $this->confirmUninstall = $this->l('ATTENZIONE: La disinstallazione di questo modulo compromette la sincronizzazione con il gestionale carforagest');
    }

    public function install(): bool
    {
        return $this->prepareConfiguration()
            && $this->installTab()
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('moduleRoutes');
    }

    protected function prepareConfiguration(): bool
    {
        Configuration::set(CONFIGURATION_HOST_KEY, '');
        Configuration::set(CONFIGURATION_DB_KEY, '');
        Configuration::set(CONFIGURATION_USER_KEY, '');
        Configuration::set(CONFIGURATION_PASS_KEY, '');
        return (
            Configuration::hasKey(CONFIGURATION_HOST_KEY) &&
            Configuration::hasKey(CONFIGURATION_DB_KEY) &&
            Configuration::hasKey(CONFIGURATION_USER_KEY) &&
            Configuration::hasKey(CONFIGURATION_PASS_KEY)
        );
    }

    protected function installTab(): bool
    {
        $tab = new Tab();
        $tab->class_name = 'CarforaGestAdmin';
        $tab->module = $this->name;
        $tab->id_parent = (int)Tab::getIdFromClassName('DEFAULT');
        $tab->icon = 'settings';

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            $tab->name[$language['id_lang']] = 'CarforaGest';
        }

        try {
            $tab->save();
        } catch (Exception $e) {
            $this->errors[] = $this->l($e->getMessage());
            return false;
        }

        return true;
    }

    public function hookModuleRoutes($params)
    {
        return [
            'carforagest' => [
                'controller' => 'carforagest',
                'rule' => 'come-raggiungerci',
                'keywords' => [],
                'params' => [
                    'module' => $this->name,
                    'fc' => 'module',
                    'controller' => 'carforagest',
                ],
            ],
        ];
    }

    public function hookDisplayBackOfficeHeader($params): bool
    {
        $this->context->controller->addCSS($this->_path . 'views/css/admin_carforagest.css', 'all');
        //$this->context->controller->addJS($this->_path . 'views/js/admin_carforagest.js');
        //$this->context->controller->addJS($this->_path . 'views/js/ajax_carforagest.js');
        return true;
    }

    public function uninstall(): bool
    {
        return $this->uninstallDB() && $this->uninstallTab() && $this->deleteConfiguration() && parent::uninstall();
    }

    protected function uninstallDB(): bool
    {
        return true;
    }

    protected function uninstallTab(): bool
    {
        $tabId = (int)Tab::getIdFromClassName('CarforaGestAdmin');

        if ($tabId) {
            $tab = new Tab($tabId);
            try {
                $tab->delete();
            } catch (Exception $e) {
                $this->errors[] = $this->l($e->getMessage());
                return false;
            }
        }

        return true;
    }

    protected function deleteConfiguration(): bool
    {
        return Configuration::deleteByName(CONFIGURATION_HOST_KEY) &&
            Configuration::deleteByName(CONFIGURATION_DB_KEY) &&
            Configuration::deleteByName(CONFIGURATION_USER_KEY) &&
            Configuration::deleteByName(CONFIGURATION_PASS_KEY);
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $host = Tools::getValue(CONFIGURATION_HOST_KEY);
            $db = Tools::getValue(CONFIGURATION_DB_KEY);
            $user = Tools::getValue(CONFIGURATION_USER_KEY);
            $pass = Tools::getValue(CONFIGURATION_PASS_KEY);

            if (
                $this->updateConfiguration([
                    CONFIGURATION_HOST_KEY => $host,
                    CONFIGURATION_DB_KEY => $db,
                    CONFIGURATION_USER_KEY => $user,
                    CONFIGURATION_PASS_KEY => $pass
                ])
            ) {
                $output .= $this->displayConfirmation($this->l('Impostazioni aggiornate con successo.'));
            } else {
                $output .= $this->displayError($this->l('I valori inseriti non sono validi'));
            }
        }

        return $output . $this->displayForm();
    }

    protected function updateConfiguration(array $values): bool
    {
        if (empty($values)) {
            return false;
        }

        if (isset($values[CONFIGURATION_HOST_KEY])) {
            Configuration::updateValue(CONFIGURATION_HOST_KEY, $values[CONFIGURATION_HOST_KEY]);
        }

        if (isset($values[CONFIGURATION_DB_KEY])) {
            Configuration::updateValue(CONFIGURATION_DB_KEY, $values[CONFIGURATION_DB_KEY]);
        }

        if (isset($values[CONFIGURATION_USER_KEY])) {
            Configuration::updateValue(CONFIGURATION_USER_KEY, $values[CONFIGURATION_USER_KEY]);
        }

        if (isset($values[CONFIGURATION_PASS_KEY])) {
            Configuration::updateValue(CONFIGURATION_PASS_KEY, $values[CONFIGURATION_PASS_KEY]);
        }

        return true;
    }

    protected function displayForm(): string
    {
        $configurationValues = $this->getConfigurationValues();
        $fieldForm = [
            0 => [
                'form' => [
                    'legend' => [
                        'title' => $this->l('Impostazioni della pagina \"Come raggiungerci\"'),
                    ],
                    'input' => [
                        [
                            'type' => 'text',
                            'label' => $this->l('Inserisci l\'host del server carforagest'),
                            'name' => CONFIGURATION_HOST_KEY,
                            'size' => 100,
                            'required' => true
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Inserisci il DB del server carforagest'),
                            'name' => CONFIGURATION_DB_KEY,
                            'size' => 100,
                            'required' => true
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Inserisci l\'user del server carforagest'),
                            'name' => CONFIGURATION_USER_KEY,
                            'size' => 100,
                            'required' => true
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Inserisci la password del server carforagest'),
                            'name' => CONFIGURATION_PASS_KEY,
                            'size' => 100,
                            'required' => true
                        ]
                    ],
                    'submit' => [
                        'title' => $this->l('Save'),
                        'class' => 'btn btn-default pull-right'
                    ]
                ]
            ]
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->title = $this->displayName;
        $helper->submit_action = 'submit' . $this->name;
        $helper->fields_value[CONFIGURATION_HOST_KEY] = $configurationValues[CONFIGURATION_HOST_KEY];
        $helper->fields_value[CONFIGURATION_DB_KEY] = $configurationValues[CONFIGURATION_DB_KEY];
        $helper->fields_value[CONFIGURATION_USER_KEY] = $configurationValues[CONFIGURATION_USER_KEY];
        $helper->fields_value[CONFIGURATION_PASS_KEY] = $configurationValues[CONFIGURATION_PASS_KEY];


        return $helper->generateForm($fieldForm);
    }

    protected function getConfigurationValues(): array
    {
        return [
            CONFIGURATION_HOST_KEY => Configuration::get(CONFIGURATION_HOST_KEY),
            CONFIGURATION_DB_KEY => Configuration::get(CONFIGURATION_DB_KEY),
            CONFIGURATION_USER_KEY => Configuration::get(CONFIGURATION_USER_KEY),
            CONFIGURATION_PASS_KEY => Configuration::get(CONFIGURATION_PASS_KEY),
        ];
    }
}