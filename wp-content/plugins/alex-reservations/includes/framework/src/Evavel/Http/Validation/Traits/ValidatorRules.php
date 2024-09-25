<?php

namespace Evavel\Http\Validation\Traits;

use Evavel\Eva;
use Evavel\Query\Query;

trait ValidatorRules
{
    public function validateRequired($attribute, $value)
    {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif ((is_array($value) || $value instanceof Countable) && count($value) < 1) {
            return false;
        }

        return true;
    }

    public function validateMax($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'max');

        return $this->getSize($attribute, $value) <= $parameters[0];
    }

    public function validateInteger($attribute, $value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    public function validateNumeric($attribute, $value)
    {
        return is_numeric($value);
    }

    public function requireParameterCount($count, $parameters, $rule)
    {
        if (count($parameters) < $count) {
            throw new InvalidArgumentException("Validation rule $rule requires at least $count parameters.");
        }
    }

	public function validateEmail($attribute, $value, $parameters)
	{
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}

	public function validateUnique($attribute, $value, $parameters)
	{
		$table = (is_array($parameters) && isset($parameters[0]))
			? $parameters[0]
			: false;

		if (!$table) return true;

		$query = Query::table($table)
		     ->where($attribute, $value);

		// Table users?
		if ($table == 'users'){
			$user = Eva::make('user');
			$query->where('id', '!=', $user->id);
		}

		$result = $query->first();

		return $result == null;
	}

	public function validateEmailwp($attribute, $value, $parameters)
	{
		$user_app = Eva::make('user');

		$user_wp = Query::tableWP(evavel_wp_table_users())
			->where('user_email', $value)
			->where('id', '!=', $user_app->wp_user_id)
			->first();

		return $user_wp == null;
	}

}
