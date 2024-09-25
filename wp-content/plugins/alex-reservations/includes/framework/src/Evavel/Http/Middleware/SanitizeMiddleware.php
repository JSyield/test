<?php

namespace Evavel\Http\Middleware;

use Evavel\Http\Request\Request;

class SanitizeMiddleware
{
	/**
	 * Handle request
	 *
	 * @param Request $request
	 * @param \Closure $next
	 *
	 * @return mixed
	 */
	public function handle(Request $request, \Closure $next)
	{
		$request->params = $this->sanitize($request->params);

		$params = $request->body_params;

		if (isset($params['has_html_tags']))
		{
			unset($params['has_html_tags']);
			$request->body_params = $this->sanitize($params, true);
		} else {
			$request->body_params = $this->sanitize($params);
		}

		return $next($request);
	}

	/**
	 * Sanitize process
	 *
	 * @param $params
	 *
	 * @return mixed|string
	 */
	public function sanitize($params, $keep_html_tags = false)
	{
		return $this->sanitize_text_or_array_field($params, $keep_html_tags);
	}

	/**
	 * Sanitize array or string input recursively
	 *
	 * @param $array_or_string
	 *
	 * @return mixed|string
	 */
	function sanitize_text_or_array_field($array_or_string, $keep_html_tags = false)
	{
		if(is_string($array_or_string) ) {
			$array_or_string = $this->sanitize_text_field($array_or_string, $keep_html_tags);
		}

		elseif(is_array($array_or_string) ) {
			foreach ( $array_or_string as $key => &$value ) {
				if (is_array($value) ) {
					$value = $this->sanitize_text_or_array_field($value, $keep_html_tags);
				}
				else {
					$value = $this->sanitize_text_field($value, $keep_html_tags);
				}
			}
		}

		return $array_or_string;
	}

	/**
	 * Use WP sanitize function
	 *
	 * @param $string
	 *
	 * @return string
	 */
	function sanitize_text_field($string, $keep_html_tags = false)
	{
		if ($keep_html_tags) {
			$string = wp_filter_post_kses($string);
			$string = str_replace('\"', '"', $string);
			return $string;
		}

		// Keep boolean values
		if ($string === 'true' || $string === true) return true;
		if ($string === 'false' || $string === false) return false;

		// Keep new lines
		return sanitize_textarea_field($string);

		// Does not keep new lines
		//return sanitize_text_field($string);
	}
}
