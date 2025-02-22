<?php

require_once __DIR__ . '/../classes/CarforaGestResult.php';

class ManufacturerImporter
{
    public function __construct(array $languages, array $shop) {
        $this->languages = $languages;
        $this->shop = $shop;
        $this->db = Db::getInstance();
    }

    /**
     * Si occupa dell'inserimento di un marchio nuovo
     * Nota bene: questo metodo non gestisce le immagini
     * @param string $name
     * @param bool $active
     * @param string $description
     * @param string $metaTitle
     * @param string $metaKeyword
     * @return array $result
     */
    public function insertManufacturer(
        string $name,
        bool $active,
        string $description,
        string $metaTitle,
        string $metaKeyword,
    ): CarforaGestResult
    {
        $temp = new Manufacturer();
        $temp->name = $name;
        $temp->active = $active;

        $descriptions = array();
        $meta_titles = array();
        $meta_keywords = array();
        $link_rewrites = array();

        foreach ($this->languages as $lang) {
            $descriptions[$lang['id_lang']] = $description;
            $meta_titles[$lang['id_lang']] = $metaTitle;
            $meta_keywords[$lang['id_lang']] = $metaKeyword;
            $link_rewrites[$lang['id_lang']] = Tools::link_rewrite($name);
        }

        $temp->description = $descriptions;
        $temp->meta_title = $meta_titles;
        $temp->meta_keywords = $meta_keywords;

        if (!$temp->validateFields(false)) {
            return new CarforaGestResult(false, 'I campi inseriti non sono validi', null);
        }

        try {
            if (!$temp->save()) {
                return new CarforaGestResult(
                    false,
                    'Errore nel salvataggio del marchio',
                    null
                );
            }

            foreach($this->shop as $shop) {
                $id_shop = $shop['id_shop'];

                $insertData = array(
                    'id_manufacturer' => $temp->id,
                    'id_shop' => (int)$id_shop
                );
                $this->db->insert('manufacturer_shop', $insertData);
            }

            return new CarforaGestResult(
                true,
                'Marchio ' . $temp->name . ' aggiunto con successo',
                'Marchio ' . $temp->name . ' aggiunto con id: ' .$temp->id
            );
        } catch (PrestaShopException $e) {
            return new CarforaGestResult(
                false,
                'Errore nel salvataggio del marchio ' .  $e->getMessage(),
                null
            );
        }
    }

    /**
     * Itera sull'array di marchi e procede all'inserimento di ognuno
     * @param array $manufacturers
     * @return array $result
     */
    public function importManufacturers(array $manufacturers): CarforaGestResult
    {
        foreach ($manufacturers as $manufacturer) {
            $result = $this->insertManufacturer($manufacturer[0], $manufacturer[1], $manufacturer[2], $manufacturer[3], $manufacturer[4]);
            if (!$result->status) {
                return $result;
            }
        }

        return new CarforaGestResult(true, 'Tutti i marchi sono stati inseriti', null);
    }
}