<?php

namespace Alexr\Http\Traits;

// Get settings for a specific view of the UI dashboard
use Evavel\Http\Request\Request;

trait UISettingsController {

	public function getSettings(Request $request)
	{
		$tenantId = $request->tenant;

		// Esto es mas lento
		/*$settings = [
			'timeline_start' => alexr_get_dashboard_setting($tenantId, 'timeline_start'),
			'timeline_end' => alexr_get_dashboard_setting($tenantId, 'timeline_end'),
			'timeline_bar_width' => alexr_get_dashboard_setting($tenantId, 'timeline_bar_width', 1500),
			'timeline_width_col_table' => alexr_get_dashboard_setting($tenantId, 'timeline_width_col_table', 100),
		];*/

		// Para hacerlos personales para cada usuario puedo usar: timeline_bar_width_{userId}

		// Mejor de golpe
		$all = alexr_get_all_dashboard_settings($tenantId);
		$settings = [
			'timeline_start' => isset($all['timeline_start']) ? intval($all['timeline_start']) : 4 * 3600,
			'timeline_end' => isset($all['timeline_end']) ? intval($all['timeline_end']) : 2 * 3600,
			'timeline_bar_width' => isset($all['timeline_bar_width']) ? intval($all['timeline_bar_width']) : 1500,
			'timeline_width_col_table' => isset($all['timeline_width_col_table']) ? intval($all['timeline_width_col_table']) : 100,
			'time_slider_expire_time' => isset($all['time_slider_expire_time']) ? intval($all['time_slider_expire_time']) : 60
		];


		return $this->response([
			'success' => true,
            'settings' => $settings
		]);
	}

	public function saveSettings(Request $request)
	{
		$tenantId = $request->tenant;
		$settings = $request->settings;

		alexr_save_all_dashboard_settings($tenantId, $settings);

		return $this->response(['success' => true]);
	}
}
