<?php
namespace Kof\Phalcon\Validation\Validator;

use Phalcon\Validation;
use Phalcon\Validation\Validator;
use Phalcon\Validation\ValidatorInterface;
use Phalcon\Validation\Message;

class Mobile extends Validator implements ValidatorInterface
{
    /**
     * Executes the uniqueness validation
     *
     * @param  Validation $validator
     * @param  string $attribute
     * @return boolean
     */
    public function validate(Validation $validator, $field)
    {
        $mobile = $validator->getValue($field);

        if (empty($mobile) || !preg_match('/^(13|14|15|16|17|18|19)[0-9]{9}$/', $mobile)) {
            $message = $this->getOption('message');
            if (is_array($message)) {
                $message = $message[$field];
            }
            if (!$message) {
                $message = '手机号格式不正确！';
            }

            $validator->appendMessage(new Message($message, $field, 'Mobile'));

            return false;
        }

        return true;
    }
}