<?php

namespace Evavel\Http\Controllers;

use Evavel\Eva;
use Evavel\Http\Request\UserSettingsRequest;
use Evavel\Http\Validation\Validator;
use Evavel\Query\Query;
use Evavel\Resources\Fields\Password;
use Evavel\Resources\Fields\Select;
use Evavel\Resources\Fields\Text;

class UserSettingsController extends Controller
{
	// These names are also used in UserSettings.vue
	const F_PASSWORD = 'password';
	const F_PASSWORD_CONFIRM = 'password-confirmation';

	public function handle(UserSettingsRequest $request)
	{
		$user = Eva::make('user');

		$fields = $this->fields();
		foreach($fields as &$field)
		{
			$field->resolveValueFromModel($user);
		}

		$data = [
			'resource' => $user,
			'resourceName' => 'user-settings',
			'resourceId' => $user->id,
			'fields' => $fields,
			'password_fields' => [ self::F_PASSWORD, self::F_PASSWORD_CONFIRM]
		];

		return $this->response($data);
	}

	public function store(UserSettingsRequest $request)
	{
		// Validate
		$validator = Validator::make($request->body_params, $this->updateRules());

		if ($validator->fails()) {
			$response = [
				'message' => __eva('The given data was invalid.'),
				'errors' => $validator->errors()
			];
			evavel_send_json($response, 422);
		}

		// Update the user model
		$user = Eva::make('user');
		$params = $request->body_params;

		$email_old = $user->email;
		$email_new = $params['email'];

		foreach($this->fields() as $field) {
			$attribute = $field->attribute;
			if (!in_array($attribute, [self::F_PASSWORD, self::F_PASSWORD_CONFIRM])){
				$user->{$attribute} = $params[$attribute];
			}
		}
		$user->save();


		// Update the user WP email
		if ($email_old != $email_new) {
			$user_wp = Query::tableWP(evavel_wp_table_users())
				->where('user_email', $email_old)
				->first();

			if ($user_wp != null) {
				Query::tableWP(evavel_wp_table_users())
					->where('id', $user_wp->ID)
					->update([ 'user_email' => $email_new ]);
			}
		}

		// Update the WP password
		$password_new = $params[self::F_PASSWORD];
		if (!empty($password_new)) {
			$user_wp = Query::tableWP(evavel_wp_table_users())
				->where('user_email', $email_new)
				->first();
			if ($user_wp != null){
				evavel_wp_set_password($password_new,  $user_wp->ID);
				evavel_wp_auto_login($user_wp->ID);
			}
		}

		return $this->response([]);
	}

	public function fields()
	{
		return [
			Text::make(__eva('Name'), 'name')
				->rules('required', 'max:50')
			    ->placeholder(__eva('Your name'))
				->help(__eva('Change your full name')),
			    //->suggestions(['Alejandro', 'Eva', 'Bruno']),

			Text::make(__eva('Email'), 'email')
				->sortable()
			    ->rules('required', 'max:255', 'email', 'unique:users', 'emailwp')
				->placeholder(__eva('email'))
			    ->help(__eva('Change your email')),

			Select::make(__eva('Language'), 'language')
				->rules('required')
				->options(evavel_languages_allowed()),

			Password::make(__eva('Password'), self::F_PASSWORD)
			    ->rules( 'max:50'),

			Password::make(__eva('Confirm password'), self::F_PASSWORD_CONFIRM)
				->rules( 'max:50'),
		];
	}

	public function updateRules()
	{
		$rules = [];
		foreach($this->fields() as $field){
			$rules[$field->attribute] = $field->rules;
		}
		return $rules;
	}
}
