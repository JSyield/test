<?php

namespace Alexr\Http\Controllers;

use Evavel\Eva;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;

class TranslateController extends Controller {

	/**
	 * Get translated string for a file
	 * @param Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function index(Request $request)
	{
		$lang = $request->lang;

		$json = $this->getFilesOriginalAndCustom($lang);

		if ($json['json_original'] == '{}') {
			return $this->response([
				'success' => false,
				'error' => __eva('Language file not found!')
			]);
		}

		return $this->response([
			'success' => true,
			'original' => json_decode($json['json_original']),
			'strings' => json_decode($json['json_custom'])
		]);
	}

	protected function getFilesOriginalAndCustom($lang)
	{
		$file_custom = ALEXR_CUSTOM_TRANSLATION_PATH . "{$lang}.json";
		$file_original = EVAVEL_DIR_TRANSLATIONS.$lang.'.json';

		if (!file_exists($file_original)){
			return [
				'json_custom' => '{}',
				'json_original' => '{}'
			];
		}

		$json_original = file_get_contents($file_original);

		if (file_exists($file_custom)) {
			$json_custom = file_get_contents($file_custom);
		} else {
			$json_custom = $json_original;
		}

		return [
			'json_custom' => $json_custom,
			'json_original' => $json_original
		];
	}

	/**
	 * Save the translation file
	 * @param Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function save(Request $request)
	{
		$lang = $request->lang;

		$strings = base64_decode($request->strings);

		// @TODO check it is valid json
		// $strings_decoded = json_decode($strings, true);

		$path = ALEXR_CUSTOM_TRANSLATION_PATH;
		$file = $path . "{$lang}.json";

		if (!is_dir($path)) {
			//mkdir($path, 0770, true);
			$folder_created = wp_mkdir_p($path);
			if (!$folder_created) {
				return $this->response([
					'success' => false,
					'error' => __eva('Error creating folder.')
				]);
			}
		}

		$strings = str_replace('","', '"'.",\n".'"', $strings);

		file_put_contents($file, $strings);

		return $this->response([
			'success' => true
		]);

	}

	/**
	 * Get the active languages
	 * @return \WP_REST_Response
	 */
	public function activeLanguages(Request $request)
	{
		// Get the list of languages and the activation
		$languages = evavel_languages_all();
		$active = alexr_get_active_languages();

		return $this->response([
			'success' => true,
			'languages' => $languages,
			'active_languages' => $active
		]);
	}

	public function saveActiveLanguages(Request $request)
	{
		$active = $request->active;
		alexr_save_setting('active_languages', $active);

		return $this->response([
			'success' => true
		]);
	}

	/**
	 * Sync translated language with the default file that has more strings
	 *
	 * @param Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function sync(Request $request)
	{
		$lang = $request->lang;

		return $this->syncForLanguage($lang);
	}

	public function syncForLanguage($lang)
	{
		$json = $this->getFilesOriginalAndCustom($lang);

		$json_decode_original = json_decode($json['json_original'], true);
		$json_decode_custom = json_decode($json['json_custom'], true);

		foreach ( $json_decode_original as $key => $value ) {
			if (isset($json_decode_custom[$key])) {
				$json_decode_original[$key] = $json_decode_custom[$key];
			}
		}

		$strings = json_encode($json_decode_original);

		$path = ALEXR_CUSTOM_TRANSLATION_PATH;
		$file = $path . "{$lang}.json";
		$strings = str_replace('","', '"'.",\n".'"', $strings);
		file_put_contents($file, $strings);

		return $this->response([
			'success' => true
		]);
	}

	// Not needed
	public function download(Request $request)
	{
		return $this->response([
			'success' => true
		]);
	}

	/**
	 * To sync all custom languages at once
	 *
	 * @return void
	 */
	public static function syncAllFiles()
	{
		$controller = new TranslateController();

		$languages = evavel_languages_all();

		foreach($languages as $lang => $label)
		{
			$file_custom = ALEXR_CUSTOM_TRANSLATION_PATH . "{$lang}.json";
			if (file_exists($file_custom)) {
				$controller->syncForLanguage($lang);
			}
		}
	}
}
