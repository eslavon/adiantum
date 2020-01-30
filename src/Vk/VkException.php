<?php

namespace Eslavon\Adiantum\Vk;

use Exception;
use Throwable;

class VkException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
    public function __toString() 
    {
        $error = "[Exception]: An error has occurred:";
        $error .= "\r\n[Exception]: Message Error: {$this->getMessage()}";
        $error .= "\r\n[Exception]: Code Error: {$this->getCode()}";
        $error .= "\r\n[Exception]: File Error: {$this->getFile()}:{$this->getLine()}";
        $error .= "\r\n[Exception]: Path Error: {$this->getTraceAsString()}\r\n";

        $file = fopen("error_log" . date("d-m-Y_h") . ".log", "a");
        fwrite($file, $error);
        fclose($file);
        return $error;
    }
}
