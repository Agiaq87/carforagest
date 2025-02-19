<?php

require_once 'AjaxInfo.php';

interface Consumer
{
    public function handleMessage(AjaxInfo $info);
}