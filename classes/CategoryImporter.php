<?php

require_once __DIR__ . '/../classes/CarforaGestResult.php';

class CategoryImporter
{
    private array $createdCategories;

    public function __construct(array $languages, array $shop)
    {
        $this->languages = $languages;
        $this->shop = $shop;
        $this->db = Db::getInstance();
        $this->createdCategories = array();

    }

    function sanitizeLinkRewrite($string)
    {
        // Converti in minuscolo
        $string = strtolower($string);

        // Sostituisci caratteri accentati con equivalenti non accentati
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);

        // Rimuovi tutto ciò che non è una lettera o un numero
        $string = preg_replace('/[^a-z0-9]/', '', $string);

        // Se la stringa è vuota, assegna un valore di fallback
        if (empty($string)) {
            $string = 'categoria' . uniqid();
        }

        return $string;
    }


    function getCategoryIdByName($name) {
        if (empty($name)) {
            return 0;
        }

        // Prima cerca nelle categorie appena create
        if (isset($this->createdCategories[$name])) {
            return $this->createdCategories[$name];
        }

        // Altrimenti cerca nel database
        $query = new DbQuery();
        $query->select('DISTINCT cl.id_category')
            ->from('category_lang', 'cl')
            ->innerJoin('category', 'c', 'c.id_category = cl.id_category')
            ->innerJoin('category_shop', 'cs', 'cs.id_category = cl.id_category')
            ->where('cl.name = "'.pSQL($name).'"')
            ->where('c.active = 1')
            ->where('cs.id_shop = '.(int)Context::getContext()->shop->id);

        $result = (int)Db::getInstance()->getValue($query);
        return $result ?: 0;
    }

    /**
     * Si occupa dell'inserimento di una nuova categoria
     * @param string $name
     * @param bool $active
     * @param string $description
     * @param string $metaTitle
     * @param string $metaKeyword
     * @return array $result
     */
    public function newCategory(
        string $name,
        bool $active,
        string $description,
        string $metaTitle,
        bool $rootCategory,
        string $parentCategory,
        string $metaKeyword,
        string $metaDescription
    ): CarforaGestResult
    {
        $temp = new Category();
        $temp->active = $active;
        $temp->id_parent = $rootCategory ?
            0 : $this->getCategoryIdByName($parentCategory);
        $temp->name = $name;

        $descriptions = array();
        $meta_titles = array();
        $meta_keywords = array();
        $meta_descriptions = array();
        $link_rewrites = array();

        // Prepara i dati multilingua
        foreach ($this->languages as $language) {
            $descriptions[$language['id_lang']] = $description;
            $meta_titles[$language['id_lang']] = $metaTitle;
            $meta_keywords[$language['id_lang']] = $metaKeyword;
            $meta_descriptions[$language['id_lang']] = $metaDescription;
            $link_rewrites[$language['id_lang']] = $this->sanitizeLinkRewrite($name);
        }

        $temp->description = $descriptions;
        $temp->meta_title = $meta_titles;
        $temp->meta_keywords = $meta_keywords;
        $temp->meta_description = $meta_descriptions;
        $temp->link_rewrite = $link_rewrites;

        if (empty($temp->id_parent) || empty($temp->name) || empty($temp->link_rewrite)) {
            return new CarforaGestResult(false, 'Dati mancanti per la categoria: ' . $name, null);
        }

        try {
            if (!$temp->save()) {
                return new CarforaGestResult(
                    false,
                    'Errore nel salvataggio della categoria ' . $name . '',
                    null
                );
            }

            foreach($this->shop as $shop) {
                $temp->addShop($shop['id_shop']);
            }

            $this->createdCategories[$name] = $temp->id;

            return new CarforaGestResult(
                true,
                'Marchio ' . $temp->name . ' aggiunto con successo',
                'Marchio ' . $temp->name . ' aggiunto con id: ' .$temp->id
            );
        } catch (PrestaShopException $e) {
            return new CarforaGestResult(
                false,
                'Errore nel salvataggio della categoria ' .  $e->getMessage(),
                null
            );
        }
    }

    /**
     * Itera sull'array di categorie e procede all'inserimento di ognuna
     * @param array $categories
     * @return array $result
     */
    public function importCategories(array $categories): CarforaGestResult
    {
        // Prima passsata: categorie root
        foreach ($categories as $category) {
            // Carica tutte le categorie root
            if ($category[6] == 1) {
                $result = $this->newCategory(
                    $category[0],
                    $category[1] == 1,
                    $category[2],
                    $category[3],
                    $category[4],
                    $category[5],
                    $category[6] == 1,
                    $category[7]
                );
                if ($result->status === false) {
                    return $result;
                }
            }
        }

        Category::regenerateEntireNtree();

        return new CarforaGestResult(true, 'Tutti le categorie sono state inserite', null);
    }
}