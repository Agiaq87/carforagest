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
require_once 'classes/Ssh2Checker.php';

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
        if (!$this->prepareConfiguration()) {
            $this->errors[] = $this->l('Impossibile creare le configurazioni');
        }

        if (!$this->installTab()) {
            $this->errors[] = $this->l('Impossibile creare la tab');
        }


        return parent::install()
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('moduleRoutes');
    }

    protected function prepareConfiguration(): bool
    {
        Configuration::set(CONFIGURATION_HOST_KEY, '');
        Configuration::set(CONFIGURATION_PORT_KEY, '');
        Configuration::set(CONFIGURATION_USER_KEY, '');
        Configuration::set(CONFIGURATION_PASS_KEY, '');
        Configuration::set(CONFIGURATION_REQUIRED_SSH_TUNNEL, 0);
        Configuration::set(CONFIGURATION_SSH_TUNNEL_HOST, '');
        Configuration::set(CONFIGURATION_SSH_TUNNEL_PORT, '');
        Configuration::set(CONFIGURATION_SSH_TUNNEL_USER, '');
        Configuration::set(CONFIGURATION_SSH_TUNNEL_PASS, '');

        $returnedValue = (
            Configuration::hasKey(CONFIGURATION_HOST_KEY) &&
            Configuration::hasKey(CONFIGURATION_PORT_KEY) &&
            Configuration::hasKey(CONFIGURATION_USER_KEY) &&
            Configuration::hasKey(CONFIGURATION_PASS_KEY) &&
            Configuration::hasKey(CONFIGURATION_REQUIRED_SSH_TUNNEL) &&
            Configuration::hasKey(CONFIGURATION_SSH_TUNNEL_HOST) &&
            Configuration::hasKey(CONFIGURATION_SSH_TUNNEL_PORT) &&
            Configuration::hasKey(CONFIGURATION_SSH_TUNNEL_USER) &&
            Configuration::hasKey(CONFIGURATION_SSH_TUNNEL_PASS)
        );

        if (!$returnedValue) {
            $this->errors[] = $this->l('Impossibile creare le configurazioni');
            return false;
        }

        return true;
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
        $this->context->controller->addCSS($this->_path . 'views/css/admin_loader_carforagest.css', 'all');
        $this->context->controller->addJS($this->_path . 'views/js/admin_carforagest.js');
        $this->context->controller->addJS($this->_path . 'views/js/ajax_carfora_gest.js');
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
            Configuration::deleteByName(CONFIGURATION_PORT_KEY) &&
            Configuration::deleteByName(CONFIGURATION_USER_KEY) &&
            Configuration::deleteByName(CONFIGURATION_PASS_KEY) &&
            Configuration::deleteByName(CONFIGURATION_REQUIRED_SSH_TUNNEL) &&
            Configuration::deleteByName(CONFIGURATION_SSH_TUNNEL_HOST) &&
            Configuration::deleteByName(CONFIGURATION_SSH_TUNNEL_PORT) &&
            Configuration::deleteByName(CONFIGURATION_SSH_TUNNEL_USER) &&
            Configuration::deleteByName(CONFIGURATION_SSH_TUNNEL_PASS);
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $phpSsh2 = Tools::getValue(PHP_SSH2_INSTALLED);
            $host = Tools::getValue(CONFIGURATION_HOST_KEY);
            $db = Tools::getValue(CONFIGURATION_PORT_KEY);
            $user = Tools::getValue(CONFIGURATION_USER_KEY);
            $pass = Tools::getValue(CONFIGURATION_PASS_KEY);
            $sshTunnel = Tools::getValue(CONFIGURATION_REQUIRED_SSH_TUNNEL);
            $sshHost = Tools::getValue(CONFIGURATION_SSH_TUNNEL_HOST);
            $sshPort = Tools::getValue(CONFIGURATION_SSH_TUNNEL_PORT);
            $sshUser = Tools::getValue(CONFIGURATION_SSH_TUNNEL_USER);
            $sshPass = Tools::getValue(CONFIGURATION_SSH_TUNNEL_PASS);

            if (
                $this->updateConfiguration([
                    PHP_SSH2_INSTALLED => $phpSsh2,
                    CONFIGURATION_HOST_KEY => $host,
                    CONFIGURATION_PORT_KEY => $db,
                    CONFIGURATION_USER_KEY => $user,
                    CONFIGURATION_PASS_KEY => $pass,
                    CONFIGURATION_REQUIRED_SSH_TUNNEL => $sshTunnel,
                    CONFIGURATION_SSH_TUNNEL_HOST => $sshHost,
                    CONFIGURATION_SSH_TUNNEL_PORT => $sshPort,
                    CONFIGURATION_SSH_TUNNEL_USER => $sshUser,
                    CONFIGURATION_SSH_TUNNEL_PASS => $sshPass,
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

        if (isset($values[CONFIGURATION_PORT_KEY])) {
            Configuration::updateValue(CONFIGURATION_PORT_KEY, $values[CONFIGURATION_PORT_KEY]);
        }

        if (isset($values[CONFIGURATION_USER_KEY])) {
            Configuration::updateValue(CONFIGURATION_USER_KEY, $values[CONFIGURATION_USER_KEY]);
        }

        if (isset($values[CONFIGURATION_PASS_KEY])) {
            Configuration::updateValue(CONFIGURATION_PASS_KEY, $values[CONFIGURATION_PASS_KEY]);
        }

        if (isset($values[CONFIGURATION_REQUIRED_SSH_TUNNEL])) {
            Configuration::updateValue(CONFIGURATION_REQUIRED_SSH_TUNNEL, $values[CONFIGURATION_REQUIRED_SSH_TUNNEL]);
        }

        if (isset($values[CONFIGURATION_SSH_TUNNEL_HOST])) {
            Configuration::updateValue(CONFIGURATION_SSH_TUNNEL_HOST, $values[CONFIGURATION_SSH_TUNNEL_HOST]);
        }

        if (isset($values[CONFIGURATION_SSH_TUNNEL_PORT])) {
            Configuration::updateValue(CONFIGURATION_SSH_TUNNEL_PORT, $values[CONFIGURATION_SSH_TUNNEL_PORT]);
        }

        if (isset($values[CONFIGURATION_SSH_TUNNEL_USER])) {
            Configuration::updateValue(CONFIGURATION_SSH_TUNNEL_USER, $values[CONFIGURATION_SSH_TUNNEL_USER]);
        }

        if (isset($values[CONFIGURATION_SSH_TUNNEL_PASS])) {
            Configuration::updateValue(CONFIGURATION_SSH_TUNNEL_PASS, $values[CONFIGURATION_SSH_TUNNEL_PASS]);
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
                        'icon' => 'icon-book',
                        'title' => $this->l('Libreria PhpSsh2'),
                    ],
                        'input' => [
                            [
                                'type' => 'switch',
                                'label' => $this->l('Attiva'),
                                'desc' => $this->l('Abilita o disabilita la libreria'),
                                'name' => PHP_SSH2_INSTALLED,
                                'is_bool' => true,
                                'disabled' => true,
                                'values' => [
                                    [
                                        'id' => PHP_SSH2_INSTALLED.'_on',
                                        'value' => 1,
                                        'label' => $this->l('Sì')
                                    ],
                                    [
                                        'id' => PHP_SSH2_INSTALLED.'_off',
                                        'value' => 0,
                                        'label' => $this->l('No')
                                    ]
                                ]
                            ]
                        ],
                    'submit' => [
                        'title' => $this->l('Salva'),
                        'class' => 'btn btn-default pull-right',
                    ]
                ]
            ],
            1 => [
                'form' => [
                    'legend' => [
                        'title' => $this->l('Impostazioni connessioni DB'),
                        'icon' => 'icon-random',
                    ],
                    'input' => [
                        [
                            'type' => 'text',
                            'label' => $this->l('Hostname/IP:'),
                            'name' => CONFIGURATION_HOST_KEY,
                            'size' => 100,
                            'required' => true
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Porta:'),
                            'name' => CONFIGURATION_PORT_KEY,
                            'size' => 100,
                            'required' => true
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('User:'),
                            'name' => CONFIGURATION_USER_KEY,
                            'size' => 100,
                            'required' => true
                        ],
                        [
                            'type' => 'password',
                            'label' => $this->l('Password:'),
                            'name' => CONFIGURATION_PASS_KEY,
                            'size' => 100,
                            'required' => true
                        ],
                    ],
                    'submit' => [
                        'title' => $this->l('Salva dati DB'),
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'fa-solid fa-floppy-disk'
                    ]
                ]
            ],
            2 => [
                'form' => [
                    'legend' => [
                        'title' => $this->l('Tunnel SSH'),
                        'icon' => 'icon-exchange',
                    ],
                    'input' => [
                        [
                            'type' => 'hidden',
                            'name' => 'ssh_tunnel_configuration'
                        ],
                        [
                            'type' => 'switch',
                            'label' => $this->l('Abilita SSH tunnel'),
                            'name' => CONFIGURATION_REQUIRED_SSH_TUNNEL,
                            'is_bool' => true,
                            'on_change' => 'setupInput',
                            'values' => [
                                [
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('Sì')
                                ],
                                [
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('No')
                                ],
                            ]
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Host SSH:'),
                            'name' => CONFIGURATION_SSH_TUNNEL_HOST,
                            'size' => 100,
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Porta SSH:'),
                            'name' => CONFIGURATION_SSH_TUNNEL_PORT,
                            'size' => 100,
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('User SSH:'),
                            'name' => CONFIGURATION_SSH_TUNNEL_USER,
                            'size' => 100,
                        ],
                        [
                            'type' => 'password',
                            'label' => $this->l('Password SSH:'),
                            'name' => CONFIGURATION_SSH_TUNNEL_PASS,
                            'size' => 100,
                        ],
                    ],
                    'submit' => [
                        'title' => $this->l('Salva dati tunnel SSH'),
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'fa-solid fa-floppy-disk'
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
        $helper->fields_value[PHP_SSH2_INSTALLED] = $configurationValues[PHP_SSH2_INSTALLED] ?: 0;
        $helper->fields_value[CONFIGURATION_HOST_KEY] = $configurationValues[CONFIGURATION_HOST_KEY];
        $helper->fields_value[CONFIGURATION_PORT_KEY] = $configurationValues[CONFIGURATION_PORT_KEY];
        $helper->fields_value[CONFIGURATION_USER_KEY] = $configurationValues[CONFIGURATION_USER_KEY];
        $helper->fields_value[CONFIGURATION_PASS_KEY] = $configurationValues[CONFIGURATION_PASS_KEY];
        $helper->fields_value[CONFIGURATION_REQUIRED_SSH_TUNNEL] = $configurationValues[CONFIGURATION_REQUIRED_SSH_TUNNEL];
        $helper->fields_value[CONFIGURATION_SSH_TUNNEL_HOST] = $configurationValues[CONFIGURATION_SSH_TUNNEL_HOST];
        $helper->fields_value[CONFIGURATION_SSH_TUNNEL_PORT] = $configurationValues[CONFIGURATION_SSH_TUNNEL_PORT];
        $helper->fields_value[CONFIGURATION_SSH_TUNNEL_USER] = $configurationValues[CONFIGURATION_SSH_TUNNEL_USER];
        $helper->fields_value[CONFIGURATION_SSH_TUNNEL_PASS] = $configurationValues[CONFIGURATION_SSH_TUNNEL_PASS];
        print_r($configurationValues);

        return $helper->generateForm($fieldForm);
    }

    protected function getConfigurationValues(): array
    {
        return [
            PHP_SSH2_INSTALLED => Ssh2Checker::isSsh2LibraryInstalled(),
            CONFIGURATION_HOST_KEY => Configuration::get(CONFIGURATION_HOST_KEY),
            CONFIGURATION_PORT_KEY => Configuration::get(CONFIGURATION_PORT_KEY),
            CONFIGURATION_USER_KEY => Configuration::get(CONFIGURATION_USER_KEY),
            CONFIGURATION_PASS_KEY => Configuration::get(CONFIGURATION_PASS_KEY),
            CONFIGURATION_REQUIRED_SSH_TUNNEL => Configuration::get(CONFIGURATION_REQUIRED_SSH_TUNNEL),
            CONFIGURATION_SSH_TUNNEL_HOST => Configuration::get(CONFIGURATION_SSH_TUNNEL_HOST),
            CONFIGURATION_SSH_TUNNEL_PORT => Configuration::get(CONFIGURATION_SSH_TUNNEL_PORT),
            CONFIGURATION_SSH_TUNNEL_USER => Configuration::get(CONFIGURATION_SSH_TUNNEL_USER),
            CONFIGURATION_SSH_TUNNEL_PASS => Configuration::get(CONFIGURATION_SSH_TUNNEL_PASS),
        ];
    }
}