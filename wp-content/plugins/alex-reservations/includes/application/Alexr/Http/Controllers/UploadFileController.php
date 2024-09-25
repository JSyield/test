<?php

namespace Alexr\Http\Controllers;

use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;

class UploadFileController extends Controller
{
	// https://www.w3schools.com/php/php_file_upload.asp
	public function upload(Request $request)
	{
		$file = $_FILES['file'];

		// Target dir / url
		$upload_dir = wp_upload_dir();
		$date = evavel_date_now()->format('Y/m');
		$base_dir = $upload_dir['basedir'].'/'.ALEXR_UPLOAD_FOLDER.'/'.$date;
		$base_url = $upload_dir['baseurl'].'/'.ALEXR_UPLOAD_FOLDER.'/'.$date;

		if (!file_exists($base_dir)) {
			//mkdir($base_dir, 0777, true);
			$folder_created = wp_mkdir_p($base_dir);
			if (!$folder_created) {
				return $this->response([
					'success' => false,
					'error' => __eva('Error creating folder.')
				]);
			}
		}

		$file_name = $file['name'];
		$file_name = preg_replace('/[^a-z0-9_\.\-[:space:]]/i', '_', $file_name);

		$target_dir_file = $base_dir.'/'.$file_name;
		$target_url_file = $base_url.'/'.$file_name;

		$result = copy($file['tmp_name'], $target_dir_file);

		if (!$result) {
			return $this->response([
				'success' => false,
				'error' => __eva('Error saving file.')
			]);
		}

		return $this->response([
			'success' => true,
			'file_path' => $target_dir_file,
			'file_url' => $target_url_file,
			'message' => __eva('Uploaded.')
		]);
	}

	public function delete(Request $request)
	{
		$file_url = $request->file_url;

		$upload_dir = wp_upload_dir();
		$base_dir = $upload_dir['basedir'];
		$base_url = $upload_dir['baseurl'];

		$file_dir = str_replace($base_url, $base_dir, $file_url);

		$result = unlink($file_dir);

		if (!$result) {
			return $this->response([
				'success' => false,
				'error' => __eva('Error deleting file.')
			]);
		}

		return $this->response([
			'success' => true,
			'message' => __eva('Deleted.')
		]);
	}
}
