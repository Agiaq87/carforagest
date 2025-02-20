<?php

class CarforaGestState
{
    private string $currentModality;
    private array $modalities = ['reset', 'db', 'csv', 'progress']; // Questo individua se importare con il CSV o con IL DB
    private string $currentArgument;
    private array $arguments = ['Reset', 'Fornitori', 'Marchi', 'Prodotti'];
    private array $maxNumOfColumnsOfFileForArgument = [
        'Fornitori' => 3,
        'Marchi' => 5,
        'Prodotti' => 4
    ];

    public function __construct()
    {
        $this->currentArgument = 'Reset';
        $this->currentModality = 'reset';
    }

    public function setDB() {
        $this->currentModality = 'db';
    }

    public function setCSV() {
        $this->currentModality = 'csv';
    }

    public function setSuppliers() {
        $this->currentArgument = $this->arguments[1];
    }

    public function setManufacturers() {
        $this->currentArgument = $this->arguments[2];
    }

    public function setProducts() {
        $this->currentArgument = $this->arguments[3];
    }

    public function detectArguments(string $value)
    {
        if (strcasecmp($value, $this->arguments[1]) === 0) { // Fornitori
            $this->currentArgument = 'Fornitori';
        } else if (strcasecmp($value, $this->arguments[2]) === 0) { // Marchi
            $this->currentArgument = 'Marchi';
        } else if (strcasecmp($value, $this->arguments[3]) === 0) { // Prodotti
            $this->currentArgument = 'Prodotti';
        } else {
            $this->currentArgument = 'Reset';
        }
    }

    public function detectModalities(string $value)
    {
        if (strcasecmp($value, $this->modalities[1]) === 0) { // DB
            $this->currentModality = 'db';
        } else if (strcasecmp($value, $this->modalities[2]) === 0) { // CSV
            $this->currentModality = 'csv';
        } else if (strcasecmp($value, $this->modalities[3]) === 0){
            $this->currentModality = 'progress';
        } else {
            $this->currentModality = 'reset';
        }
    }

    // Passa null per resettare
    public function detectState(string | null $modality, string | null $argument)
    {
        if (isset($modality)) {
            if (strcasecmp($modality, $this->modalities[1]) === 0) { // DB
                $this->currentModality = 'db';
            } else if (strcasecmp($modality, $this->modalities[2]) === 0) { // CSV
                $this->currentModality = 'csv';
            } else if (strcasecmp($modality, $this->modalities[3]) === 0){
                $this->currentModality = 'progress';
            }
        } else {
            $this->currentModality = 'reset';
        }

        if (isset($argument)) {
            if (strcasecmp($argument, $this->arguments[1]) === 0) { // Fornitori
                $this->currentArgument = 'Fornitori';
            } else if (strcasecmp($argument, $this->arguments[2]) === 0) { // Marchi
                $this->currentArgument = 'Marchi';
            } else if (strcasecmp($argument, $this->arguments[3]) === 0) { // Prodotti
                $this->currentArgument = 'Prodotti';
            }
        } else {
            $this->currentArgument = 'Reset';
        }
    }

    public function resetState()
    {
        $this->currentModality = 'reset';
        $this->currentArgument = 'Reset';
    }

    public function getCurrentModality(): string
    {
        return $this->currentModality;
    }

    public function getModalityForSwitch(): int
    {
        switch($this->currentModality) {
            case 'db': return 1;
            case 'csv': return 2;
            case 'progress': return 3;
            default: return 0;
        }
    }

    public function getCurrentArgument(): string
    {
        return $this->currentArgument;
    }

    public function getArgumentForSwitch(): int
    {
        switch($this->currentArgument) {
            case 'Fornitori': return 1;
            case 'Marchi': return 2;
            case 'Prodotti': return 3;
            default: return 0;
        }
    }

    public function getResetState(): string
    {
        return $this->modalities[0];
    }

    public function getNextModality(): string
    {
        switch($this->currentModality) {
            case 'db':
            case 'csv': return $this->modalities[3];
            case 'progress': return $this->modalities[0];
            default: return $this->modalities[0];
        }
        return $this->modalities[$this->getModalityForSwitch() + 1];
    }

    public function getMaxNumOfColumnsOfFileForArgument(): int
    {
        return $this->maxNumOfColumnsOfFileForArgument[$this->currentArgument];
    }

    public function toArray(): array
    {
        return [
            'modality' => $this->currentModality,
            'argument' => $this->currentArgument
        ];
    }
}