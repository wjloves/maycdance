<?php
namespace App\Exception;

class phpmailerException extends \Exception {
    public function errorMessage() {
        $errorMsg = '<strong>' . $this->getMessage() . "</strong><br />\n";
        return $errorMsg;
    }
}