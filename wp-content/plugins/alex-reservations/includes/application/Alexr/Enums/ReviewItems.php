<?php

namespace Alexr\Enums;

class ReviewItems {

	const SCORE1 = 'score1';
	const SCORE2 = 'score2';
	const SCORE3 = 'score3';
	const SCORE4 = 'score4';
	const SCORE5 = 'score5';

	public static function conceptForScore( $score ) {
		$list = [
			self::SCORE1 => __eva('Food'),
			self::SCORE2 => __eva('Service'),
			self::SCORE3 => __eva('Atmosphere'),
			self::SCORE4 => __eva('Cleaning'),
			self::SCORE5 => __eva('Value for money'),
		];

		return isset($list[$score]) ? $list[$score] : $score;
	}

	public static function labelForNumber($number) {
		$list = [
			'1' => __eva('Very bad'),
			'2' => __eva('Bad'),
			'3' => __eva('Regular'),
			'4' => __eva('Good'),
			'5' => __eva('Very good'),
		];
		return isset($list[$number]) ? $list[$number] : $number;
	}
}
