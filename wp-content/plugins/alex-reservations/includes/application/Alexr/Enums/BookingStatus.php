<?php

namespace Alexr\Enums;

class BookingStatus {

	/**
	'pending': 'Pending',
	'wait-list': 'Wait list',
	'booked': 'Booked',
	'confirmed': 'Confirmed',
	'late': 'Running late',
	'partially-arrived': 'Partially arrived',
	'arrived': 'Arrived',
	'partially-seated': 'Partially seated',
	'seated': 'Seated',
	'appetizer': 'Appetizer',
	'entree': 'Entree',
	'dessert': 'Dessert',
	'check-dropped': 'Check dropped',
	'paid': 'Paid',
	'bussing': 'Bussing needed',
	'finished': 'Finished',
	'cancelled': 'Cancelled',
	'denied': 'Denied',
	'no-show': 'No show',
	 */
	const SELECTED = "selected"; // Booking has been selected but user details are not filled yet
	const PENDING = "pending";
	const PENDING_PAYMENT = "pending_payment";
	const BOOKED = "booked"; // Restaurant confirmed the booking
	const CONFIRMED = "confirmed"; // User has re-confirmed the booking
	const CANCELLED = "cancelled"; // By user
	const DENIED = "denied"; // BY the restaurant
	const NO_SHOW = "no-show"; // By user
	const WAIT_LIST = "wait-list";
	const LATE = "late";
	const PARTIALLY_ARRIVED = "partially-arrived";
	const ARRIVED = "arrived";
	const PARTIALLY_SEATED = "partially-seated";
	const SEATED = "seated";
	const APPETIZER = "appetizer";
	const ENTREE = "entree";
	const DESSERT = "dessert";
	const CHECK_DROPPED = "check-dropped";
	const PAID = "paid";
	const BUSSING = "bussing";
	const FINISHED = "finished";
	const DELETED = "deleted";


	public static function listing() {
		return [
			self::SELECTED  => __eva('Selected'),
			self::PENDING   => __eva('Pending'),
			self::PENDING_PAYMENT => __eva('Pending Payment'),
			self::BOOKED => __eva('Confirmed'),
			self::CONFIRMED => __eva('User Confirmed'),
			self::CANCELLED => __eva('Cancelled'),
			self::DENIED  => __eva('Denied'),
			self::NO_SHOW  => __eva('No show'),
			self::WAIT_LIST  => __eva('No show'),
			self::LATE => __eva("lLate"),
			self::PARTIALLY_ARRIVED => __eva("Partially Arrived"),
			self::ARRIVED => __eva("Arrived"),
			self::PARTIALLY_SEATED => __eva("Partially Seated"),
			self::SEATED => __eva("Seated"),
			self::APPETIZER => __eva("Appetizer"),
			self::ENTREE => __eva("Entree"),
			self::DESSERT => __eva("Dessert"),
			self::CHECK_DROPPED => __eva("Check dropped"),
			self::PAID      => __eva('Paid'),
			self::BUSSING      => __eva('Bussing'),
			self::FINISHED      => __eva('Finished'),
		];
	}

	public static function label($status) {
		$list = self::listing();
		return isset($list[$status]) ? $list[$status] : ucfirst($status);
	}

	// OLD use (for resources)
	public static function styles() {
		return [
			self::SELECTED  => 'status-selected',
			self::PENDING   => 'status-pending',
			self::CONFIRMED => 'status-confirmed',
			self::CANCELLED => 'status-cancelled',
			self::DENIED    => 'status-denied',
			self::SEATED    => 'status-seated',
			self::PAID      => 'status-paid',
		];
	}

	// Son los que se usan en el panel de control
	public static function all_allowed() {
		if (defined('ALEXR_PRO_VERSION')) {
			return [
				'pending',
				//'pending_payment',
				'booked', // Restaurant confirmed
				'confirmed', // User confirmed
				'seated',
				'paid',
				'finished',
				'denied',
				'cancelled',
				'no-show'
			];
		}

		return [
			'pending',
			'booked', // Restaurant confirmed
			'seated',
			'paid',
			'finished',
			'denied',
			'cancelled',
			'no-show'
		];
	}

	// Dashboard al crear nueva reserva status aceptados
	public static function for_new_bookings() {
		return [
			'pending',
			//'wait-list',
			'booked',
			'confirmed',
			'seated',
			//'cancelled'
		];
	}

	// Los asientos ocupados para calcular los libres
	public static function occupied() {
		return [
			self::SELECTED,
			self::PENDING,
			self::PENDING_PAYMENT,
			self::BOOKED,
			self::CONFIRMED,
			self::SEATED,
			self::PAID,
			self::FINISHED,
		];
	}

	// Los asientos ocupados en total incluso reservas ya terminadas
	// Aparece en las shifts en el popup de crear reservas
	public static function occupied_not_selected() {
		return [
			self::PENDING,
			self::PENDING_PAYMENT,
			self::BOOKED,
			self::CONFIRMED,
			self::SEATED,
			self::PAID,
			self::FINISHED,
		];
	}


	// Reservas validas hechas para las metricas
	public static function valid() {
		return [
			//self::SELECTED,
			//self::PENDING,
			//self::PENDING_PAYMENT,
			self::BOOKED,
			self::CONFIRMED,
			//self::LATE,
			//self::PARTIALLY_ARRIVED,
			//self::PARTIALLY_SEATED,
			self::SEATED,
			self::PAID,
			self::FINISHED
			//self::APPETIZER,
			//self::ENTREE,
			//self::DESSERT,
			//self::CHECK_DROPPED,
		];
	}
}
