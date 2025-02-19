<?php

require_once 'AjaxInfo.php';

abstract class Producer
{
    protected $listener;
    public function setListener(callable $func)
    {
        $this->listener = $func;
    }

    public function produce(AjaxInfo $info)
    {
        if ($this->listener) {
            call_user_func($this->listener, $info);
        }
    }
}