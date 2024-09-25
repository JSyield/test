<?php

namespace Evavel\Http\Validation\Traits;

trait ValidatorMessages
{
    public function getErrorMessage($attribute, $rule, $parameters)
    {
        $method = "message".ucfirst($rule);

        $message = $this->$method($attribute, $rule, $parameters);

        return $this->parseMessage(__eva_x($message), $attribute, ...$parameters);
    }

    public function messageRequired($attribute, $rule)
    {
        return "The field [attribute] is required.";
    }

    public function messageMax($attribute, $rule, $parameters)
    {
        $hasNumeric = $this->hasRule($attribute, ['integer','numeric']);

        if ($hasNumeric){
            return "The field [attribute] should have a max value of [param1]";
        } else {
            return "The field [attribute] should have max [param1] characters";
        }
    }

    public function messageInteger($attribute, $rule, $parameters)
    {
        return "The field [attribute] should be an integer value.";
    }

	public function messageEmail($attribute, $rule, $parameters)
	{
		return "The email is not valid.";
	}

	public function messageUnique($attribute, $rule, $parameters)
	{
		return "The value for [attribute] has to be unique. Already exists in the database.";
	}

	public function messageEmailwp($attribute, $rule, $parameters)
	{
		return "This email already exists in wordpress users table. Cannot be used.";
	}

    public function parseMessage($message, $attribute, $param1 = false, $param2 = false)
    {
        $message = str_replace('[attribute]', $attribute, $message);
        $message = str_replace('[param1]', $param1, $message);
        $message = str_replace('[param2]', $param2, $message);
        return $message;
    }
}
