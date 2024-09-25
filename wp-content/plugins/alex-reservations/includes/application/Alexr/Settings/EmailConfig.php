<?php

namespace Alexr\Settings;

use Evavel\Models\SettingSimpleGrouped;

class EmailConfig extends SettingSimpleGrouped
{
	public static $table_name = 'restaurant_setting';
	public static $meta_key = 'email_config';
	public static $pivot_tenant_field = 'restaurant_id';
	public static $component_list_item = 'item-email-template';

	function settingName()
	{
		return __eva('Email Configuration');
	}

	public function listItems()
	{
		return [
			[
				'label' => __eva('EMAIL Layout'),
				'slug' => 'email_layout'
			],
			[
				'label' => __eva('SMTP configuration'),
				'slug' => 'stmp_configuration'
			],
			[
				'label' => __eva('SMS configuration'),
				'slug' => 'sms_configuration'
			],
		];
	}

	public function fields()
	{
		return [
			'email_layout' => $this->fieldsLayout(),
			'stmp_configuration' => $this->fieldsSMTP(),
			'sms_configuration' => $this->fieldsSMS(),
		];
	}

	protected function fieldsSMTP()
	{
		return [
			[
				'attribute' => 'use_smtp',
				'stacked' => true,
				'name' => __eva('SMTP for sending emails (recommended)'),
				'component' => 'boolean-field',
				'type' => 'switch',
				'value' => $this->use_smtp,
				'helpText' => __eva('By default emails are sent using WP functionality')
			],
			[
				'attribute' => 'smtp_fields',
				'stacked' => true,
				'name' => __eva('SMTP'),
				'component' => 'group-field',
				'textColor' => 'text-indigo-400 text-lg',
				'borderColor' => 'border-indigo-300',
				'backColor' => 'bg-indigo-50',
				'showWhen' => [
					'attribute' => 'use_smtp',
					'values' => ['true']
				],
				'fields' => [
					[
						'attribute' => 'smtp_from_email',
						'stacked' => false,
						'name' => __eva('From email address'),
						'component' => 'text-field',
						'value' => $this->smtp_from_email,
						'placeholder' => 'alex@gmail.com',
						'helpText' => __eva('This email address will be used in the "From" field.')
					],
					[
						'attribute' => 'smtp_from_name',
						'stacked' => false,
						'name' => __eva('From Name'),
						'component' => 'text-field',
						'value' => $this->smtp_from_name,
						'placeholder' => 'Joe',
						'helpText' => __eva('This text will be used in the "FROM" field')
					],
					[
						'attribute' => 'smtp_reply_to',
						'stacked' => false,
						'name' => __eva('Reply-To Email Address'),
						'component' => 'text-field',
						'value' => $this->smtp_reply_to,
						'placeholder' => 'alex@gmail.com',
						'helpText' => __eva("Optional. This email address will be used in the 'Reply-To' field of the email. Leave it blank to use 'From' email as the reply-to value.")
					],
					[
						'attribute' => 'smtp_bbc_email',
						'stacked' => false,
						'name' => __eva('BCC Email Address'),
						'component' => 'text-field',
						'value' => $this->smtp_bbc_email,
						'placeholder' => '',
						'helpText' => __eva("Optional. This email address will be used in the 'BCC' field of the outgoing emails. Use this option carefully since all your outgoing emails from this site will add this address to the BCC field. You can also enter multiple email addresses (comma separated).")
					],
					[
						'attribute' => 'smtp_type_encryption',
						'stacked' => false,
						'name' => __eva('Type of Encryption'),
						'component' => 'select-field',
						'value' => $this->smtp_type_encryption,
						'placeholder' => '',
						'options' => [
							['label' => 'None', 'value' => 'none'],
							['label' => 'SSL/TLS', 'value' => 'ssl'],
							['label' => 'STARTTLS', 'value' => 'tls'],
						],
						'helpText' => __eva("For most servers SSL/TLS is the recommended option")
					],
					[
						'attribute' => 'smtp_host',
						'stacked' => false,
						'name' => __eva('SMTP Host'),
						'component' => 'text-field',
						'value' => $this->smtp_host,
						'placeholder' => '',
						'helpText' => __eva("Your mail server")
					],
					[
						'attribute' => 'smtp_port',
						'stacked' => false,
						'name' => __eva('SMTP Port'),
						'component' => 'text-field',
						'value' => $this->smtp_port,
						'placeholder' => '25',
						'helpText' => __eva("The port to your mail server")
					],
					[
						'attribute' => 'smtp_authentication',
						'stacked' => false,
						'name' => __eva('SMTP Authentication'),
						'component' => 'select-field',
						'value' => $this->smtp_authentication,
						'placeholder' => '',
						'options' => [
							['label' => 'Yes', 'value' => 'yes'],
							['label' => 'No', 'value' => 'no']
						],
						'helpText' => __eva("This options should always be checked 'Yes'")
					],
					[
						'attribute' => 'smtp_username',
						'stacked' => false,
						'name' => __eva('SMTP Username'),
						'component' => 'text-field',
						'value' => $this->smtp_username,
						'placeholder' => '',
						'helpText' => __eva("The username to login to your mail server")
					],
					[
						'attribute' => 'smtp_password',
						'stacked' => false,
						'name' => __eva('SMTP Password'),
						'component' => 'password-field',
						'value' => $this->smtp_password,
						'placeholder' => '',
						'helpText' => __eva("The password to login to your mail server")
					],
				]
			],
			[
				'attribute' => 'use_smtp_phpmailer',
				'stacked' => true,
				'name' => __eva('Use PHPMailer to send emails'),
				'component' => 'boolean-field',
				'type' => 'switch',
				'value' => $this->use_smtp_phpmailer,
				'helpText' => __eva('Skip the WP mail funcion and send them using PHPMailer.')
				              .'<br>'
				              .__eva('Activate this only if you know what you are doing.')
			],
			[
				'attribute' => 'test_email_fields',
				'stacked' => true,
				'name' => __eva('Test Email'),
				'component' => 'group-test-email-field',
				'textColor' => 'text-indigo-400 text-lg',
				'borderColor' => 'border-indigo-300',
				'backColor' => 'bg-indigo-50',
				'showWhen' => [
					'attribute' => 'use_smtp',
					'values' => ['true']
				],
				'helpText' => __eva("Use this section to send an email from your server using the SMTP details to see if the email gets delivered.") . '<br>' .
				              __eva('Be sure to save your SMTP configuration before sending the email.'),
				'fields' => [
					[
						'attribute' => 'test_email_to',
						'stacked' => false,
						'name' => __eva('To'),
						'component' => 'text-field',
						'value' => $this->test_email_to,
						'placeholder' => '',
						'helpText' => __eva("Enter the recipient's email address")
					],
					[
						'attribute' => 'test_email_subject',
						'stacked' => false,
						'name' => __eva('Subject'),
						'component' => 'text-field',
						'value' => $this->test_email_subject,
						'placeholder' => '',
						'helpText' => __eva("Enter a subject for your message")
					],
					[
						'attribute' => 'test_email_message',
						'stacked' => false,
						'name' => __eva('Message'),
						'component' => 'textarea-field',
						'value' => alexr_transform_textarea_to_new_lines($this->test_email_message),
						'placeholder' => '',
						'helpText' => __eva("Write your email message")
					],
				],
			]
		];
	}

