<?php

namespace Alexr\Settings;

use Alexr\Models\Restaurant;
use Evavel\Models\SettingSimple;

class Panorama extends SettingSimple
{
	public static $table_name = 'restaurant_setting';
	public static $meta_key = 'panoramas';
	public static $pivot_tenant_field = 'restaurant_id';

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	function settingName()
	{
		return __eva('Panoramas');
	}

	function defaultValue()
	{
		return [
			'panoramas' => [],
		];
	}



	public function fields()
	{
		/*
		$panoramas = [
		    [
				'id' => 1,
		        'url' => '/wp-content/uploads/alex-reservations/2023/08/google_panorama_aisushis_00.jpg',
		        'title' => 'Interior view 1',
		        'selected' => false
		    ],
		    [
				'id' => 2,
		        'url' => '/wp-content/uploads/alex-reservations/2023/08/google_panorama_aisushis_01.jpg',
		        'title' => 'Interior view 2',
		        'selected' => false
		    ],
		    [
				'id' => 3,
		        'url' => '/wp-content/uploads/alex-reservations/2023/08/google_panorama_aisushis_02.jpg',
		        'title' => 'Interior view 3',
		        'selected' => false
		    ],
		    [
				'id' => 4,
		        'url' => '/wp-content/uploads/alex-reservations/2023/08/google_panorama_aisushis_03.jpg',
		        'title' => 'Interior view 4',
		        'selected' => false
		    ],
		    [
				'id' => 5,
		        'url' => '/wp-content/uploads/alex-reservations/2023/08/google_panorama_aisushis_04.jpg',
		        'title' => 'Interior view 5',
		        'selected' => false
		    ],
		    [
				'id' => 6,
		        'url' => '/wp-content/uploads/alex-reservations/2023/08/google_panorama_aisushis_05.jpg',
		        'title' => 'Interior view 6',
		        'selected' => false
		    ]
		];
		*/

		return [
			[
				'attribute' => 'panoramas',
				'value' => $this->panoramas
			]
		];

	}

	public function validate()
	{
		return [];
	}

}
