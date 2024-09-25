<?php

namespace Alexr\Settings\Traits;

trait LoadEmailTemplates {

	/**
	 * Template email for attribute
	 * ex: booking_pending
	 * @param $attribute
	 *
	 * @return array
	 */
	public function get_template_email_for($attribute)
	{
		$this->load_templates_email();

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

	protected function transformTemplates($templates)
	{
		foreach($templates as $lang => $list){
			foreach($list as $type_email => $subject_content) {
				foreach($subject_content as $type_content => $the_content){
					if ($type_content == 'content')
					{
						// Transform the content new lines
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
		// No transform for SMS, is plain text
		if ($this->is_sms) return $content;

		//return '<p>'.str_replace(array("\r\n", "\r", "\n"), "</p><p>", $content).'</p>';
		return '<p>'.str_replace(array("\r\n", "\r", "\n"), "</p><p>", $content).'</p>';
		//return nl2br($content);
	}

	/**
	 * Replace the email subject and content with the file template if exists
	 * or return the default one
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
				if ($this->is_email) {
					$json_decoded[$key]['subject'] = $content['subject'];
					$json_decoded[$key]['content'] = $content['content'];
				}
				// SMS
				else {
					$json_decoded[$key]['content'] = $content['content'];
				}
			}
		}

		return $json_decoded;
	}

	/**
	 * Get the email file template content
	 * @param $lang
	 * @param $key
	 *
	 * @return array|false
	 */
	protected function getFileTemplateContent($lang, $key)
	{
		$file = ALEXR_PLUGIN_DIR."includes/dashboard/templates/" . self::FOLDER. "/{$lang}/{$key}.html";

		if (file_exists($file))
		{
			$content = file_get_contents($file);

			if ($this->is_email) {
				// Extract the first line as the subject
				$subject = strtok($content, "\n");

				// From the third line is the content
				$content = explode("\n", $content, 3)[2];

				return [
					'subject' => $subject,
					'content' => $content
				];
			}
			// SMS
			else {
				return [
					'content' => $content
				];
			}
		}

		return false;
	}

	/**
	 * Ex: (booking_pending, subject_en)
	 * @param string $attribute
	 * @param string $subfield_attr
	 *
	 * @return string
	 */
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

	/**
	 * Fill values with default template if empty
	 * ex: (booking_pending)
	 * @param $attribute
	 *
	 * @return bool|\Carbon\Carbon|int|mixed|void|null
	 */
	protected function valueWithLanguagesFor($attribute) {

		// And merge with current values
		$values = $this->{$attribute};

		foreach($values as $subfield_attr => $value) {
			if (empty($value)) {
				$values[$subfield_attr] = $this->defaultTemplateFor($attribute, $subfield_attr);
			}
		}

		return $values;
	}
}