	protected function fieldsSMS()
	{
		return [
			[
				'attribute' => 'use_sms',
				'stacked' => true,
				'name' => __eva('Enable SMS notifications.'),
				'component' => 'boolean-field',
				'type' => 'switch',
				'value' => $this->use_sms,
				'helpText' => __eva('You need a Twilio account')
			],
			[
				'attribute' => 'sms_fields',
				'stacked' => true,
				'name' => __eva('Twilio account'),
				'component' => 'group-field',
				'textColor' => 'text-indigo-400 text-lg',
				'borderColor' => 'border-indigo-300',
				'backColor' => 'bg-indigo-50',
				'showWhen' => [
					'attribute' => 'use_sms',
					'values' => ['true']
				],
				'fields' => [
					[
						'attribute' => 'twilio_sid',
						'stacked' => false,
						'name' => __eva('Twilio account SID'),
						'component' => 'text-field',
						'value' => $this->twilio_sid,
						'placeholder' => '',
						//'helpText' => __eva('Twilio Account SID.')
					],
					[
						'attribute' => 'twilio_token',
						'stacked' => false,
						'name' => __eva('Twilio Auth Token'),
						'component' => 'password-field',
						'value' => $this->twilio_token,
						'placeholder' => '',
						//'helpText' => __eva('Twilio Auth Token.')
					],
					[
						'attribute' => 'twilio_phone',
						'stacked' => false,
						'name' => __eva('Twilio phone number'),
						'component' => 'text-field',
						'value' => $this->twilio_phone,
						'placeholder' => '+16166666666',
						//'helpText' => __eva('Twilio Auth Token.')
					],
					// @TODO pending twilio whatsapp business
					/*[
						'attribute' => 'use_whatsapp',
						'stacked' => true,
						'name' => __eva('WhatsApp active.'),
						'component' => 'boolean-field',
						'type' => 'switch',
						'value' => $this->use_whatsapp,
						'helpText' => __eva('You can also use WhatsApp messages from Twilio')
					],
					[
						'attribute' => 'twilio_whatsapp_phone',
						'stacked' => false,
						'name' => __eva('Twilio WhatsApp number'),
						'component' => 'text-field',
						'value' => $this->twilio_whatsapp_phone,
						'placeholder' => '+14155555555',
						'showWhen' => [
							'attribute' => 'use_whatsapp',
							'values' => ['true']
						],
					],*/
				]
			],
			[
				'attribute' => 'test_sms_fields',
				'stacked' => true,
				'name' => __eva('Test SMS'),
				'component' => 'group-test-sms-field',
				'textColor' => 'text-indigo-400 text-lg',
				'borderColor' => 'border-indigo-300',
				'backColor' => 'bg-indigo-50',
				'showWhen' => [
					'attribute' => 'use_sms',
					'values' => ['true']
				],
				'with_whatsapp' => false, // @todo enable whatsapp when ready
				'helpText' => __eva("Use this section to send a SMS for testing").'<br>'.
				              __eva('Be sure to save your SMS configuration before sending this message.'),
				'fields' => [
					[
						'attribute' => 'test_sms_phone',
						'stacked' => false,
						'name' => __eva('Phone number'),
						'component' => 'text-field',
						'value' => $this->test_sms_phone,
						'placeholder' => '+1 12345678',
						'helpText' => __eva("Enter the country code and the phone number")
					],
				]
			],
		];
	}

