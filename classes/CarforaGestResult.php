<?php

class CarforaGestResult
{
    public function __construct(bool $status, string $message, string | array | resource | null $data) {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
    }
}