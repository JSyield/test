<?php


if (!function_exists('__eva')){

	// Useful just for searching the strings to be translated
	// but is returning the same string
	// Let me elaborate the list of words to translate using the function
	// evavel_translate_files
	function __eva($string){
		return $string;
		// Not going to use WP translations for the dashboard
		//return __($string, 'alex-reservations');
	}

	// Return the translation
	function __eva_x($message, $lang = 'en')
	{
		static $cached;

		if (!isset($cached[$lang]))
		{
			if (!is_array($cached)) {
				$cached = [];
			}

			// Comprobar la version original
			$file = EVAVEL_DIR_TRANSLATIONS.$lang.'.json';
			if (file_exists($file)) {
				$cached[$lang] = json_decode(file_get_contents($file), true);
			}

			// Comprobar la traduccion personalizada
			$file_custom = EVAVEL_CUSTOM_TRANSLATION_PATH.$lang.'.json';
			if (file_exists($file_custom)) {
				$cached[$lang] = json_decode(file_get_contents($file_custom), true);
			}
		}

		return isset($cached[$lang][$message]) ? $cached[$lang][$message] : $message;
	}
}




if (! function_exists('evavel_current_user_lang'))
{
	function evavel_current_user_lang()
	{
		// @todo
		return 'en';
	}

	/**
	 * All languages from the config file
	 * @return false|mixed
	 */
	function evavel_languages_all()
	{
		return evavel_config('app.languages', ['en' => 'English']);
	}

	/**
	 * Languages filters by the active ones
	 * @return string[]
	 */
	function evavel_languages_allowed()
	{
		$languages = evavel_languages_all();
		$active = evavel_get_active_languages();

		$list = [];
		foreach($languages as $key => $label) {
			if (isset($active[$key]) && $active[$key] == true){
				$list[$key] = $label;
			}
		}

		if (empty($list)){
			return ['en' => 'English'];
		}

		return $list;
	}

	function evavel_languages_as_options()
	{
		$options = [];
		$list = evavel_languages_allowed();
		foreach ($list as $key => $label) {
			$options[] = [
				'label' => $label,
				'value' => $key
			];
		}
		return $options;
	}

	function evavel_all_languages_as_options()
	{
		$options = [];
		$list = evavel_languages_all();
		foreach ($list as $key => $label) {
			$options[] = [
				'label' => $label,
				'value' => $key
			];
		}
		return $options;
	}

	function evavel_all_timezones()
	{
		$list = [];
		foreach(timezone_identifiers_list() as $tz){
			$list[$tz] = $tz;
		}
		return $list;
	}

	function evavel_all_timezones_as_options()
	{
		$list = [];
		foreach(timezone_identifiers_list() as $tz){
			$list[] = [
				'label' => $tz,
				'value' => $tz
			];
		}
		return $list;
	}

	function evavel_load_language_files_used_by_tenants()
	{
		return evavel_load_language_files(false, true);
	}

	function evavel_load_language_files( $use_all = false, $filter_by_tenants = false )
	{
		if ($use_all) {
			$languages = evavel_languages_all();
		} else {
			$languages = evavel_languages_allowed();
		}

		// Only languages used by restaurants
		if ($filter_by_tenants){

			$languages = evavel_languages_all();
			$keys = array_keys($languages);

			$tenant_class = evavel_tenant_class();
			$langs_tenants = array_unique( $tenant_class::get()->pluck('language') );
			$languages_filtered = [];

			foreach($langs_tenants as $key){
				if (in_array($key, $keys)){
					$languages_filtered[$key] = $languages[$key];
				}
			}

			$languages = $languages_filtered;
		}

		// Always English by default
		if (!isset($languages['en'])) {
			$languages['en'] = 'English';
		}

		$translations = [];
		foreach ($languages as $lang => $label)
		{
			// Try with the custom translation first
			$custom_file = EVAVEL_CUSTOM_TRANSLATION_PATH . $lang . '.json';

			if (file_exists($custom_file)) {
				$json = file_get_contents($custom_file);
				$translations[$lang] = json_decode($json);
			}

			// Try with the original one
			else {

				$file = EVAVEL_DIR_TRANSLATIONS.$lang.'.json';
				if (file_exists($file)){
					$json = file_get_contents($file);
					$translations[$lang] = json_decode($json);
				} else {
					$translations[$lang] = [];
				}
			}

		}

		return $translations;
	}
}


// Translate all the files of the plugin
// evavel_translate_files(['en','es']);

/*if (!function_exists('evavel_translate_files'))
{
	function evavel_translate_files($language)
	{
		$exporter = new \Evavel\Translate\Exporter();

		if (is_string($language))
		{
			$exporter->export($language);
		}
		else if (is_array($language))
		{
			foreach($language as $lang) {
				$exporter->export($lang);
			}
		}


	}
}*/
