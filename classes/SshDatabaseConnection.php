<?php

class SshDatabaseConnection
{
    private $ssh_connection;
    private $tunnel;
    private $db_connection;

    private $ssh_host;
    private $ssh_username;
    private $ssh_password;
    private $ssh_port;

    private $db_host;
    private $db_username;
    private $db_password;
    private $db_name;
    private $db_port;

    public function __construct($config)
    {
        // SSH configuration
        $this->ssh_host = $config['ssh_host'];
        $this->ssh_username = $config['ssh_username'];
        $this->ssh_password = $config['ssh_password'];
        $this->ssh_port = $config['ssh_port'] ?? 22;

        // Database configuration
        $this->db_host = $config['db_host'];
        $this->db_username = $config['db_username'];
        $this->db_password = $config['db_password'];
        $this->db_name = $config['db_name'];
        $this->db_port = $config['db_port'] ?? 3306;

        // Local connection
        $this->localDb = Db::getInstance();
        $result = $this->localDb->getShops();
        $this->shops = $result['status'] === true ? $result['data'] : [];
    }

    public function connect(): array
    {
        try {
            // Establish SSH connection
            $this->ssh_connection = ssh2_connect($this->ssh_host, $this->ssh_port);
            if (!$this->ssh_connection) {
                return [
                    'status' => false,
                    'message' => 'Impossibile stabilire la connessione SSH'
                ];
            }

            // Authenticate
            if (!ssh2_auth_password($this->ssh_connection, $this->ssh_username, $this->ssh_password)) {
                return [
                    'status' => false,
                    'message' => 'Autenticazione SSH fallita'
                    ];
            }

            // Create SSH tunnel
            $this->tunnel = ssh2_tunnel($this->ssh_connection, $this->db_host, $this->db_port);
            if (!$this->tunnel) {
                return [
                    'status' => false,
                    'message' => 'Impossibile creare il tunnel SSH'
                    ];
            }

            // Connect to database through SSH tunnel
            $this->remoteDb = new PDO(
                "mysql:host=127.0.0.1;port={$this->db_port};dbname={$this->db_name}",
                $this->db_username,
                $this->db_password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => true)
            );

            return [
                'status' => true,
                'message' => 'Connessione eseguita'
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'Errore di connessione: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Execute generic query
     * @param $sql
     * @param $params
     * @return array
     */
    public function query($sql, $params = []): array
    {
        try {
            $stmt = $this->remoteDb->prepare($sql);
            $stmt->execute($params);
            return [
                'status' => true,
                'message' => 'Query eseguita',
                'payload' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (PDOException $e) {
            return [
                'status' => false,
                'message' => 'Errore query: ' . $e->getMessage()
            ];
        }
    }

    public function disconnect()
    {
        $this->remoteDb = null;
        if ($this->tunnel) {
            fclose($this->tunnel);
        }
        if ($this->ssh_connection) {
            ssh2_disconnect($this->ssh_connection);
        }
    }

    public function getShops(): array
    {
        $result = $this->localDb->executeS('select * from `' . _DB_PREFIX_ . 'shop`');
        if (empty($result)) {
            return [
                'status' => false,
                'message' => 'Nessun shop non trovato'
            ];
        }
        return [
            'status' => true,
            'data' => $result[0]
        ];
    }

    /**
     * Recupera i marchi dal database remoto monkey
     * @return array
     */
    public function getManufacturerFromRemoteConnection(): array
    {
        return $this->query('select * from `gest_manufacturer`');
    }

    /**
     * @param array $queryResult
     * @return array
     */
    public function insertManufacturerIntoLocalConnection(array $manufacturerResultForRemoteConnection): array
    {
        if ($manufacturerResultForRemoteConnection['status'] === true) {
            $payload = $manufacturerResultForRemoteConnection['payload'];
            // Manufacturer
            $insertIntoManufacturer = [
                'id_manufacturer' => $payload['id'],
                'name' => $payload['name'],
                'date_add' => $payload['dateCreation'],
                'date_upd' => $payload['dateUpdate'],
                'active' => $payload['enabled']
            ];
            $returnedValue = $this->localDb->insert('manufacturer', $insertIntoManufacturer);
            if (!$returnedValue) {
                return [
                    'status' => false,
                    'message' => 'Errore inserimento nella tabella manufacturer'
                ];
            }
            // Manufacturer_lang
            $insertIntoManufacturerLang = [
                'id_manufacturer' => $payload['id'],
                'id_lang' => 1,
                'description' => '',
                'short_description' => $payload['name'],
                'meta_title' => $payload['name'],
                'meta_keywords' => '',
                'meta_description' => ''
            ];
            $returnedValue = $this->localDb->insert('manufacturer_lang', $insertIntoManufacturerLang);
            if (!$returnedValue) {
                return [
                    'status' => false,
                    'message' => 'Errore inserimento nella tabella manufacturer_lang'
                ];
            }

            // Manufacturer_shop
            if (!empty($this->shops)) {
                $insertIntoManufacturerShop = '';
                foreach ($payload['id'] as $manufacturerId) {
                    foreach ($this->shops['id_shop'] as $shopId) {
                        $insertIntoManufacturerShop .= '(' . $manufacturerId . ',' . $shopId . '),';
                    }
                }
                $returnedValue = $this->localDb->execute('insert into `'. _DB_PREFIX_ . 'manufacturer_shop` values ' . $insertIntoManufacturerShop);
                if (!$returnedValue) {
                    return [
                        'status' => false,
                        'message' => 'Errore inserimento nella tabella manufacturer_shop'
                    ];
                }
            }
             return [
                 'status' => true,
                 'message' => 'Manufacturer inserito'
             ];
        }

        return [
            'status' => false,
            'message' => $manufacturerResultForRemoteConnection['message']
        ];
    }

}