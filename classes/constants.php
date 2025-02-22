<?php
const PHP_SSH2_INSTALLED = 'php_ssh2_installed';
const CONFIGURATION_HOST_KEY = 'carforagest_configuration_host';
const CONFIGURATION_PORT_KEY = 'carforagest_configuration_port';
const CONFIGURATION_USER_KEY = 'carforagest_configuration_user';
const CONFIGURATION_PASS_KEY = 'carforagest_configuration_pass';
const CONFIGURATION_REQUIRED_SSH_TUNNEL = 'carforagest_configuration_required_ssh_tunnel';
const CONFIGURATION_SSH_TUNNEL_HOST = 'carforagest_configuration_ssh_tunnel_host';
const CONFIGURATION_SSH_TUNNEL_PORT = 'carforagest_configuration_ssh_tunnel_port';
const CONFIGURATION_SSH_TUNNEL_USER = 'carforagest_configuration_ssh_tunnel_user';
const CONFIGURATION_SSH_TUNNEL_PASS = 'carforagest_configuration_ssh_tunnel_pass';

// ADMIN
const NEXT_STEP_BUTTON = 'next_step_button';
const DASHBOARD_BUTTON = 'dashboard_button';

const START_IMPORT = 'start_import';


const SQL_MANUFACTURER = 'select name,enabled,name as description, name as meta_title, name as meta_keyword from gest_publisher';
const SQL_PRODUCT = 'select name,enabled,name as description, name as meta_title, name as meta_keyword from gest_product';

const SQL_CATEGORY = 'SELECT gc.name, gc.enabled, gc.description, gc.name AS meta_title, CASE WHEN gc.parentId IS NULL THEN 1 ELSE 0 END AS root_category, (SELECT name FROM gest_category WHERE id = gc.parentId) AS parent_category, gc.name AS meta_keyword, gc.name AS meta_description FROM gest_category gc';

const MANUFACTURER_PANEL_HEADING_CSV_IMPORT = 'Importa i marchi da CSV';
const PRODUCT_PANEL_HEADING_CSV_IMPORT = 'Importa i prodotti da CSV';
const CATEGORY_PANEL_HEADING_CSV_IMPORT = 'Importa le categorie da CSV';

const MANUFACTURER_WARNING_STEPS = [
    ' name (nome del marchio)',
    ' enabled (stato attivo: 0 o 1)',
    ' description (descrizione)',
    ' meta_title (titolo meta)',
    ' meta_keyword (parole chiave meta)',
];

const PRODUCT_WARNING_STEPS = [
    ' name (nome del prodotto)',
    ' enabled (stato attivo: 0 o 1)',
];

const CATEGORY_WARNING_STEPS = [
    ' name (nome della categoria)',
    ' enabled (stato attivo: 0 o 1)',
    ' description (descrizione)',
    ' meta_title (titolo meta)',
    ' root_category (categoria radice)',
    ' parent_category (categoria padre)',
    ' meta_keyword (parole chiave meta)',
    ' meta_description (descrizione meta)',
];