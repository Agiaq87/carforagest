<?php

require_once __DIR__ . '/../classes/Producer.php';
require_once __DIR__ . '/../classes/AjaxInfo.php';
require_once __DIR__ . '/../classes/CarforaGestResult.php';

class ManufacturerImporter extends Producer
{
    public function __construct(array $languages) {
        $this->languages = $languages;
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
    public function newManufacturer(
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
        $total = count($manufacturers);

        if ($total === 0) {
            return [
                'status' => false,
                'message' => 'Nessun marchio da inserire',
            ];
        }
        $counter = 1;
        $result = null;

        foreach ($manufacturers as $manufacturer) {
            $result = $this->newManufacturer($manufacturer[0], $manufacturer[1], $manufacturer[2], $manufacturer[3], $manufacturer[4]);
            $this->produce(
                new AjaxInfo($result, $counter, $total)
            );
            $counter++;
        }

        return new CarforaGestResult(true, 'Tutti i marchi sono stati inseriti', null);
    }
}