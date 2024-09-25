<?php

namespace Alexr\Settings;

use Evavel\Models\SettingSimpleGrouped;

class WidgetMessage extends SettingSimpleGrouped
{
	public static $table_name = 'restaurant_setting';
	public static $meta_key = 'widget_messages';
	public static $pivot_tenant_field = 'restaurant_id';
	public static $component_list_item = 'item-email-template';

	public $templates;

	function settingName()
	{
		return __eva('Messages');
	}

	function defaultValue()
	{
		$this->load_templates_messages();

		$list = [
			'message_pending' => $this->get_template_message_for('message_pending'),
			'message_booked' => $this->get_template_message_for('message_booked'),
		];

		return $list;
	}

	public function listItems()
	{
		return [
			[
				'label' => __eva('Message booking Pending'),
				'slug' => 'message_pending'
			],
			[
				'label' => __eva('Message booking Confirmed'),
				'slug' => 'message_booked'
			],
		];
	}

	public function fields()
	{
		$this->load_templates_messages();

		return [
			'message_pending' => [
				[
					'attribute' => 'message_pending',
					'stacked' => true,
					'name' => __eva('Message for Booking Pending'),
					'component' => 'group-email-languages-field',
					'subfields' => ['content'],
					'previewButton' => false,
					'value' => $this->valueWithLanguagesFor('message_pending'),
					'helpText' => __eva('This message will appear in the widget after the user has submitted the request.'),
					'help' => [
						'content' => __eva('Message content.'),
						//'content' => __eva('Message content. You can use tags.'). ' - <strong>{booking_details} {booking_details_no_duration}</strong>'
					],
					'template' => $this->get_template_message_for('message_pending'),
					'tags' => $this->tags(),
				]
			],
			'message_booked' => [
				[
					'attribute' => 'message_booked',
					'stacked' => true,
					'name' => __eva('Message for Booking Confirmed'),
					'component' => 'group-email-languages-field',
					'subfields' => ['content'],
					'previewButton' => false,
					'value' => $this->valueWithLanguagesFor('message_booked'),
					'helpText' => __eva('This message will appear in the widget after the user has submitted the request.'),
					'help' => [
						'content' => __eva('Message content.'),
						//'content' => __eva('Message content. You can use tags.'). ' - <strong>{booking_details} {booking_details_no_duration}</strong>'
					],
					'template' => $this->get_template_message_for('message_booked'),
					'tags' => $this->tags(),
				]
			]
		];
	}

	public function get_template_message_for($attribute)
	{
		$this->load_templates_messages();

		$result = [];

		foreach($this->templates as $lang => $list) {
			foreach($list as $template_attr => $data) {
				if ($template_attr == $attribute){
					foreach($data as $key => $value) {
						$result[$key.'_'.$lang] = $value;
					}
				}
			}
		}

		return $result;
	}

	protected function load_templates_messages()
	{
		if ($this->templates) return;

		$languages = evavel_languages_allowed();

		$templates = [];
		foreach ($languages as $lang => $label)
		{
			$file = ALEXR_DIR_TEMPLATES_WIDGET_MESSAGES.$lang.'.json';

			if (file_exists($file)){
				$json = file_get_contents($file);
				//$templates[$lang] = json_decode($json);
				// If there is a template file for the email then use it
				$templates[$lang] = $this->replaceByTemplateFiles($lang, json_decode($json, true));
			} else {
				$templates[$lang] = [];
			}
		}

		// Convert from object to array
		//$templates = evavel_json_decode(evavel_json_encode($templates), true);
		// nltobr
		$templates = $this->transformTemplates($templates);

		// Convert to Base64 all values because javascript will decode them
		$templates = alexr_convertArrayToBase64($templates);

		$this->templates = $templates;
	}

	/**
	 * Replace the content with the template file or return the default one
	 * @param $lang
	 * @param $json_decoded
	 *
	 * @return array
	 */
	protected function replaceByTemplateFiles($lang, $json_decoded)
	{
		foreach($json_decoded as $key => $list) {
			$content = 	$this->getFileTemplateContent($lang, $key);
			if ($content) {
				$json_decoded[$key]['content'] = $content['content'];
			}
		}

		return $json_decoded;
	}

	/**
	 * Get the template file content
	 * @param $lang
	 * @param $key
	 *
	 * @return array|false
	 */
	protected function getFileTemplateContent($lang, $key)
	{
		$file = ALEXR_PLUGIN_DIR."includes/dashboard/templates/widget-messages/{$lang}/{$key}.html";

		if (file_exists($file)) {
			$content = file_get_contents($file);
			return [
				'content' => $content
			];
		}

		return false;
	}

	protected function transformTemplates($templates)
	{
		foreach($templates as $lang => $list){
			foreach($list as $type_email => $subject_content) {
				foreach($subject_content as $type_content => $the_content){
					if ($type_content == 'content'){
						$the_content = $this->transformEmailContent($the_content);
						$templates[$lang][$type_email][$type_content] = $the_content;
					}
				}
			}
		}

		return $templates;
	}

	protected function transformEmailContent($content)
	{
		return nl2br($content);
	}

	protected function valueWithLanguagesFor($attribute)
	{
		// And merge with current values
		$values = $this->{$attribute};

		foreach($values as $subfield_attr => $value) {
			if (empty($value)) {
				$values[$subfield_attr] = $this->defaultTemplateFor($attribute, $subfield_attr);
			}
		}

		return $values;
	}

	protected function defaultTemplateFor($attribute, $subfield_attr)
	{
		$lang = false;
		if (preg_match('#(.+)_(.+)#', $subfield_attr, $matches)){
			$sub_attr = $matches[1]; // subject
			$lang = $matches[2]; // en
		}
		if (!$lang) return '';

		if (isset($this->templates[$lang][$attribute][$sub_attr])){
			return $this->templates[$lang][$attribute][$sub_attr];
		}

		return '';
	}

	public function tags()
	{
		// NO tags used yet, can be added later
		$list = [
			//'booking_details' => __eva('Booking details'),
			//'cancel' => __eva('Cancel link'),
		];

		$final_list = [];

		foreach($list as $key => $text) {
			$final_list['{'.$key.'}'] = $text;
		}

		return $final_list;
	}
}
