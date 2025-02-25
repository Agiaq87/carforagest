<?php

class AjaxInfo
{
    private string $message;
    private int $currentStep;
    private int $totalSteps;
    private bool $success;

    public function __construct(string $message, int $currentStep, int $totalSteps, bool $success = true)
    {
        $this->message = $message;
        $this->currentStep = $currentStep;
        $this->totalSteps = $totalSteps;
        $this->success = $success;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'currentStep' => $this->currentStep,
            'totalSteps' => $this->totalSteps,
            'success' => $this->success,
            'percentage' => $this->calculate_percentage($this->currentStep, $this->totalSteps)
        ];
    }

    public function calculate_percentage($value, $total): int
    {
        return $total != 0 ? ((int)(round(($value / $total) * 100))) : 0;
    }
}