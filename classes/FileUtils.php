<?php

require_once 'CarforaGestResult.php';

class FileUtils
{
    public function __construct()
    {

    }

    public function checkFileHealty(): CarforaGestResult
    {
        if (empty($_FILES['csv_file'])) {
            return new CarforaGestResult(false, "File non presente", null);
        }

        if ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            // Si è verificato un errore durante il caricamento
            switch ($_FILES['nome_del_file']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    return new CarforaGestResult(false, "Il file è troppo grande (supera il limite massimo consentito).", null);
                case UPLOAD_ERR_FORM_SIZE:
                    return new CarforaGestResult(false, "Il file è troppo grande (supera il limite massimo consentito dal form).", null);
                case UPLOAD_ERR_PARTIAL:
                    return new CarforaGestResult(false, "Il file è stato caricato solo parzialmente.", null);
                case UPLOAD_ERR_NO_FILE:
                    return new CarforaGestResult(false, "Nessun file è stato caricato.", null);
                case UPLOAD_ERR_NO_TMP_DIR:
                    return new CarforaGestResult(false, "La cartella temporanea non esiste.", null);
                case UPLOAD_ERR_CANT_WRITE:
                    return new CarforaGestResult(false, "Impossibile scrivere il file sul disco.", null);
                case UPLOAD_ERR_EXTENSION:
                    return new CarforaGestResult(false, "Estensione del file non consentita.", null);
                default:
                    return new CarforaGestResult(false, "Errore sconosciuto durante il caricamento del file.", null);
            }
        }

        return new CarforaGestResult(true, "File caricato correttamente.", $_FILES['csv_file']['name']);
    }

    public function checkFileExtension(string $file): CarforaGestResult
    {
        return (
            (str_contains($file, '.csv')) ?
                new CarforaGestResult(true, "Estensione del file valida.", null) :
                new CarforaGestResult(false, "Estensione del file non valida.", null)
        );
    }

    public function extractFile()
    {
        return $_FILES['csv_file']['tmp_name'];
    }

    public function openFile(string $file)
    {
        $handle = fopen($file, "r");
        if ($handle === false) {
            return false;
        }
        return $handle;
    }

    public function checkHeader($handle, int $numOfColumn): CarforaGestResult
    {
        $header = fgetcsv($handle, separator: ',');
        return $header != null && count($header) === $numOfColumn ? new CarforaGestResult(true, "Header valido.", $header) : new CarforaGestResult(false, "Header non valido.", null);
    }

    /**
     * Prende il file caricato, ne verifica l'integrità, quindi procede ad estrapolare i dati assicurandosi che sia rispettato il numero di colonne
     * @return CarforaGestResult
     */
    public function extractData(int $numOfColumn): CarforaGestResult
    {
        $result = $this->checkFileHealty();
        if (!$result->status) {
            return $result;
        }
        $fileName = $result->data;

        $result = $this->checkFileExtension($fileName);
        if (!$result->status) {
            return $result;
        }

        $file = $this->extractFile();
        if ($file == null) {
            return new CarforaGestResult(false, "File non caricato.", null);
        }


        $handle = $this->openFile($file);
        if ($handle === false) {
            return new CarforaGestResult(false, "Impossibile aprire il file.", null);
        }

        $result = $this->checkHeader($handle, $numOfColumn);
        if (!$result->status) {
            return $result;
        }
        $header = $result->data;

        $manufacturers = array();
        $i = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($i === 0) {
                $i++;
                continue;
            }
            $manufacturers[] = $data;
        }
        fclose($handle);

        return new CarforaGestResult(true, "File caricato correttamente.", $manufacturers);
    }
}