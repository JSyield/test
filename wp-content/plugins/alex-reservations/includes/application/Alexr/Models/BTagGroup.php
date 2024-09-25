<?php

namespace Alexr\Models;

use Evavel\Models\Model;

class BTagGroup extends Model
{
	public static $table_name = 'btaggroups';
	public static $pivot_tenant_field = 'restaurant_id';

	protected $casts = [
		'id' => 'int',
		'is_deletable' => 'int',
		'is_vip' => 'int',
		'is_private' => 'int'
	];

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function tags()
	{
		return $this->hasMany(BTag::class, 'group_id');
	}

	public function createAndAttachTags($tags)
	{
		if (!is_array($tags)){
			$tags = explode(',', $tags);
		}

		foreach($tags as $tag) {
			$mtag = BTag::create([
				'restaurant_id' => $this->restaurant_id,
				'group_id' => $this->id,
				'name' => $tag,
				'ordering' => 999,
				'is_deletable' => 1,
				'notes' => ''
			]);
			$mtag->save();
		}
	}

	public static function addPredefinedGroup($group_name, $restaurant_id)
	{
		$groups = alexr_config_tags($restaurant_id, 'bookings');

		foreach ($groups as $key => $data)
		{
			if ($key == $group_name)
			{
				$groupModel = BTagGroup::create([
					'restaurant_id' => $restaurant_id,
					'name' => $group_name,
					'ordering' => 999,
					'color' => $data['colors']['color'],
					'backcolor' => $data['colors']['background'],
					'is_deletable' => 1,
					'is_vip' => $group_name == 'VIP' ? 1 : 0,
					'is_private' => 0,
					'notes' => ''
				]);

				$groupModel->save();
				$groupModel->createAndAttachTags($data['tags']);
			}
		}
	}
}
