<?php


namespace Modules\Directory\Traits;


trait ErrorHandler
{
    private $errors = [];

    public function error($error)
    {
        array_push($this->errors, $error);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
