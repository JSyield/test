<?php

namespace Alexr\Settings;

use Evavel\Models\SettingSimpleGrouped;

class Payment extends SettingSimpleGrouped
{
	public static $table_name = 'restaurant_setting';
	public static $meta_key = 'payments_config';
	public static $pivot_tenant_field = 'restaurant_id';
	public static $component_list_item = 'item-payment';

	protected $casts = [
		'stripe_active' => 'boolean',
		'stripe_sandbox' => 'boolean'
	];

	function settingName()
	{
		return __eva('Payments');
	}

	public function listItems()
	{
		$fields = $this->fields();

		return [
			[
				'label' => 'Stripe',
				'slug' => 'config_stripe',
				'fields' => isset($fields['config_stripe']) ? $fields['config_stripe'] : []
			],
			[
				'label' => 'Paypal',
				'slug' => 'config_paypal',
				'fields' => isset($fields['config_paypal']) ? $fields['config_paypal'] : []
			],
			[
				'label' => 'Redsys',
				'slug' => 'config_redsys',
				'fields' => isset($fields['config_redsys']) ? $fields['config_redsys'] : []
			],
			[
				'label' => 'Mercadopago',
				'slug' => 'config_mercadopago',
				'fields' => isset($fields['config_mercadopago']) ? $fields['config_mercadopago'] : []
			],
			[
				'label' => 'Mollie',
				'slug' => 'config_mollie',
				'fields' => isset($fields['config_mollie']) ? $fields['config_mollie'] : []
			],
			[
				'label' => 'Square',
				'slug' => 'config_square',
				'fields' => isset($fields['config_square']) ? $fields['config_square'] : []
			],
		];
	}

	public function fields()
	{
		return [
			'config_stripe' => $this->fieldsStripe(),
			'config_redsys' => $this->fieldsRedsys(),
			'config_paypal' => $this->fieldsPaypal(),
			'config_mercadopago' => $this->fieldsMercadopago(),
			'config_mollie' => $this->fieldsMollie(),
			'config_square' => $this->fieldsSquare(),
		];
	}

	public function fieldsStripe()
	{
		return [
			[
				'attribute' => 'stripe_active',
				'stacked' => true,
				'name' => __eva('Enable Stripe payments'),
				'component' => 'boolean-field',
				'type' => 'switch',
				'value' => $this->stripe_active,
				'helpText' => __eva('Enable this to allow Stripe payments')
			],
			[
				'attribute' => 'stripe_fields',
				'stacked' => true,
				'name' => __eva('Configuration'),
				'component' => 'group-field',
				'textColor' => 'text-indigo-400 text-lg',
				'borderColor' => 'border-indigo-300',
				'backColor' => 'bg-indigo-50',
				'showWhen' => [
					'attribute' => 'stripe_active',
					'values' => ['true']
				],
				'fields' => [
					[
						'attribute' => 'stripe_title',
						'stacked' => false,
						'name' => __eva('Title'),
						'component' => 'text-field',
						'value' => $this->stripe_title,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'stripe_description',
						'stacked' => false,
						'name' => __eva('Description'),
						'component' => 'textarea-field',
						'value' => $this->stripe_description,
						'placeholder' => '',
						'helpText' => __eva('This controls the description which the user sees during checkout.')
					],
					[
						'attribute' => 'stripe_public_key',
						'stacked' => false,
						'name' => __eva('Public Key (Live)'),
						'component' => 'text-field',
						'value' => $this->stripe_public_key,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'stripe_private_key',
						'stacked' => false,
						'name' => __eva('Private Key (Live)'),
						'component' => 'text-field',
						'value' => $this->stripe_private_key,
						'placeholder' => '',
						'helpText' => __eva('')
					],

					[
						'attribute' => 'preauth_days',
						'stacked' => false,
						'name' => __eva('Pre-authorize days'),
						'component' => 'select-field',
						'value' => $this->preauth_days == null ? 5 : $this->preauth_days,
						'options' => $this->listOfPreauthDays(),
						'helpText' => [__eva('The card will be pre-authorized [X] days before the reservation date. If the reservation is made closer than that, the card-on-file will be used instead.'),
							'<a style="color:blue" target="_blank" href="https://docs.stripe.com/payments/place-a-hold-on-a-payment-method">STRIPE Place a hold on a payment method -> </a>']
					],
					[
						'attribute' => 'stripe_sandbox',
						'stacked' => true,
						'name' => __eva('Enabled Sandbox environment'),
						'component' => 'boolean-field',
						'type' => 'switch',
						'value' => $this->stripe_sandbox,
						'helpText' => __eva('Use for testing payments')
					],
					[
						'attribute' => 'stripe_public_key_sandbox',
						'stacked' => false,
						'name' => __eva('Public Key (Sandbox)'),
						'component' => 'text-field',
						'value' => $this->stripe_public_key_sandbox,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'stripe_private_key_sandbox',
						'stacked' => false,
						'name' => __eva('Private Key (Sandbox)'),
						'component' => 'text-field',
						'value' => $this->stripe_private_key_sandbox,
						'placeholder' => '',
						'helpText' => __eva('')
					],
				]
			],
		];
	}

	protected function listOfPreauthDays( ) {
		$list = [];
		for ($days = 0; $days <= 28; $days++) {
			$list[] = [
				'label' => $days,
				'value' => $days
			];
		}
		return $list;
	}

