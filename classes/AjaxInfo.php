<?php

require_once 'CarforaGestResult.php';
require_once 'AjaxHelper.php';
class AjaxInfo
{
    public string $message;
    public int $percentage;
    public bool $status;

    public function __construct(CarforaGestResult $result, int $stepNumber, int $totalSteps)
    {
        $this->message = $result->message;
        $this->percentage = calculate_percentage($stepNumber, $totalSteps);
        $this->status = $result->status;
    }

    public function toJson(): string
    {
        return json_encode(['status' => $this->status, 'message' => $this->message, 'percentage' => $this->percentage]);
    }

}