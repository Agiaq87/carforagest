<?php

class ProductImporter
{
    public function __construct(array $languages, array $shop) {
        $this->languages = $languages;
        $this->shop = $shop;
        $this->db = Db::getInstance();
    }

    private function getCategoryIdByName($name)
    {
        if (empty($name)) {
            return (int)Configuration::get('PS_HOME_CATEGORY'); // Categoria di default
        }

        // Cerca la categoria nel database
        $idCategory = (int)Db::getInstance()->getValue(
            'SELECT id_category FROM `' . _DB_PREFIX_ . 'category_lang`
            WHERE name = `' . pSQL($name) . '` AND id_shop = `' . (int)$this->shop_id . '`');

        // Se non esiste, la crea
        if (!$idCategory) {
            $category = new Category();
            $category->id_parent = (int)Configuration::get('PS_HOME_CATEGORY');
            $category->active = 1;

            foreach ($this->languages as $lang) {
                $category->name[$lang['id_lang']] = $name;
                $category->link_rewrite[$lang['id_lang']] = Tools::link_rewrite($name);
            }

            if ($category->add()) {
                return $category->id;
            } else {
                return (int)Configuration::get('PS_HOME_CATEGORY'); // Fallback
            }
        }

        return $idCategory;
    }

    public function insertProduct(
        string $name,
        string $reference,
        float $price,
        int $quantity,
        string $categoryName,
        string $description,
    ): CarforaGestResult
    {
        $product = new Product();
        $product->reference = $reference;
        $product->price = $price;
        $product->id_category_default = $id_category;
        $product->id_shop_default = $this->shop_id;
        $product->active = 1; // Attiva il prodotto

        // Aggiunge i dati multilingua
        foreach ($this->languages as $lang) {
            $id_lang = $lang['id_lang'];
            $product->name[$id_lang] = $name;
            $product->description[$id_lang] = $description;
            $product->link_rewrite[$id_lang] = Tools::link_rewrite($name);
        }

        try {
            if (!$product->save()) {
                return new CarforaGestResult(false, 'Errore durante l\'inserimento del prodotto', null);
            }
        } catch (PrestashopException $e) {
            return new CarforaGestResult(false, 'Errore durante l\'inserimento del prodotto: ' . $e->getMessage(), null);
        }
    }

    public function importProducts(array $products): CarforaGestResult
    {
        foreach($products as $product) {
            $result = $this->insertProduct(
                $product[0],
                $product[1],
                $product[2],
                $product[3],
                $product[4],
                $product[5]
            );
            if (!$result->status) {
                return $result;
            }
        }

        return new CarforaGestResult(true, 'Tutti i prodotti sono stati inseriti', null);
    }
}