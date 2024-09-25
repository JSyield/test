<?php

namespace Evavel\Http\Controllers;

use Evavel\Http\Request\Request;
use Evavel\Log\Log;

class LogController extends Controller {

	public function index(Request $request)
	{
		$dir = evavel_path_to_log_files();
		if (!is_dir($dir)) {
			$files = [];
		} else {
			//$files = scandir($dir);
			$files = preg_grep('/^([^.])/', scandir($dir));
		}

		$option = get_option(Log::WP_OPTION, 'no');

		$response = [
			'success' => true,
			'files' => array_values($files),
			'log_enabled' => $option == 'no' ? false : true,
		];

		return $this->response($response);
	}

	public function getDownloadUrl(Request $request)
	{
		$filename = $request->filename;

		// Check if file exists
		$dir = evavel_path_to_log_files().'/'.$filename;

		$response = [
			'success' => true,
			'url' => Log::createDownloadLink($filename),
		];

		return $this->response($response);
	}

	public function toggleOption(Request $request)
	{
		$option = $request->option;

		update_option(Log::WP_OPTION, $option);

		return $this->index($request);
	}
}
