<?php

namespace App\Services\Exceptions;

class UnsupportedSystemException extends \Exception
{
    public function __construct($system)
    {
        parent::__construct('System (' . $system . ') is not supported.');
    }
}