	public function fieldsRedsys()
	{
		return [
			[
				'attribute' => 'redsys_active',
				'stacked' => true,
				'name' => __eva('Enable Redsys Payments'),
				'component' => 'boolean-field',
				'type' => 'switch',
				'value' => $this->redsys_active,
				'helpText' => __eva('Enable this to allow Redsys payments')
			],
			[
				'attribute' => 'redsys_fields',
				'stacked' => true,
				'name' => __eva('Configuration'),
				'component' => 'group-field',
				'textColor' => 'text-indigo-400 text-lg',
				'borderColor' => 'border-indigo-300',
				'backColor' => 'bg-indigo-50',
				'showWhen' => [
					'attribute' => 'redsys_active',
					'values' => ['true']
				],
				'fields' => [
					[
						'attribute' => 'redsys_title',
						'stacked' => false,
						'name' => __eva('Title'),
						'component' => 'text-field',
						'value' => $this->redsys_title,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'redsys_description',
						'stacked' => false,
						'name' => __eva('Description'),
						'component' => 'textarea-field',
						'value' => $this->redsys_description,
						'placeholder' => '',
						'helpText' => __eva('This controls the description which the user sees during checkout.')
					],
					[
						'attribute' => 'redsys_language',
						'stacked' => false,
						'name' => __eva('Language'),
						'component' => 'select-field',
						'value' => $this->redsys_language,
						'placeholder' => '',
						'helpText' => __eva('Choose the language for the Gateway. Not all Banks accept all languages'),
						'options' => [
							['label' => 'Español', 'value' => '001'],
							['label' => 'English - Inglés', 'value' => '002'],
							['label' => 'Català', 'value' => '003'],
							['label' => 'Français - Frances', 'value' => '004'],
							['label' => 'Deutsch - Aleman', 'value' => '005'],
							['label' => 'Nederlands - Holandes', 'value' => '006'],
							['label' => 'Italiano', 'value' => '007'],
							['label' => 'Svenska - Sueco', 'value' => '008'],
							['label' => 'Português', 'value' => '009'],
							['label' => 'Valencià', 'value' => '010'],
							['label' => 'Polski - Polaco', 'value' => '011'],
							['label' => 'Galego', 'value' => '012'],
							['label' => 'Euskara', 'value' => '013'],
							['label' => 'български език - Bulgaro', 'value' => '100'],
							['label' => 'Chino', 'value' => '156'],
							['label' => 'Hrvatski - Croata', 'value' => '191'],
							['label' => 'Čeština - Checo', 'value' => '203'],
							['label' => 'Dansk - Danes', 'value' => '208'],
							['label' => 'Eesti keel - Estonio', 'value' => '233'],
							['label' => 'Suomi - Finlandes', 'value' => '246'],
							['label' => 'ελληνικά - Griego', 'value' => '300'],
							['label' => 'Magyar - Hungaro', 'value' => '348'],
							['label' => 'Japonés', 'value' => '392'],
							['label' => 'Latviešu valoda - Leton', 'value' => '428'],
							['label' => 'Lietuvių kalba - Lituano', 'value' => '440'],
							['label' => 'Malti - Maltés', 'value' => '470'],
							['label' => 'Română - Rumano', 'value' => '642'],
							['label' => 'ру́сский язы́к – Ruso', 'value' => '643'],
							['label' => 'Slovenský jazyk - Eslovaco', 'value' => '703'],
							['label' => 'Slovenski jezik - Esloveno', 'value' => '705'],
							['label' => 'Türkçe - Turco', 'value' => '792'],
						]
					],
					[
						'attribute' => 'redsys_commerce_number',
						'stacked' => false,
						'name' => __eva('Commerce Number (FUC)'),
						'component' => 'text-field',
						'value' => $this->redsys_commerce_number,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'redsys_commerce_name',
						'stacked' => false,
						'name' => __eva('Commerce Name'),
						'component' => 'text-field',
						'value' => $this->redsys_commerce_name,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'redsys_pay_options',
						'stacked' => false,
						'name' => __eva('Pay Options'),
						'component' => 'select-field',
						'value' => $this->redsys_pay_options,
						'placeholder' => '',
						'helpText' => __eva('Chose options in Redsys Gateway (by Default Credit Card + iUpay)'),
						'options' => [
							['label' => 'All Methods', 'value' => 'all'],
							['label' => 'Credit Card & IUpay', 'value' => 'T'],
							['label' => 'Credit Card', 'value' => 'C'],
						]
					],
					[
						'attribute' => 'redsys_terminal',
						'stacked' => false,
						'name' => __eva('Terminal number'),
						'component' => 'text-field',
						'value' => $this->redsys_terminal,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					/*[
						'attribute' => 'redsys_sni',
						'stacked' => true,
						'name' => __eva('HTTPS SNI Compatibility'),
						'component' => 'boolean-field',
						'type' => 'switch',
						'value' => $this->redsys_sni,
						'helpText' => __eva("If you are using HTTPS and Redsys don't support your certificate, example Lets Encrypt, you can deactivate HTTPS notifications. WARNING: If you are forcing redirection to HTTPS with htaccess, you need to add an exception for notification URL.")
					],*/
					/*[
						'attribute' => 'redsys_lwv',
						'stacked' => true,
						'name' => __eva('Enable LWV'),
						'component' => 'boolean-field',
						'type' => 'switch',
						'value' => $this->redsys_lwv,
						'helpText' => __eva("Enable LWV. WARNING, your bank has to enable it before you use it.")
					],*/
					[
						'attribute' => 'redsys_secret_live',
						'stacked' => false,
						'name' => __eva('Encryption secret passphrase SHA-256'),
						'component' => 'text-field',
						'value' => $this->redsys_secret_live,
						'placeholder' => '',
						'helpText' => __eva('Provided by your bank')
					],
					[
						'attribute' => 'redsys_test_mode',
						'stacked' => true,
						'name' => __eva('Running in TEST MODE'),
						'component' => 'boolean-field',
						'type' => 'switch',
						'value' => $this->redsys_test_mode,
						'helpText' => __eva("Select this option for the initial testing required by your bank, deselect this option once you pass the required test phase and your production environment is active.")
					],
					[
						'attribute' => 'redsys_secret_test',
						'stacked' => false,
						'name' => __eva('TEST MODE: Encryption secret passphrase SHA-256'),
						'component' => 'text-field',
						'value' => $this->redsys_secret_test,
						'placeholder' => '',
						'helpText' => __eva('Provided by your bank')
					],
				]
			]
		];
	}

	public function fieldsPaypal()
	{
		return [
			[
				'attribute' => 'paypal_active',
				'stacked' => true,
				'name' => __eva('Enable Paypal Payments'),
				'component' => 'boolean-field',
				'type' => 'switch',
				'value' => $this->paypal_active,
				'helpText' => __eva('Enable this to allow Paypal payments')
			],
			[
				'attribute' => 'paypal_fields',
				'stacked' => true,
				'name' => __eva('Configuration'),
				'component' => 'group-field',
				'textColor' => 'text-indigo-400 text-lg',
				'borderColor' => 'border-indigo-300',
				'backColor' => 'bg-indigo-50',
				'showWhen' => [
					'attribute' => 'paypal_active',
					'values' => ['true']
				],
				'fields' => [
					[
						'attribute' => 'paypal_title',
						'stacked' => false,
						'name' => __eva('Title'),
						'component' => 'text-field',
						'value' => $this->paypal_title,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'paypal_description',
						'stacked' => false,
						'name' => __eva('Description'),
						'component' => 'textarea-field',
						'value' => $this->paypal_description,
						'placeholder' => '',
						'helpText' => __eva('This controls the description which the user sees during checkout.')
					],
					[
						'attribute' => 'paypal_currency',
						'stacked' => false,
						'name' => __eva('Currency'),
						'component' => 'select-field',
						'options' => [
							['label' => 'Australian dollar AUD', 'value' => 'AUD'],
							['label' => 'Brazilian real BRL', 'value' => 'BRL'],
							['label' => 'Canadian dollar CAD', 'value' => 'CAD'],
							['label' => 'Chinese Renminbi CNY', 'value' => 'CNY'],
							['label' => 'Czech koruna CZK', 'value' => 'CZK'],
							['label' => 'Danish krone DKK', 'value' => 'DKK'],
							['label' => 'Euro EUR', 'value' => 'EUR'],
							['label' => 'Hong Kong dollar HKD', 'value' => 'HKD'],
							['label' => 'Hungarian forint HUF', 'value' => 'HUF'],
							['label' => 'Israeli new shekel ILS', 'value' => 'ILS'],
							['label' => 'Japanese yen JPY', 'value' => 'JPY'],
							['label' => 'Malaysian ringgit MYR', 'value' => 'MYR'],
							['label' => 'Mexican peso MXN', 'value' => 'MXN'],
							['label' => 'New Taiwan dollar TWD', 'value' => 'TWD'],
							['label' => 'New Zealand dollar NZD', 'value' => 'NZD'],
							['label' => 'Norwegian krone NOK', 'value' => 'NOK'],
							['label' => 'Philippine peso PHP', 'value' => 'PHP'],
							['label' => 'Polish złoty PLN', 'value' => 'PLN'],
							['label' => 'Pound sterling GBP', 'value' => 'GBP'],
							['label' => 'Singapore dollar SGD', 'value' => 'SGD'],
							['label' => 'Swedish krona SEK', 'value' => 'SEK'],
							['label' => 'Swiss franc CHF', 'value' => 'CHF'],
							['label' => 'Thai baht THB', 'value' => 'THB'],
							['label' => 'United States dollar USD', 'value' => 'USD']
						],
						'value' => $this->paypal_currency,
						'placeholder' => '',
						'helpText' => '<div style="color: red;">'.__eva('Customer will be charged using this currency').'</div>'
					],
					[
						'attribute' => 'paypal_currency_exchange',
						'stacked' => false,
						'name' => __eva('Currency exchange'),
						'component' => 'text-field',
						'value' => $this->paypal_currency_exchange,
						'placeholder' => '1.0',
						'helpText' => '<div style="color: red;">'.__eva("If the restaurant currency differs from the PayPal currency you used, you'll need to provide the exchange rate.") .'<br>'. __("I will then multiply your payment amount by this exchange rate.").'</div>'
					],
					[
						'attribute' => 'paypal_live_id',
						'stacked' => false,
						'name' => __eva('Client ID (Live)'),
						'component' => 'text-field',
						'value' => $this->paypal_live_id,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'paypal_live_secret',
						'stacked' => false,
						'name' => __eva('Secret Key (Live)'),
						'component' => 'text-field',
						'value' => $this->paypal_live_secret,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'paypal_sandbox',
						'stacked' => true,
						'name' => __eva('Running in TEST MODE'),
						'component' => 'boolean-field',
						'type' => 'switch',
						'value' => $this->paypal_sandbox,
						'helpText' => __eva("Select this option for the initial testing, deselect this option once you pass the required test phase and your production environment is active.")
					],
					[
						'attribute' => 'paypal_sandbox_id',
						'stacked' => false,
						'name' => __eva('Client ID (Sandbox)'),
						'component' => 'text-field',
						'value' => $this->paypal_sandbox_id,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'paypal_sandbox_secret',
						'stacked' => false,
						'name' => __eva('Secret Key (Sandbox)'),
						'component' => 'text-field',
						'value' => $this->paypal_sandbox_secret,
						'placeholder' => '',
						'helpText' => __eva('')
					],
				]
			],

		];
	}

	public function fieldsMercadopago()
	{
		return [
			[
				'attribute' => 'mercadopago_active',
				'stacked' => true,
				'name' => __eva('Enable Mercadopago Payments'),
				'component' => 'boolean-field',
				'type' => 'switch',
				'value' => $this->mercadopago_active,
				'helpText' => __eva('Enable this to allow Mercadopago payments')
			],
			[
				'attribute' => 'mercadopago_fields',
				'stacked' => true,
				'name' => __eva('Configuration'),
				'component' => 'group-field',
				'textColor' => 'text-indigo-400 text-lg',
				'borderColor' => 'border-indigo-300',
				'backColor' => 'bg-indigo-50',
				'showWhen' => [
					'attribute' => 'mercadopago_active',
					'values' => ['true']
				],
				'fields' => [
					[
						'attribute' => 'mercadopago_title',
						'stacked' => false,
						'name' => __eva('Title'),
						'component' => 'text-field',
						'value' => $this->mercadopago_title,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'mercadopago_description',
						'stacked' => false,
						'name' => __eva('Description'),
						'component' => 'textarea-field',
						'value' => $this->mercadopago_description,
						'placeholder' => '',
						'helpText' => __eva('This controls the description which the user sees during checkout.')
					],
					[
						'attribute' => 'mercadopago_currency',
						'stacked' => false,
						'name' => __eva('Currency'),
						'component' => 'select-field',
						'options' => [
							['label' => 'Peso argentino', 'value' => 'ARS'],
							['label' => 'Boliviano', 'value' => 'BOB'],
							['label' => 'Real', 'value' => 'BRL'],
							['label' => 'Unidad de Fomento', 'value' => 'CLF'],
							['label' => 'Colones', 'value' => 'CRC'],
							['label' => 'Peso Chileno', 'value' => 'CLP'],
							['label' => 'Peso colombiano', 'value' => 'COP'],
							['label' => 'Peso Cubano Convertible', 'value' => 'CUC'],
							['label' => 'Peso Cubano', 'value' => 'CUP'],
							['label' => 'Peso Dominicano', 'value' => 'DOP'],
							['label' => 'Euro', 'value' => 'EUR'],
							['label' => 'Quetzal Guatemalteco', 'value' => 'GTQ'],
							['label' => 'Lempira', 'value' => 'HNL'],
							['label' => 'Peso Mexicano', 'value' => 'MXN'],
							['label' => 'Córdoba', 'value' => 'NIO'],
							['label' => 'Balboa', 'value' => 'PAB'],
							['label' => 'Soles', 'value' => 'PEN'],
							['label' => 'Guaraní', 'value' => 'PYG'],
							['label' => 'Dólar', 'value' => 'USD'],
							['label' => 'Peso Uruguayo', 'value' => 'UYU'],
							['label' => 'Bolivar fuerte', 'value' => 'VEF'],
							['label' => 'Bolivar Soberano', 'value' => 'VES']
						],
						'value' => $this->mercadopago_currency,
						'placeholder' => '',
						'helpText' => '<div style="color: red;">'.__eva('Customer will be charged using this currency').'</div>'
					],
					/*[
						'attribute' => 'mercadopago_currency_exchange',
						'stacked' => false,
						'name' => __eva('Currency exchange'),
						'component' => 'text-field',
						'value' => $this->mercadopago_currency_exchange,
						'placeholder' => '1.0',
						'helpText' => '<div style="color: red;">'.__eva("If the restaurant currency differs from the Mercadopago currency you used, you'll need to provide the exchange rate.") .'<br>'. __("I will then multiply your payment amount by this exchange rate.").'</div>'
					],*/
					[
						'attribute' => 'mercadopago_public_key',
						'stacked' => false,
						'name' => __eva('Public Key (Live)'),
						'component' => 'text-field',
						'value' => $this->mercadopago_public_key,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'mercadopago_access_token',
						'stacked' => false,
						'name' => __eva('Access Token (Live)'),
						'component' => 'text-field',
						'value' => $this->mercadopago_access_token,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'mercadopago_sandbox',
						'stacked' => true,
						'name' => __eva('Enabled Sandbox environment'),
						'component' => 'boolean-field',
						'type' => 'switch',
						'value' => $this->mercadopago_sandbox,
						'helpText' => __eva('Use for testing payments')
					],
					[
						'attribute' => 'mercadopago_public_key_sandbox',
						'stacked' => false,
						'name' => __eva('Public Key (Sandbox)'),
						'component' => 'text-field',
						'value' => $this->mercadopago_public_key_sandbox,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'mercadopago_access_token_sandbox',
						'stacked' => false,
						'name' => __eva('Access Token (Sandbox)'),
						'component' => 'text-field',
						'value' => $this->mercadopago_access_token_sandbox,
						'placeholder' => '',
						'helpText' => __eva('')
					],
				]
			]
		];
	}

	public function fieldsMollie(){
		return [
			[
				'attribute' => 'mollie_active',
				'stacked' => true,
				'name' => __eva('Enable Mollie Payments'),
				'component' => 'boolean-field',
				'type' => 'switch',
				'value' => $this->mollie_active,
				'helpText' => __eva('Enable this to allow Mollie payments')
			],
			[
				'attribute' => 'mollie_fields',
				'stacked' => true,
				'name' => __eva('Configuration'),
				'component' => 'group-field',
				'textColor' => 'text-indigo-400 text-lg',
				'borderColor' => 'border-indigo-300',
				'backColor' => 'bg-indigo-50',
				'showWhen' => [
					'attribute' => 'mollie_active',
					'values' => ['true']
				],
				'fields' => [
					[
						'attribute' => 'mollie_title',
						'stacked' => false,
						'name' => __eva('Title'),
						'component' => 'text-field',
						'value' => $this->mollie_title,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'mollie_description',
						'stacked' => false,
						'name' => __eva('Description'),
						'component' => 'textarea-field',
						'value' => $this->mollie_description,
						'placeholder' => '',
						'helpText' => __eva('This controls the description which the user sees during checkout.')
					],
					[
						'attribute' => 'mollie_currency',
						'stacked' => false,
						'name' => __eva('Currency'),
						'component' => 'select-field',
						'options' => [
							['label' => 'United Arab Emirates dirham', 'value' => 'AED'],
							['label' => 'Australian dollar', 'value' => 'AUD'],
							['label' => 'Bulgarian lev', 'value' => 'BGN'],
							['label' => 'Brazilian real', 'value' => 'BRL'],
							['label' => 'Canadian dollar', 'value' => 'CAD'],
							['label' => 'Swiss franc', 'value' => 'CHF'],
							['label' => 'Czech koruna', 'value' => 'CZK'],
							['label' => 'Danish krone', 'value' => 'DKK'],
							['label' => 'Euro', 'value' => 'EUR'],
							['label' => 'British pound', 'value' => 'GBP'],
							['label' => 'Hong Kong dollar', 'value' => 'HKD'],
							['label' => 'Hungarian forint', 'value' => 'HUF'],
							['label' => 'Israeli new shekel', 'value' => 'ILS'],
							['label' => 'Mexican peso', 'value' => 'MXN'],
							['label' => 'Malaysian ringgit', 'value' => 'MYR'],
							['label' => 'Norwegian krone', 'value' => 'NOK'],
							['label' => 'New Zealand dollar', 'value' => 'NZD'],
							['label' => 'Philippine piso', 'value' => 'PHP'],
							['label' => 'Polish złoty', 'value' => 'PLN'],
							['label' => 'Romanian leu', 'value' => 'RON'],
							['label' => 'Russian ruble', 'value' => 'RUB'],
							['label' => 'Swedish krona', 'value' => 'SEK'],
							['label' => 'Singapore dollar', 'value' => 'SGD'],
							['label' => 'Thai baht', 'value' => 'THB'],
							['label' => 'New Taiwan dollar', 'value' => 'TWD'],
							['label' => 'United States dollar', 'value' => 'USD'],
							['label' => 'South African rand', 'value' => 'ZAR'],
						],
						'value' => $this->mollie_currency,
						'placeholder' => '',
						'helpText' => '<div style="color: red;">'.__eva('Customer will be charged using this currency').'</div>'
					],
					[
						'attribute' => 'mollie_api_key',
						'stacked' => false,
						'name' => __eva('Api Key (Live)'),
						'component' => 'text-field',
						'value' => $this->mollie_api_key,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'mollie_sandbox',
						'stacked' => true,
						'name' => __eva('Enabled Sandbox environment'),
						'component' => 'boolean-field',
						'type' => 'switch',
						'value' => $this->mollie_sandbox,
						'helpText' => __eva('Use for testing payments')
					],
					[
						'attribute' => 'mollie_api_key_sandbox',
						'stacked' => false,
						'name' => __eva('Api Key (Sandbox)'),
						'component' => 'text-field',
						'value' => $this->mollie_api_key_sandbox,
						'placeholder' => '',
						'helpText' => __eva('')
					],
				]
			]
		];
	}

	public function fieldsSquare(){
		return [
			[
				'attribute' => 'square_active',
				'stacked' => true,
				'name' => __eva('Enable Square Payments'),
				'component' => 'boolean-field',
				'type' => 'switch',
				'value' => $this->square_active,
				'helpText' => __eva('Enable this to allow Square payments')
			],
			[
				'attribute' => 'square_fields',
				'stacked' => true,
				'name' => __eva('Configuration'),
				'component' => 'group-field',
				'textColor' => 'text-indigo-400 text-lg',
				'borderColor' => 'border-indigo-300',
				'backColor' => 'bg-indigo-50',
				'showWhen' => [
					'attribute' => 'square_active',
					'values' => ['true']
				],
				'fields' => [
					[
						'attribute' => 'square_title',
						'stacked' => false,
						'name' => __eva('Title'),
						'component' => 'text-field',
						'value' => $this->square_title,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'square_description',
						'stacked' => false,
						'name' => __eva('Description'),
						'component' => 'textarea-field',
						'value' => $this->square_description,
						'placeholder' => '',
						'helpText' => __eva('This controls the description which the user sees during checkout.')
					],
					[
						'attribute' => 'square_country',
						'stacked' => false,
						'name' => __eva('Country'),
						'component' => 'select-field',
						'options' => [
							['label' => 'Australia', 'value' => 'AU'],
							['label' => 'Canada', 'value' => 'CA'],
							['label' => 'Japan', 'value' => 'JP'],
							['label' => 'United Kingdom', 'value' => 'GB'],
							['label' => 'United States', 'value' => 'US']
						],
						'value' => $this->square_country,
						'placeholder' => '',
						'helpText' => ''
					],
					[
						'attribute' => 'square_currency',
						'stacked' => false,
						'name' => __eva('Currency'),
						'component' => 'select-field',
						'options' => [
							['label' => 'United Arab Emirates dirham', 'value' => 'AED'],
							['label' => 'Afghan afghani', 'value' => 'AFN'],
							['label' => 'Albanian lek', 'value' => 'ALL'],
							['label' => 'Armenian dram', 'value' => 'AMD'],
							['label' => 'Netherlands Antillean guilder', 'value' => 'ANG'],
							['label' => 'Angolan kwanza', 'value' => 'AOA'],
							['label' => 'Argentine peso', 'value' => 'ARS'],
							['label' => 'Australian dollar', 'value' => 'AUD'],
							['label' => 'Aruban florin', 'value' => 'AWG'],
							['label' => 'Azerbaijani manat', 'value' => 'AZN'],
							['label' => 'Bosnia and Herzegovina convertible mark', 'value' => 'BAM'],
							['label' => 'Barbados dollar', 'value' => 'BBD'],
							['label' => 'Bangladeshi taka', 'value' => 'BDT'],
							['label' => 'Bulgarian lev', 'value' => 'BGN'],
							['label' => 'Bahraini dinar', 'value' => 'BHD'],
							['label' => 'Burundian franc', 'value' => 'BIF'],
							['label' => 'Bermudian dollar', 'value' => 'BMD'],
							['label' => 'Brunei dollar', 'value' => 'BND'],
							['label' => 'Boliviano', 'value' => 'BOB'],
							['label' => 'Bolivian Mvdol', 'value' => 'BOV'],
							['label' => 'Brazilian real', 'value' => 'BRL'],
							['label' => 'Bahamian dollar', 'value' => 'BSD'],
							['label' => 'Bhutanese ngultrum', 'value' => 'BTN'],
							['label' => 'Botswana pula', 'value' => 'BWP'],
							['label' => 'Belarusian ruble', 'value' => 'BYR'],
							['label' => 'Belize dollar', 'value' => 'BZD'],
							['label' => 'Canadian dollar', 'value' => 'CAD'],
							['label' => 'Congolese franc', 'value' => 'CDF'],
							['label' => 'WIR Euro', 'value' => 'CHE'],
							['label' => 'Swiss franc', 'value' => 'CHF'],
							['label' => 'WIR Franc', 'value' => 'CHW'],
							['label' => 'Unidad de Fomento', 'value' => 'CLF'],
							['label' => 'Chilean peso', 'value' => 'CLP'],
							['label' => 'Chinese yuan', 'value' => 'CNY'],
							['label' => 'Colombian peso', 'value' => 'COP'],
							['label' => 'Unidad de Valor Real', 'value' => 'COU'],
							['label' => 'Costa Rican colon', 'value' => 'CRC'],
							['label' => 'Cuban convertible peso', 'value' => 'CUC'],
							['label' => 'Cuban peso', 'value' => 'CUP'],
							['label' => 'Cape Verdean escudo', 'value' => 'CVE'],
							['label' => 'Czech koruna', 'value' => 'CZK'],
							['label' => 'Djiboutian franc', 'value' => 'DJF'],
							['label' => 'Danish krone', 'value' => 'DKK'],
							['label' => 'Dominican peso', 'value' => 'DOP'],
							['label' => 'Algerian dinar', 'value' => 'DZD'],
							['label' => 'Egyptian pound', 'value' => 'EGP'],
							['label' => 'Eritrean nakfa', 'value' => 'ERN'],
							['label' => 'Ethiopian birr', 'value' => 'ETB'],
							['label' => 'Euro', 'value' => 'EUR'],
							['label' => 'Fiji dollar', 'value' => 'FJD'],
							['label' => 'Falkland Islands pound', 'value' => 'FKP'],
							['label' => 'Pound sterling', 'value' => 'GBP'],
							['label' => 'Georgian lari', 'value' => 'GEL'],
							['label' => 'Ghanaian cedi', 'value' => 'GHS'],
							['label' => 'Gibraltar pound', 'value' => 'GIP'],
							['label' => 'Gambian dalasi', 'value' => 'GMD'],
							['label' => 'Guinean franc', 'value' => 'GNF'],
							['label' => 'Guatemalan quetzal', 'value' => 'GTQ'],
							['label' => 'Guyanese dollar', 'value' => 'GYD'],
							['label' => 'Hong Kong dollar', 'value' => 'HKD'],
							['label' => 'Honduran lempira', 'value' => 'HNL'],
							['label' => 'Croatian kuna', 'value' => 'HRK'],
							['label' => 'Haitian gourde', 'value' => 'HTG'],
							['label' => 'Hungarian forint', 'value' => 'HUF'],
							['label' => 'Indonesian rupiah', 'value' => 'IDR'],
							['label' => 'Israeli new shekel', 'value' => 'ILS'],
							['label' => 'Indian rupee', 'value' => 'INR'],
							['label' => 'Iraqi dinar', 'value' => 'IQD'],
							['label' => 'Iranian rial', 'value' => 'IRR'],
							['label' => 'Icelandic króna', 'value' => 'ISK'],
							['label' => 'Jamaican dollar', 'value' => 'JMD'],
							['label' => 'Jordanian dinar', 'value' => 'JOD'],
							['label' => 'Japanese yen', 'value' => 'JPY'],
							['label' => 'Kenyan shilling', 'value' => 'KES'],
							['label' => 'Kyrgyzstani som', 'value' => 'KGS'],
							['label' => 'Cambodian riel', 'value' => 'KHR'],
							['label' => 'Comoro franc', 'value' => 'KMF'],
							['label' => 'North Korean won', 'value' => 'KPW'],
							['label' => 'South Korean won', 'value' => 'KRW'],
							['label' => 'Kuwaiti dinar', 'value' => 'KWD'],
							['label' => 'Cayman Islands dollar', 'value' => 'KYD'],
							['label' => 'Kazakhstani tenge', 'value' => 'KZT'],
							['label' => 'Lao kip', 'value' => 'LAK'],
							['label' => 'Lebanese pound', 'value' => 'LBP'],
							['label' => 'Sri Lankan rupee', 'value' => 'LKR'],
							['label' => 'Liberian dollar', 'value' => 'LRD'],
							['label' => 'Lesotho loti', 'value' => 'LSL'],
							['label' => 'Lithuanian litas', 'value' => 'LTL'],
							['label' => 'Latvian lats', 'value' => 'LVL'],
							['label' => 'Libyan dinar', 'value' => 'LYD'],
							['label' => 'Moroccan dirham', 'value' => 'MAD'],
							['label' => 'Moldovan leu', 'value' => 'MDL'],
							['label' => 'Malagasy ariary', 'value' => 'MGA'],
							['label' => 'Macedonian denar', 'value' => 'MKD'],
							['label' => 'Myanmar kyat', 'value' => 'MMK'],
							['label' => 'Mongolian tögrög', 'value' => 'MNT'],
							['label' => 'Macanese pataca', 'value' => 'MOP'],
							['label' => 'Mauritanian ouguiya', 'value' => 'MRO'],
							['label' => 'Mauritian rupee', 'value' => 'MUR'],
							['label' => 'Maldivian rufiyaa', 'value' => 'MVR'],
							['label' => 'Malawian kwacha', 'value' => 'MWK'],
							['label' => 'Mexican peso', 'value' => 'MXN'],
							['label' => 'Mexican Unidad de Inversion', 'value' => 'MXV'],
							['label' => 'Malaysian ringgit', 'value' => 'MYR'],
							['label' => 'Mozambican metical', 'value' => 'MZN'],
							['label' => 'Namibian dollar', 'value' => 'NAD'],
							['label' => 'Nigerian naira', 'value' => 'NGN'],
							['label' => 'Nicaraguan córdoba', 'value' => 'NIO'],
							['label' => 'Norwegian krone', 'value' => 'NOK'],
							['label' => 'Nepalese rupee', 'value' => 'NPR'],
							['label' => 'New Zealand dollar', 'value' => 'NZD'],
							['label' => 'Omani rial', 'value' => 'OMR'],
							['label' => 'Panamanian balboa', 'value' => 'PAB'],
							['label' => 'Peruvian sol', 'value' => 'PEN'],
							['label' => 'Papua New Guinean kina', 'value' => 'PGK'],
							['label' => 'Philippine peso', 'value' => 'PHP'],
							['label' => 'Pakistani rupee', 'value' => 'PKR'],
							['label' => 'Polish złoty', 'value' => 'PLN'],
							['label' => 'Paraguayan guaraní', 'value' => 'PYG'],
							['label' => 'Qatari riyal', 'value' => 'QAR'],
							['label' => 'Romanian leu', 'value' => 'RON'],
							['label' => 'Serbian dinar', 'value' => 'RSD'],
							['label' => 'Russian ruble', 'value' => 'RUB'],
							['label' => 'Rwandan franc', 'value' => 'RWF'],
							['label' => 'Saudi riyal', 'value' => 'SAR'],
							['label' => 'Solomon Islands dollar', 'value' => 'SBD'],
							['label' => 'Seychelles rupee', 'value' => 'SCR'],
							['label' => 'Sudanese pound', 'value' => 'SDG'],
							['label' => 'Swedish krona', 'value' => 'SEK'],
							['label' => 'Singapore dollar', 'value' => 'SGD'],
							['label' => 'Saint Helena pound', 'value' => 'SHP'],
							['label' => 'Sierra Leonean leone', 'value' => 'SLL'],
							['label' => 'Somali shilling', 'value' => 'SOS'],
							['label' => 'Surinamese dollar', 'value' => 'SRD'],
							['label' => 'South Sudanese pound', 'value' => 'SSP'],
							['label' => 'Sao Tome and Príncipe dobra', 'value' => 'STD'],
							['label' => 'Salvadoran colón', 'value' => 'SVC'],
							['label' => 'Syrian pound', 'value' => 'SYP'],
							['label' => 'Swazi lilangeni', 'value' => 'SZL'],
							['label' => 'Thai baht', 'value' => 'THB'],
							['label' => 'Tajikstani somoni', 'value' => 'TJS'],
							['label' => 'Turkmenistan manat', 'value' => 'TMT'],
							['label' => 'Tunisian dinar', 'value' => 'TND'],
							['label' => 'Tongan pa\'anga', 'value' => 'TOP'],
							['label' => 'Turkish lira', 'value' => 'TRY'],
							['label' => 'Trinidad and Tobago dollar', 'value' => 'TTD'],
							['label' => 'New Taiwan dollar', 'value' => 'TWD'],
							['label' => 'Tanzanian shilling', 'value' => 'TZS'],
							['label' => 'Ukrainian hryvnia', 'value' => 'UAH'],
							['label' => 'Ugandan shilling', 'value' => 'UGX'],
							['label' => 'United States dollar', 'value' => 'USD'],
							['label' => 'Uruguay Peso en Unidedades Indexadas', 'value' => 'UYI'],
							['label' => 'Uruguyan peso', 'value' => 'UYU'],
							['label' => 'Uzbekistan som', 'value' => 'UZS'],
							['label' => 'Venezuelan bolívar soberano', 'value' => 'VEF'],
							['label' => 'Vietnamese đồng', 'value' => 'VND'],
							['label' => 'Vanuatu vatu', 'value' => 'VUV'],
							['label' => 'Samoan tala', 'value' => 'WST'],
							['label' => 'CFA franc BEAC', 'value' => 'XAF'],
							['label' => 'Silver', 'value' => 'XAG'],
							['label' => 'Gold', 'value' => 'XAU'],
							['label' => 'European Composite Unit', 'value' => 'XBA'],
							['label' => 'European Monetary Unit', 'value' => 'XBB'],
							['label' => 'European Unit of Account 9', 'value' => 'XBC'],
							['label' => 'European Unit of Account 17', 'value' => 'XBD'],
							['label' => 'East Caribbean dollar', 'value' => 'XCD'],
							['label' => 'Special drawing rights (International Monetary Fund)', 'value' => 'XDR'],
							['label' => 'CFA franc BCEAO', 'value' => 'XOF'],
							['label' => 'Palladium', 'value' => 'XPD'],
							['label' => 'CFP franc', 'value' => 'XPF'],
							['label' => 'Platinum', 'value' => 'XPT'],
							['label' => 'Yemeni rial', 'value' => 'YER'],
							['label' => 'South African rand', 'value' => 'ZAR'],
							['label' => 'Zambian kwacha', 'value' => 'ZMK'],
							['label' => 'Zambian kwacha', 'value' => 'ZMW'],
							['label' => 'Bitcoin', 'value' => 'BTC']
						],
						'value' => $this->square_currency,
						'placeholder' => '',
						'helpText' => '<div style="color: red;">'.__eva('Customer will be charged using this currency').'</div>'
					],
					[
						'attribute' => 'square_app_id',
						'stacked' => false,
						'name' => __eva('Application ID (Live)'),
						'component' => 'text-field',
						'value' => $this->square_app_id,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'square_access_token',
						'stacked' => false,
						'name' => __eva('Access Token (Live)'),
						'component' => 'text-field',
						'value' => $this->square_access_token,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'square_location_id',
						'stacked' => false,
						'name' => __eva('Location ID (Live)'),
						'component' => 'text-field',
						'value' => $this->square_location_id,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'square_sandbox',
						'stacked' => true,
						'name' => __eva('Enabled Sandbox environment'),
						'component' => 'boolean-field',
						'type' => 'switch',
						'value' => $this->square_sandbox,
						'helpText' => __eva('Use for testing payments')
					],
					[
						'attribute' => 'square_app_id_sandbox',
						'stacked' => false,
						'name' => __eva('Application ID (Sandbox)'),
						'component' => 'text-field',
						'value' => $this->square_app_id_sandbox,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'square_access_token_sandbox',
						'stacked' => false,
						'name' => __eva('Access Token (Sandbox)'),
						'component' => 'text-field',
						'value' => $this->square_access_token_sandbox,
						'placeholder' => '',
						'helpText' => __eva('')
					],
					[
						'attribute' => 'square_location_id_sandbox',
						'stacked' => false,
						'name' => __eva('Location ID (Sandbox)'),
						'component' => 'text-field',
						'value' => $this->square_location_id_sandbox,
						'placeholder' => '',
						'helpText' => __eva('')
					],
				]
			]
		];
	}
}
