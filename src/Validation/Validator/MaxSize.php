<?php
namespace Kof\Phalcon\Validation\Validator;

use Phalcon\Validation;
use Phalcon\Validation\Validator;
use Phalcon\Validation\Message;

class MaxSize extends Validator
{
    /**
     * Executes the uniqueness validation
     *
     * @param  Validation $validation
     * @param  string     $field
     * @return boolean
     */
    public function validate(Validation $validation, $field)
    {
        $value = $validation->getValue($field);
        $maxSize = $this->getOption("maxSize");
        if (is_array($maxSize)) {
            $maxSize = isset($maxSize[$field]) ? $maxSize[$field] : '0B';
        }
        $byteUnits = ["B" => 0, "K" => 10, "M" => 20, "G" => 30, "T" => 40, "KB" => 10, "MB" => 20, "GB" => 30, "TB" => 40];
        $matches = null;
        $unit = "B";

        preg_match("/^([0-9]+(?:\\.[0-9]+)?)(" . implode("|", array_keys($byteUnits)) . ")?$/Di", $maxSize, $matches);
        if (isset($matches[2])) {
            $unit = $matches[2];
        }

        $bytes = floatval($matches[1]) * pow(2, $byteUnits[$unit]);

        if (floatval($value) > floatval($bytes)) {
            $message = $this->getOption("message");
            if (is_array($message)) {
                $message = $message[$field];
            }
            if (empty($message)) {
                $message = $validation->getDefaultMessage("FileSize");
            }
            $replacePairs = [":field" => $field, ":max" => $maxSize];

            $code = $this->getOption("code");
            if (is_array($code)) {
                $code = $code[$field];
            }

            $validation->appendMessage(new Message(strtr($message, $replacePairs), $field, "FileSize", $code));
			return false;
        }

        return true;
    }
}