<?php

namespace Alexr\Enums;

class DateTranslations {

	public static function translate($date_string, $locale)
	{
		$words = explode(' ', $date_string);

		$new_words = [];

		$languages = self::languages();

		foreach($words as $word)
		{
			$word_lower = strtolower($word);
			if (isset($languages[$word_lower]) && isset($languages[$word_lower][$locale]))
			{
				$word_translated = $languages[$word_lower][$locale];
				if ($word == $word_lower){
					$new_words[] = $word_translated;
				} else {
					$new_words[] = ucfirst($word_translated);
				}
			} else {
				$new_words[] = $word;
			}
		}

		return implode(' ', $new_words);
	}

	public static function languages(){
		return [
			'monday' => [
				'es' => 'lunes',
				'fr' => 'lundi',
				'it' => 'lunedì',
				'de' => 'Montag',
				'nl' => 'maandag',
				'da' => 'mandag',
				'no' => 'mandag',
				'sv' => 'måndag',
				'el' => 'Δευτέρα'
			],
			'tuesday' => [
				'es' => 'martes',
				'fr' => 'mardi',
				'it' => 'martedì',
				'de' => 'Dienstag',
				'nl' => 'dinsdag',
				'da' => 'tirsdag',
				'no' => 'tirsdag',
				'sv' => 'tisdag',
				'el' => 'Τρίτη'
			],
			'wednesday' => [
				'es' => 'miércoles',
				'fr' => 'mercredi',
				'it' => 'mercoledì',
				'de' => 'Mittwoch',
				'nl' => 'woensdag',
				'da' => 'onsdag',
				'no' => 'onsdag',
				'sv' => 'onsdag',
				'el' => 'Τετάρτη'
			],
			'thursday' => [
				'es' => 'jueves',
				'fr' => 'jeudi',
				'it' => 'giovedì',
				'de' => 'Donnerstag',
				'nl' => 'donderdag',
				'da' => 'torsdag',
				'no' => 'torsdag',
				'sv' => 'torsdag',
				'el' => 'Πέμπτη'
			],
			'friday' => [
				'es' => 'viernes',
				'fr' => 'vendredi',
				'it' => 'venerdì',
				'de' => 'Freitag',
				'nl' => 'vrijdag',
				'da' => 'fredag',
				'no' => 'fredag',
				'sv' => 'fredag',
				'el' => 'Παρασκευή'
			],
			'saturday' => [
				'es' => 'sábado',
				'fr' => 'samedi',
				'it' => 'sabato',
				'de' => 'Samstag',
				'nl' => 'zaterdag',
				'da' => 'lørdag',
				'no' => 'lørdag',
				'sv' => 'lördag',
				'el' => 'Σάββατο'
			],
			'sunday' => [
				'es' => 'domingo',
				'fr' => 'dimanche',
				'it' => 'domenica',
				'de' => 'Sonntag',
				'nl' => 'zondag',
				'da' => 'søndag',
				'no' => 'søndag',
				'sv' => 'söndag',
				'el' => 'Κυριακή'
			],
			'january' => [
				'es' => 'enero',
				'fr' => 'janvier',
				'it' => 'gennaio',
				'de' => 'Januar',
				'nl' => 'januari',
				'da' => 'januar',
				'no' => 'januar',
				'sv' => 'januari',
				'el' => 'Ιανουάριος'
			],
			'february' => [
				'es' => 'febrero',
				'fr' => 'février',
				'it' => 'febbraio',
				'de' => 'Februar',
				'nl' => 'februari',
				'da' => 'februar',
				'no' => 'februar',
				'sv' => 'februari',
				'el' => 'Φεβρουάριος'
			],
			'march' => [
				'es' => 'marzo',
				'fr' => 'mars',
				'it' => 'marzo',
				'de' => 'März',
				'nl' => 'maart',
				'da' => 'marts',
				'no' => 'mars',
				'sv' => 'mars',
				'el' => 'Μάρτιος'
			],
			'april' => [
				'es' => 'abril',
				'fr' => 'avril',
				'it' => 'aprile',
				'de' => 'April',
				'nl' => 'april',
				'da' => 'april',
				'no' => 'april',
				'sv' => 'april',
				'el' => 'Απρίλιος'
			],
			'may' => [
				'es' => 'mayo',
				'fr' => 'mai',
				'it' => 'maggio',
				'de' => 'Mai',
				'nl' => 'mei',
				'da' => 'maj',
				'no' => 'mai',
				'sv' => 'maj',
				'el' => 'Μάιος'
			],
			'june' => [
				'es' => 'junio',
				'fr' => 'juin',
				'it' => 'giugno',
				'de' => 'Juni',
				'nl' => 'juni',
				'da' => 'juni',
				'no' => 'juni',
				'sv' => 'juni',
				'el' => 'Ιούνιος'
			],
			'july' => [
				'es' => 'julio',
				'fr' => 'juillet',
				'it' => 'luglio',
				'de' => 'Juli',
				'nl' => 'juli',
				'da' => 'juli',
				'no' => 'juli',
				'sv' => 'juli',
				'el' => 'Ιούλιος'
			],
			'august' => [
				'es' => 'agosto',
				'fr' => 'août',
				'it' => 'agosto',
				'de' => 'August',
				'nl' => 'augustus',
				'da' => 'august',
				'no' => 'august',
				'sv' => 'augusti',
				'el' => 'Αύγουστος'
			],
			'september' => [
				'es' => 'septiembre',
				'fr' => 'septembre',
				'it' => 'settembre',
				'de' => 'September',
				'nl' => 'september',
				'da' => 'september',
				'no' => 'september',
				'sv' => 'september',
				'el' => 'Σεπτέμβριος'
			],
			'october' => [
				'es' => 'octubre',
				'fr' => 'octobre',
				'it' => 'ottobre',
				'de' => 'Oktober',
				'nl' => 'oktober',
				'da' => 'oktober',
				'no' => 'oktober',
				'sv' => 'oktober',
				'el' => 'Οκτώβριος'
			],
			'november' => [
				'es' => 'noviembre',
				'fr' => 'novembre',
				'it' => 'novembre',
				'de' => 'November',
				'nl' => 'november',
				'da' => 'november',
				'no' => 'november',
				'sv' => 'november',
				'el' => 'Νοέμβριος'
			],
			'december' => [
				'es' => 'diciembre',
				'fr' => 'décembre',
				'it' => 'dicembre',
				'de' => 'Dezember',
				'nl' => 'december',
				'da' => 'december',
				'no' => 'desember',
				'sv' => 'december',
				'el' => 'Δεκέμβριος'
			],
		];
	}
}
