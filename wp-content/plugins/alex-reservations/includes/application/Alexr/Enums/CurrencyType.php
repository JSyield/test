<?php

namespace Alexr\Enums;

class CurrencyType {

	const EUR = "EUR";
	const USD = "USD";
	const GBP = "GBP";
	const AUD = "AUD";
	const CAD = "CAD";
	const DKK = "DKK";
	const NOK = "NOK";
	const SEK = "SEK";
	const CHF = "CHF";
	const RS = "RS";
	const BRL = "BRL";
	const ARS = "ARS";
	const UYU = "UYU";
	const COP = "COP";
	const CLP = "CLP";
	const MXN = "MXN";
	const SGD = "SGD";


	// https://en.wikipedia.org/wiki/Template:Most_traded_currencies
	public static function labels()
	{
		return [
			self::EUR => __eva('Euro'),
			self::USD => __eva('U.S. Dollar'),
			self::GBP => __eva('Sterling'),
			self::AUD => __eva('Australian dollar'),
			self::CAD => __eva('Canadian dollar'),
			self::DKK => __eva('Danish krone'),
			self::NOK => __eva('Norwegian krone'),
			self::SEK => __eva('Swedish krona'),
			self::CHF => __eva('Swiss franc'),
			self::RS => __eva('Mauritian rupee'),
			self::BRL => __eva('Real Brasileño'),
			self::ARS => __eva("Peso Argentino"),
			self::UYU => __eva("Peso Uruguayo"),
			self::COP => __eva("Peso Colombiano"),
			self::CLP => __eva("Peso Chileno"),
			self::MXN => __eva("Peso Mexicano"),
			self::SGD => __eva("Singapore dollar")
		];
	}

	public static function symbols()
	{
		return [
			self::EUR => '€',
			self::USD => '$',
			self::GBP => '£',
			self::AUD => 'A$',
			self::CAD => 'C$',
			self::DKK => 'kr',
			self::NOK => 'NOK',
			self::SEK => 'kr',
			self::CHF => 'CHF',
			self::RS => 'Rs',
			self::BRL => 'R$',
			self::ARS => 'ARS',
			self::UYU => 'u$',
			self::COP => 'COL$',
			self::CLP => 'CLP',
			self::MXN => 'MXN',
			self::SGD => 'SGD',
		];
	}

	public static function options()
	{
		$list = self::labels();
		$options = [];

		foreach($list as $key => $label){
			$options[] = [
				'label' => $label,
				'value' => $key
			];
		}

		return $options;
	}

	public static function symbolFor($currency) {
		$currency = strtoupper($currency);
		$list = self::symbols();
		return isset($list[$currency]) ? $list[$currency] : '$';

	}
}
