<?php

namespace Evavel\Support;

class Str
{
	/**
	 * Create Stringable class
	 *
	 * @param $string
	 *
	 * @return Stringable
	 */
    public static function of($string)
    {
        return new Stringable($string);
    }

	/**
	 * Humanize string
	 *
	 * @param $value
	 *
	 * @return array|false|string|string[]|null
	 */
    public static function humanize($value)
    {
        if (is_object($value)){
            return static::humanize(evavel_class_basename(get_class($value)));
        }

        return static::title(static::snake($value,' '));
    }

	/**
	 * Title format
	 *
	 * @param $value
	 *
	 * @return array|false|string|string[]|null
	 */
    public static function title($value)
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

	/**
	 * Snake format
	 *
	 * @param $value
	 * @param $delimiter
	 *
	 * @return array|false|mixed|string|string[]|null
	 */
    public static function snake($value, $delimiter = '_')
    {
        if (! ctype_lower($value)){
            $value = preg_replace('/\s+/u', '', ucwords($value));
            $value = preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value);
            $value = static::lower($value, 'UTF-8');
        }
        return $value;
    }

	/**
	 * Kebab format
	 *
	 * @param $value
	 *
	 * @return array|false|mixed|string|string[]|null
	 */
    public static function kebab($value)
    {
        return static::snake($value, '-');
    }

	/**
	 * Just add 's' for now
	 *
	 * @param $value
	 *
	 * @return string
	 */
    public static function plural($value)
    {
        return $value.'s';
    }

	/**
	 * Remove las character (supposed 's')
	 *
	 * @param $value
	 *
	 * @return false|string
	 */
    public static function singular($value)
    {
		if (substr($value, -1) == 's') {
			return substr($value, 0, strlen($value) - 1);
		}

		return $value;
    }

	/**
	 * Lower case
	 *
	 * @param $value
	 *
	 * @return array|false|string|string[]|null
	 */
    public static function lower($value)
    {
        return mb_strtolower($value, 'UTF-8');
    }

	/**
	 * Convert to slug format 'One Two' -> one_two
	 *
	 * @param $title
	 * @param $separator
	 *
	 * @return string
	 */
    public static function slug($title, $separator = '-')
    {
        // Convert all dashes/underscores into separator
        $flip = $separator === '-' ? '_' : '-';

        $title = preg_replace('!['.preg_quote($flip).']+!u', $separator, $title);

        // Replace @ with the word 'at'
        $title = str_replace('@', $separator.'at'.$separator, $title);

        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', static::lower($title));

        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $title);

        return trim($title, $separator);
    }

	/**
	 * Find is substring is included in the string
	 *
	 * @param $haystack
	 * @param string|string[] $needles
	 *
	 * @return bool
	 */
	public static function contains($haystack, $needles)
	{
		foreach((array) $needles as $needle){
			if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Parse class@method into [class, method]
	 *
	 * @param $callback
	 * @param $default
	 *
	 * @return array|false|string[]
	 */
	public static function parseCallback($callback, $default = null)
	{
		return static::contains($callback, '@')
			? explode('@', $callback, 2)
			: [$callback, $default];
	}

	public static function upper($value)
	{
		return mb_strtoupper($value, 'UTF-8');
	}

	public static function substr($string, $start, $length = null)
	{
		return mb_substr($string, $start, $length, 'UTF-8');
	}

	public static function ucfirst($string)
	{
		return static::upper(static::substr($string, 0, 1)).static::substr($string, 1);
	}

	public static function replace($search, $replace, $subject)
	{
		return str_replace($search, $replace, $subject);
	}

	public static function studly($value)
	{
		$words = explode(' ', static::replace(['-', '_'], ' ', $value));

		$studlyWords = array_map(function ($word) {
			return static::ucfirst($word);
		}, $words);

		return implode($studlyWords);
	}

	public static function uuid($prefix = "")
	{
		return uniqid($prefix);
	}

	public static function random($length = 16)
	{
		$string = '';

		while (($len = strlen($string)) < $length) {
			$size = $length - $len;

			$bytes = random_bytes($size);

			$string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
		}

		return $string;
	}
}
