<?php

namespace App\Services\Exceptions;

class SystemNotInitializedException extends \Exception
{
    public function __construct()
    {
        parent::__construct('System not initialized');
    }

}
