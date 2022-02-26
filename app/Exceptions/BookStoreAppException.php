<?php

namespace App\Exceptions;

use Exception;

class BookStoreAppException extends Exception
{
    public function message(){
        return $this->getMessage();

    }
    public function statusCode(){
        return $this->getCode();
    }
}