	protected function fieldsLayout()
	{
		return [
			[
				'attribute' => 'email_logo_img_url',
				'stacked' => false,
				'name' => __eva('Logo image'),
				'component' => 'image-upload-field',
				'options' => [
					'accept'    => 'image/png, image/jpeg',
					'maxWidth'  => 768,
					'maxHeight' => 250,
					'checkDimensions' => true,
					//'resize' => false
				],
				'value' => $this->email_logo_img_url,
				'placeholder' => '',
				'helpText' => __eva('You can upload the image directly or use the url field below.')
			],
			[
				'attribute' => 'email_logo_img_url_2',
				'stacked' => false,
				'name' => __eva('Logo url'),
				'component' => 'text-field',
				'value' => $this->email_logo_img_url_2,
				'placeholder' => 'https://restaurant.com/logo.jpg',
				'helpText' => __eva('Enter the URL of the logo image. If not empty this url will be used.')
			],


			/*[
				'attribute' => 'email_logo_img',
				'stacked' => false,
				'name' => __eva('Logo image'),
				'component' => 'image-field',
				'options' => [
					'accept'    => 'image/png, image/jpeg',
					'maxWidth'  => 250,
					'maxHeight' => 250,
					'resize' => true
				],
				'value' => $this->email_logo_img,
				'placeholder' => '',
				'helpText' => __eva('The logo will appear at the top of the email.')
			],*/
			[
				'attribute' => 'email_logo_link',
				'stacked' => false,
				'name' => __eva('Logo link'),
				'component' => 'text-field',
				'value' => $this->email_logo_link,
				'placeholder' => '',
				'helpText' => __eva('The logo will link to this URL.')
			],
			[
				'attribute' => 'email_header',
				'stacked' => false,
				'name' => __eva('Email header'),
				//'component' => 'text-field',
				'component' => 'tiptap-field',
				'useBase64' => true,
				'buttons' => ['bold','italic','strikethrough','underline','divider',
								'paragraph', 'text-wrap', 'divider',
								'align-left', 'align-center', 'align-right',
				],
				'value' => $this->email_header,
				'placeholder' => 'Restaurant',
				'helpText' => __eva('This header will appear at the top of the email. You can insert the restaurant name here.')
			],
			[
				'attribute' => 'email_footer',
				'stacked' => false,
				'name' => __eva('Email footer'),
				//'component' => 'trix-base64-field',
				'component' => 'tiptap-field',
				'useBase64' => true,
				'value' => $this->email_footer,
				'placeholder' => 'Restaurant',
				'helpText' => __eva('This footer will appear at the bottom of the email.')
			],
			[
				'attribute' => 'email_link_color',
				'stacked' => false,
				'style' => '',
				'name' => __eva('Link color'),
				'component' => 'color-field',
				'value' => $this->email_link_color,
				'open' => false,
				'hideInput' => false
			],
			[
				'attribute' => 'email_button_bg_color',
				'stacked' => false,
				'style' => '',
				'name' => __eva('Button background color'),
				'component' => 'color-field',
				'value' => $this->email_button_bg_color,
				'open' => false,
				'hideInput' => false
			],
			[
				'attribute' => 'email_button_text_color',
				'stacked' => false,
				'style' => '',
				'name' => __eva('Button text color'),
				'component' => 'color-field',
				'value' => $this->email_button_text_color,
				'open' => false,
				'hideInput' => false
			],
		];
	}

	function defaultValue()
	{
		return [
			'email_header' => '',
			'email_footer' => '',
 			'email_logo_img_url' => '',
			//'email_logo_img' => '',
			'email_logo_link' => '',

			'use_smtp' => false,
			'smtp_from_email' => '',
			'smtp_from_name' => '',
			'smtp_reply_to' => '',
			'smtp_bbc_email' => '',
			'smtp_type_encryption' => '',
			'smtp_host' => '',
			'smtp_port' => '',
			'smtp_authentication' => '',
			'smtp_username' => '',
			'smtp_password' => '',
			'test_email_to' => '',
			'test_email_subject' => '',

			'use_sms' => false,
			'twilio_sic' => '',
			'twilio_token' => '',
			'twilio_phone' => ''
		];
	}
}
