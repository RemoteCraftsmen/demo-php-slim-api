<?php

namespace App\Validation;

use Slim\Http\Request;
use Respect\Validation\Exceptions\NestedValidationException;

class Validator
{
    protected $errors = [];

    public function validate(Request $request, array $rules)
    {
        foreach ($rules as $field => $rule) {
            try {

                $rule->setName(ucfirst($field))->assert($request->getParam($field));

            } catch (NestedValidationException $e) {
                $this->errors[$field] = $e->getMessages();
            }
        }
        return $this;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function fail()
    {
        return !empty($this->errors);
    }
}