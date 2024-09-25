<?php

namespace Alexr\Settings\Traits;

use Alexr\Models\Table;
use Alexr\Settings\Event;
use Alexr\Settings\Shift;

Trait FieldsShiftEvent {

	public function fields() {

		$tabs = [
			[
				'tab' => 'schedule',
				'tab_label' => __eva('Schedule'),
				'help_online' => ['settings-shifts', 'settings-events', 'shifts-example'],
				'fields' => $this->fieldsSchedule(),
				'icon' => 'IconCalendar'
			],
			[
				'tab' => 'working',
				'tab_label' => __eva('Working hours'),
				'help_online' => false,
				'fields' => $this->fieldsWorking(),
				'icon' => 'IconClock'
			],
			[
				'tab' => 'availability',
				'tab_label' => __eva('Reservation'),
				//'help_online' => 'shift-availability',
				'fields' => $this->fieldsAvailability(),
				'icon' => 'IconBookings'
			],
			[
				'tab' => 'status',
				'tab_label' => __eva('Status'),
				'help_online' => 'settings-pending-confirmed',
				'fields' => $this->fieldsStatus(),
				'icon' => 'IconNotes'
			],
			[
				'tab' => 'payment',
				'tab_label' => __eva('Payment'),
				'help_online' => ['payments-settings', 'payments-shifts'],
				'fields' => $this->fieldsPayment(),
				'icon' => 'IconDollar'
			],
		];

		$tabs = apply_filters('alexr-shift-fields-tabs', $tabs, $this);

		return $tabs;

		/*return [
			'left' => $this->fieldsSchedule(),
			'right' => $this->fieldsAvailability()
		];*/
	}

	/**
	 * THIS TRAIT IS USED FOR SHIFTS AND EVENTS
	 * so I filter the fields based on the class Shift.php / Event.php
	 * @param $fields
	 *
	 * @return array
	 */
	public function filterByClass($fields) {
		$final_fields = [];

		foreach($fields as $field) {
			if (isset($field['only_for_class'])) {
				if (get_class($this) == $field['only_for_class']) {
					$final_fields[] = $field;
				}
			} else {
				$final_fields[] = $field;
			}
		}

		return $final_fields;
	}

	public function fieldsSchedule() {
		$fields = [
			[
				'attribute' => 'active',
				'stacked' => true,
				'style' => 'display: inline-block; width: 20%; vertical-align: top;',
				'name' => __eva('Active'),
				'component' => 'boolean-field',
				'type' => 'switch',
				'value' => $this->active
			],
			[
				'attribute' => 'color',
				'stacked' => true,
				'style' => 'display: inline-block; width: 30%; vertical-align: top;',
				'name' => __eva('Back Color'),
				'component' => 'color-field',
				'value' => $this->color,
				'open' => false,
				'hideInput' => false
			],
			[
				'attribute' => 'color_text',
				'stacked' => true,
				'style' => 'display: inline-block; width: 30%; vertical-align: top;',
				'name' => __eva('Text Color'),
				'component' => 'color-field',
				'value' => $this->color_text,
				'open' => false,
				'hideInput' => false
			],

			// Shift will be recurrent always for now
			// I can add other types later
			[
				'attribute' => 'type',
				'stacked' => true,
				'style' => 'display: inline-block; width: 20%; vertical-align: top;',
				'name' => __eva('Type'),
				'component' => 'select-field',
				'value' => $this->type,
				'isReadonly' => true,
				'options' => [
					['label' => __eva('Recurring'), 'value' => 'recurring'],
					['label' => __eva('Event'), 'value' => 'event']
				]
			],

			[
				'attribute' => 'name',
				'stacked' => true,
				//'style' => 'display: inline-block; width: 80%;',
				'inputClass' => 'form-input-bordered-highlight',
				'name' => __eva('Name'),
				'component' => 'text-field',
				'value' => $this->name
			],
			[
				'attribute' => 'public_notes',
				'stacked' => true,
				'name' => __eva('Description to show to the customers'),
				//'component' => 'textarea-field',
				'component' => 'tiptap-field',
				'useBase64' => true,
				'buttons' => ['bold', 'italic', 'underline','divider',  'paragraph', 'text-wrap', 'h-1', 'h-2', 'h-3', 'align-left', 'align-center', 'align-right'],
				'value' => alexr_transform_textarea_to_new_lines($this->public_notes),
			],
			[
				'only_for_class' => Shift::class,
				'attribute' => 'start_date',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Start Date'),
				'component' => 'date-field',
				'value' => $this->start_date
			],
			[
				'only_for_class' => Shift::class,
				'attribute' => 'end_date',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('End Date'),
				'component' => 'date-field',
				'value' => $this->end_date
			],
			[
				'only_for_class' => Shift::class,
				'attribute' => 'days_of_week',
				'stacked' => true,
				'name' => __eva('Days of week'),
				'component' => 'checkboxes-field',
				'hideWhen' => [
					'attribute' => 'type',
					'values' => ['event']
				],
				'options' => [
					['label' => __eva('Mon'), 'value' => 'mon'],
					['label' => __eva('Tue'), 'value' => 'tue'],
					['label' => __eva('Wed'), 'value' => 'wed'],
					['label' => __eva('Thu'), 'value' => 'thu'],
					['label' => __eva('Fri'), 'value' => 'fri'],
					['label' => __eva('Sat'), 'value' => 'sat'],
					['label' => __eva('Sun'), 'value' => 'sun'],
				],
				'mode' => 'inline',
				'value' => evavel_json_encode($this->days_of_week)
			],
			[
				'only_for_class' => Event::class,
				'attribute' => 'include_dates',
				'stacked' => true,
				'style' => '',
				'name' => __eva('Dates for this event'),
				'component' => 'select-dates',
				'selectionMode' => 'info',
				'value' => $this->include_dates,
				'helpText' => 'Dates for this event.',
			],
			[
				'only_for_class' => Shift::class,
				'attribute' => 'exclude_dates',
				'stacked' => true,
				'style' => '',
				'name' => __eva('Exclude Dates'),
				'component' => 'select-dates',
				'selectionMode' => 'warning',
				'value' => $this->exclude_dates,
				'helpText' => 'These dates the shift will not be bookable online.',
			],
			[
				'only_for_class' => Shift::class,
				'attribute' => 'include_dates',
				'stacked' => true,
				'style' => '',
				'name' => __eva('Include Dates'),
				'component' => 'select-dates',
				'selectionMode' => 'info',
				'value' => $this->include_dates,
				'helpText' => 'These dates the shift will be bookable online.',
			],
			[
				'attribute' => 'min_covers_reservation',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Min Covers per reservation'),
				'component' => 'text-field',
				'type' => 'number',
				'value' => $this->min_covers_reservation,
			],
			[
				'attribute' => 'max_covers_reservation',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Max Covers per reservation'),
				'component' => 'text-field',
				'type' => 'number',
				'value' => $this->max_covers_reservation,
			],



			// NOTES
			[
				'attribute' => 'notes',
				'stacked' => true,
				'name' => __eva('Private notes'),
				//'component' => 'textarea-field',
				'component' => 'tiptap-field',
				'useBase64' => true,
				'buttons' => ['bold', 'italic', 'underline','divider',  'paragraph', 'text-wrap', 'h-1', 'h-2', 'h-3', 'align-left', 'align-center', 'align-right'],
				'value' => alexr_transform_textarea_to_new_lines($this->notes),
			],
		];

		$fields = apply_filters('alexr-shift-fields-schedule', $fields, $this);
		return $this->filterByClass($fields);
	}

	public function fieldsWorking() {
		$fields = [
			[
				'attribute' => 'seating',
				'stacked' => true,
				//'style' => 'display: inline-block; width: 69%;',
				'name' => __eva('Start time / End time'),
				'help_online' => ['shifts-start-end-time'],
				'component' => 'first-last-seating-field',
				'value' => [
					'first_seating' => $this->first_seating,
					'last_seating' => $this->last_seating,
				],
				'options' => [
					'start_time' => 3600 * 6,
					'end_time' => 28 * 3600,
					'step' => 900,
					'maxLowerVal' => 22*3600,
					'minUpperVal' => 4*3600
				],
			],

			/*[
				'attribute' => 'first_seating',
				'stacked' => true,
				'style' => 'display: inline-block; width: 35%;',
				'name' => __eva('First Seating'),
				'component' => 'select-field',
				'value' => $this->first_seating,
				'options' => $this->listOfHoursStartEnd(8*3600, 22*3600),
			],
			[
				'attribute' => 'last_seating',
				'stacked' => true,
				'style' => 'display: inline-block; width: 35%;',
				'name' => __eva('Closed time'),
				'component' => 'select-field',
				'value' => $this->last_seating,
				'options' => $this->listOfHoursStartEnd(8*3600, 28*3600)
			],*/

			[
				'attribute' => 'interval',
				'stacked' => true,
				'style' => 'display: inline-block; width: 30%;',
				'name' => __eva('Every:'),
				'component' => 'select-field',
				'value' => $this->interval,
				'options' => [
					['label' => '15min', 'value' => 900],
					['label' => '30min', 'value' => 1800],
					['label' => '1h', 'value' => 3600],
					['label' => '1h 15min', 'value' => 4500],
					['label' => '1h 30min', 'value' => 5400],
					['label' => '1h 45min', 'value' => 6300],
					['label' => '2h', 'value' => 7200],
					['label' => '2h 30m', 'value' => 9000],
					['label' => '3h', 'value' => 10800],
				]
			],
			[
				'attribute' => 'block_slots',
				'stacked' => true,
				'name' => __eva('Block Slots (blocks in red are not available for online booking)'),
				'helpText' => '<div class="text-red-400">' . __eva('** The last time slot displayed to the customer will depend on the duration on the reservation.') . '</div>'
				              .'<div>'.__eva('You can define the duration in the Availability tab.').'</div>',
				'component' => 'list-slots-field',
				'value' => $this->block_slots,
				'attributeFirstSeating' => 'first_seating',
				'attributeLastSeating' => 'last_seating',
				'attributeInterval' => 'interval',
			],
			[
				'only_for_class' => Shift::class,
				'attribute' => 'week_working_hours',
				'stacked' => true,
				'name' => __eva('Customize working hours for individual weekdays'),
				'helpText' => __eva('Only use this to override the default working hours'),
				'component' => 'edit-working-hours',
				'attributeFirstSeating' => 'first_seating',
				'attributeLastSeating' => 'last_seating',
				'attributeInterval' => 'interval',
				'value' => [
					// Monday
					'working_hours_mon' => $this->working_hours_mon,
					'first_seating_mon' => $this->first_seating_mon,
					'last_seating_mon' => $this->last_seating_mon,
					'block_slots_mon' => $this->block_slots_mon,

					// Tuesday
					'working_hours_tue' => $this->working_hours_tue,
					'first_seating_tue' => $this->first_seating_tue,
					'last_seating_tue' => $this->last_seating_tue,
					'block_slots_tue' => $this->block_slots_tue,

					// Wednesday
					'working_hours_wed' => $this->working_hours_wed,
					'first_seating_wed' => $this->first_seating_wed,
					'last_seating_wed' => $this->last_seating_wed,
					'block_slots_wed' => $this->block_slots_wed,

					// Thursday
					'working_hours_thu' => $this->working_hours_thu,
					'first_seating_thu' => $this->first_seating_thu,
					'last_seating_thu' => $this->last_seating_thu,
					'block_slots_thu' => $this->block_slots_thu,

					// Friday
					'working_hours_fri' => $this->working_hours_fri,
					'first_seating_fri' => $this->first_seating_fri,
					'last_seating_fri' => $this->last_seating_fri,
					'block_slots_fri' => $this->block_slots_fri,

					// Saturday
					'working_hours_sat' => $this->working_hours_sat,
					'first_seating_sat' => $this->first_seating_sat,
					'last_seating_sat' => $this->last_seating_sat,
					'block_slots_sat' => $this->block_slots_sat,

					// Sunday
					'working_hours_sun' => $this->working_hours_sun,
					'first_seating_sun' => $this->first_seating_sun,
					'last_seating_sun' => $this->last_seating_sun,
					'block_slots_sun' => $this->block_slots_sun,
				]
			]
		];

		$fields = apply_filters('alexr-shift-fields-working', $fields, $this);
		return $this->filterByClass($fields);
	}

	public function fieldsBooking() {
		$fields = [];

		return $fields;
	}


	public function fieldsAvailability() {

		$availability_new = [
			[
				'attribute' => 'availability',
				'stacked' => true,
				'class' => 'block',
				'name' => __eva('Reservation mode:'),
				'help_online' => 'shifts-reservation-modes',
				'component' => 'group-field',
				'fields' => [[
					'component' => 'shift-availability-view',
					'value' => [
						'availability_type' => $this->availability_type,
						'availability_total' => $this->availability_total,
						'availability_slots' => [
							'availability_slots' => $this->availability_slots,
							'availability_slots_limit_new_covers' => $this->availability_slots_limit_new_covers,
						],
						'all_tables_covers' => [
							'tables_min_online_covers' => $this->compute('tables_min_online_covers'),
							'tables_max_online_covers' => $this->compute('tables_max_online_covers'),
							'tables_min_total_covers' => $this->compute('tables_min_total_covers'),
							'tables_max_total_covers' => $this->compute('tables_max_total_covers'),
						],
						'list_of_tables' => $this->list_of_tables,
						'cannot_duplicate_tables' => $this->cannot_duplicate_tables
					]
				]]
			]
		];

		/*
		$availability_old = [
			[
				'attribute' => 'availability',
				'stacked' => true,
				'class' => 'block',
				'name' => __eva('Reservation mode:'),
				'help_online' => 'shifts-reservation-modes',
				'component' => 'group-field',
				'fields' => [
					[
						'attribute' => 'availability_type',
						'stacked' => true,
						'style' => 'width: 50%; vertical-align: top;',
						'name' => '',
						'component' => 'select-field',
						'value' => $this->availability_type,
						'options' => [
							['label' => __eva('All Tables'), 'value' => 'tables'],
							['label' => __eva('Specific Tables'), 'value' => 'specific_tables'],
							['label' => __eva('Total covers'), 'value' => 'volume_total'],
							['label' => __eva('Covers per slot'), 'value' => 'volume_slots']
						]
					],
					[
						'attribute' => 'availability_total',
						'stacked' => true,
						'style' => 'width: 50%;',
						'name' => __eva('Total covers'),
						'component' => 'text-field',
						'value' => $this->availability_total,
						'showDisplay' => 'block',
						'showWhen' => [
							'attribute' => 'availability_type',
							'values' => ['volume_total']
						],
					],
					[
						'attribute' => 'availability_slots',
						'stacked' => true,
						'name' => __eva('Slots'),
						'component' => 'pacing-slots-field',
						'value' => $this->availability_slots,
						'attributeFirstSeating' => 'first_seating',
						'attributeLastSeating' => 'last_seating',
						'attributeInterval' => 'interval',
						'showDisplay' => 'block',
						'showWhen' => [
							'attribute' => 'availability_type',
							'values' => ['volume_slots']
						],
					],
					// All Tables
					[
						'attribute' => 'all_tables_covers',
						'stacked' => true,
						'component' => 'tables-covers-view',
						'canSave' => false,
						'isReadonly' => true,
						'hideLabel' => true,
						'showDisplay' => 'block',
						'showWhen' => [
							'attribute' => 'availability_type',
							'values' => ['tables']
						],
						'value' => [
							'tables_min_online_covers' => $this->compute('tables_min_online_covers'),
							'tables_max_online_covers' => $this->compute('tables_max_online_covers'),
							'tables_min_total_covers' => $this->compute('tables_min_total_covers'),
							'tables_max_total_covers' => $this->compute('tables_max_total_covers'),
						],
					],
					[
						'attribute' => 'list_of_tables',
						'helpText' => 'Select the tables that will be available for online reservations.',
						'stacked' => true,
						'hideLabel' => true,
						'component' => 'select-tables-for-shift',
						'url' => '/app/floorplan',
						'name' =>'',
						'showWhen' => [
							'attribute' => 'availability_type',
							'values' => ['specific_tables']
						],
						'showDisplay' => 'block',
						'value' => $this->list_of_tables,
					],
					[
						'attribute' => 'cannot_duplicate_tables',
						'stacked' => true,
						'style' => '',
						'name' => __eva('Tables can be reserved only once for this shift.'),
						'component' => 'boolean-field',
						'showWhen' => [
							'attribute' => 'availability_type',
							'values' => ['tables', 'specific_tables']
						],
						'showDisplay' => 'block',
						'type' => 'switch',
						'value' => $this->cannot_duplicate_tables
					],
				]
			]
		];
		*/

		$fields = [
			//...$availability_old,
			...$availability_new,

			// DURATION RESERVATIONS
			[
				'attribute' => 'reservation_duration',
				'stacked' => true,
				'name' => __eva('Duration of the reservation'),
				'component' => 'group-field',
				'help_online' => 'shifts-duration-reservations',
				'fields' => [
					[
						'attribute' => 'duration_mode',
						'stacked' => true,
						'name' =>'',
						'component' => 'select-field',
						'value' => $this->duration_mode,
						'options' => [
							['label' => __eva('All reservations the same duration'), 'value' => 'time'],
							['label' => __eva('Depends on the number of covers'), 'value' => 'covers']
						]
					],
					[
						'attribute' => 'duration_time',
						'stacked' => true,
						'showWhen' => [
							'attribute' => 'duration_mode',
							'values' => ['time']
						],
						'name' => __eva('Select duration'),
						'component' => 'select-field',
						'value' => $this->duration_time,
						'options' => $this->toListDurations([
							'30min', '45min',
							'1h', '1h 15min', '1h 30min', '1h 45min',
							'2h', '2h 15min', '2h 30min', '2h 45min',
							'3h', '3h 15min', '3h 30min', '3h 45min',
							'4h', '4h 15min', '4h 30min', '4h 45min',
							'5h', '5h 15min', '5h 30min', '5h 45min',
							'6h'
						]),
					],
					[
						'attribute' => 'duration_covers',
						'stacked' => true,
						'showWhen' => [
							'attribute' => 'duration_mode',
							'values' => ['covers']
						],
						'name' => __eva('Duration by number of covers'),
						'component' => 'cover-duration-field',
						'value' => $this->duration_covers,
					],
				]
			],

			// OPEN RESERVATIONS
			[
				'attribute' => 'open_reservations',
				'stacked' => true,
				'class' => 'block',
				'name' => __eva('OPEN online reservations'),
				'component__' => 'group-field',
				'component' => 'group-reservations-field',
				'fields' => [
					[
						'attribute' => 'open_reservation_mode',
						'stacked' => true,
						'name' => '',
						'component' => 'select-field',
						'value' => $this->open_reservation_mode,
						'options_components' => [
							['label' => [__eva('Open all time')], 'value' => 'open_all_time'],
							['label' => [__eva('Open') , '[open_hours_before]', __eva('before (for every slot)') ], 'value' => 'open_hours_before'],
							['label' => [__eva('Open the same day at'), '[open_same_day_time]'], 'value' => 'open_same_day_at_time'],
							['label' => [__eva('Open'), '[open_several_days_count]', __eva('before at'), '[open_several_days_time]'], 'value' => 'open_days_before_at_time'],
						],
						/*'options' => [
							['label' => __eva('Open all time'), 'value' => 'open_all_time'],
							['label' => __eva('Open [open_hours_before] before'), 'value' => 'open_hours_before'],
							['label' => __eva('Open the same day at time [open_same_day_time]'), 'value' => 'open_same_day_at_time'],
							['label' => __eva('Open [open_several_days_count] days before at time [open_several_days_time]'), 'value' => 'open_days_before_at_time'],
						],*/
					],
					[
						'attribute' => 'open_hours_before',
						'stacked' => true,
						'name' => __eva('Open Minutes/hours before'),
						'showWhen' => [
							'attribute' => 'open_reservation_mode',
							'values' => ['open_hours_before']
						],
						'component' => 'select-field',
						'hideLabel' => true,
						'value' => $this->open_hours_before,
						'options' => $this->toListDurations([
							'5min', '10min', '15min', '30min', '45min', '1h', '1h 30min', '2h', '3h', '5h', '6h', '7h',
							'8h', '9h', '10h', '11h', '12h', '13h','14h', '15h', '16h', '17h', '18h', '19h', '20h',
							'21h', '22h', '23h', '24h', '2 days', '3 days', '4 days', '5 days', '6 days', '7 days',
							'8 days', '9 days', '10 days', '11 days', '12 days', '13 days', '14 days', '15 days',
							'16 days', '17 days', '18 days', '19 days', '20 days', '21 days', '22 days', '23 days',
							'24 days', '25 days', '26 days', '27 days', '28 days', '29 days', '30 days'
						])
					],
					[
						'attribute' => 'open_same_day_time',
						'stacked' => true,
						//'style' => 'display: inline-block; width: 50%;',
						'name' => __eva('At time'),
						'showWhen' => [
							'attribute' => 'open_reservation_mode',
							'values' => ['open_same_day_at_time']
						],
						'showDisplay' => 'block',
						'component' => 'select-field',
						'hideLabel' => true,
						'value' => $this->open_same_day_time,
						'options' => $this->listOfHours()
					],
					[
						'attribute' => 'open_several_days_count',
						'stacked' => true,
						'style' => 'display: inline-block; width: 50%;',
						'name' => __eva('Days before'),
						'showWhen' => [
							'attribute' => 'open_reservation_mode',
							'values' => ['open_days_before_at_time']
						],
						'showDisplay' => 'inline-block',
						'component' => 'select-field',
						'hideLabel' => true,
						'value' => $this->open_several_days_count,
						'options' => $this->toDaysDurations([
							'1 days', '2 days', '3 days', '4 days', '5 days', '6 days', '7 days',
							'8 days', '9 days', '10 days', '11 days', '12 days', '13 days', '14 days', '15 days',
							'16 days', '17 days', '18 days', '19 days', '20 days', '21 days', '22 days', '23 days',
							'24 days', '25 days', '26 days', '27 days', '28 days', '29 days', '30 days'
						])
					],
					[
						'attribute' => 'open_several_days_time',
						'stacked' => true,
						'style' => 'display: inline-block; width: 49%;',
						'name' => __eva('At time'),
						'showWhen' => [
							'attribute' => 'open_reservation_mode',
							'values' => ['open_days_before_at_time']
						],
						'showDisplay' => 'inline-block',
						'component' => 'select-field',
						'hideLabel' => true,
						'value' => $this->open_several_days_time,
						'options' => $this->listOfHours()
					],

				]
			],

			// CLOSE RESERVATIONS
			[
				'attribute' => 'close_reservations',
				'stacked' => true,
				'class' => 'block',
				'name' => __eva('CLOSE online reservations. Can reserve ..'),
				'component_' => 'group-field',
				'component' => 'group-reservations-field',
				'fields' => [
					[
						'attribute' => 'close_reservation_mode',
						'stacked' => true,
						'name' => '',
						'component' => 'select-field',
						'value' => $this->close_reservation_mode,
						'options_components' => [
							['label' => [__eva('.. until the last minute')], 'value' => 'until_last_minute'],
							['label' => [__eva('.. until'), '[until_hours_period]' , __eva('before (for every slot)')], 'value' => 'until_hours'],
							['label' => [__eva('.. until same day at'), '[until_same_day_time]'], 'value' => 'until_same_day'],
							['label' => [__eva('.. until') , '[until_previous_day_count]', __eva('before at'), '[until_previous_day_time]'], 'value' => 'until_previous_day'],
						],
					],
					[
						'attribute' => 'until_hours_period',
						'stacked' => true,
						//'style' => 'display: inline-block; width: 50%;',
						'name' => __eva('Minutes/hours'),
						'showWhen' => [
							'attribute' => 'close_reservation_mode',
							'values' => ['until_hours']
						],
						'component' => 'select-field',
						'hideLabel' => true,
						'value' => $this->until_hours_period,
						'options' => $this->toListDurations([
							'5min', '10min', '15min', '30min', '45min', '1h', '1h 30min', '2h', '3h', '5h', '6h', '7h',
							'8h', '9h', '10h', '11h', '12h', '13h','14h', '15h', '16h', '17h', '18h', '19h', '20h',
							'21h', '22h', '23h', '24h', '2 days', '3 days', '4 days', '5 days', '6 days', '7 days',
							'8 days', '9 days', '10 days', '11 days', '12 days', '13 days', '14 days', '15 days',
							'16 days', '17 days', '18 days', '19 days', '20 days', '21 days', '22 days', '23 days',
							'24 days', '25 days', '26 days', '27 days', '28 days', '29 days', '30 days'
						])
					],
					[
						'attribute' => 'until_same_day_time',
						'stacked' => true,
						//'style' => 'display: inline-block; width: 50%;',
						'name' => __eva('Time'),
						'showWhen' => [
							'attribute' => 'close_reservation_mode',
							'values' => ['until_same_day']
						],
						'component' => 'select-field',
						'hideLabel' => true,
						'value' => $this->until_same_day_time,
						'options' => $this->listOfHours()
					],
					[
						'attribute' => 'until_previous_day_count',
						'stacked' => true,
						'style' => 'display: inline-block; width: 50%;',
						'name' => __eva('Days before'),
						'showWhen' => [
							'attribute' => 'close_reservation_mode',
							'values' => ['until_previous_day']
						],
						'showDisplay' => 'inline-block',
						'component' => 'select-field',
						'hideLabel' => true,
						'value' => $this->until_previous_day_count,
						'options' => $this->toDaysDurations([
							'1 days', '2 days', '3 days', '4 days', '5 days', '6 days', '7 days',
							'8 days', '9 days', '10 days', '11 days', '12 days', '13 days', '14 days', '15 days',
							'16 days', '17 days', '18 days', '19 days', '20 days', '21 days', '22 days', '23 days',
							'24 days', '25 days', '26 days', '27 days', '28 days', '29 days', '30 days'
						])
					],
					[
						'attribute' => 'until_previous_day_time',
						'stacked' => true,
						'style' => 'display: inline-block; width: 49%;',
						'name' => __eva('Time'),
						'showWhen' => [
							'attribute' => 'close_reservation_mode',
							'values' => ['until_previous_day']
						],
						'showDisplay' => 'inline-block',
						'component' => 'select-field',
						'hideLabel' => true,
						'value' => $this->until_previous_day_time,
						'options' => $this->listOfHours()
					],
				]
			],
		];

		$fields = apply_filters('alexr-shift-fields-availability', $fields, $this);
		return $this->filterByClass($fields);
	}

	public function fieldsStatus() {

		$fields = [
			// BOOKING STATUS
			[
				'attribute' => 'status_rules',
				'stacked' => true,
				//'style' => 'width: 50%; vertical-align: top;',
				'name' => __eva('Max covers confirmed'),
				'component' => 'status-rules-field',
				'helpText' => __eva('RULES'),
				'value' => [
					'booking_status' => $this->booking_status,

					'rule_bookings_enable' => $this->rule_bookings_enable,
					'rule_seats_enable' => $this->rule_seats_enable,
					'rule_slots_enable' => $this->rule_slots_enable,
					'rule_customers_enable' => $this->rule_customers_enable,
					'rule_exclude_days_enable' => $this->rule_exclude_days_enable,
					'rule_exclude_tables_enable' => $this->rule_exclude_tables_enable,

					'status_confirmed_covers' => $this->status_confirmed_covers,
					'status_seats_pending' => $this->status_seats_pending,
					'status_per_slot' => $this->status_per_slot,
					'status_tags_customers' => $this->status_tags_customers,
					'status_exclude_days' => $this->status_exclude_days,
					'status_exclude_tables' => $this->status_exclude_tables,
				],
			],

			/* OLD
			[
				'attribute' => 'booking_status_group',
				'stacked' => true,
				'class' => 'block',
				'name' => __eva('Set new bookings received to status:'),
				'component' => 'group-field',
				'fields' => [
					[
						'attribute' => 'booking_status',
						'stacked' => true,
						'style' => 'width: 50%; vertical-align: top;',
						'name' => __('Status for new bookings'),
						'component' => 'select-field',
						'value' => $this->booking_status,
						'options' => [
							['label' => __eva('Pending'), 'value' => 'pending'],
							['label' => __eva('Confirmed'), 'value' => 'confirmed'],
							['label' => __eva('Confirmed up to X covers, then Pending'), 'value' => 'confirmed_then_pending']
						],
						'helpText' => [
							__eva('PENDING: the booking will receive the status PENDING and you need to confirm it later.'),
							__eva('CONFIRMED: the booking will receive the status CONFIRMED and tables will be assigned automatically if covers available are defined by tables.'),
							__eva('CONFIRMED then PENDING: the first covers will be CONFIRMED until you have reached a max of X, the rest will be set as PENDING.')
						],

						'helpText_' => __eva('PENDING: the booking will receive the status PENDING and you need to confirm it later.').'<br>'
						               .__eva('CONFIRMED: the booking will receive the status CONFIRMED and tables will be assigned automatically if covers available are defined by tables.').'<br>'
						               .__eva('CONFIRMED then PENDING: the first covers will be CONFIRMED until you have reached a max of X, the rest will be set as PENDING.')
					],
					[
						'attribute' => 'status_confirmed_covers',
						'stacked' => true,
						'style' => 'width: 50%; vertical-align: top;',
						'name' => __eva('Max covers confirmed'),
						'component' => 'text-field',
						'helpText' => __eva('Use this if you have selected CONFIRMED then PENDING'),
						'value' => $this->status_confirmed_covers,
						'_showDisplay' => 'block',
						'_showWhen' => [
							'attribute' => 'booking_status',
							'values' => ['confirmed_then_pending']
						],
					],
				]
			],
			*/
		];

		$fields = apply_filters('alexr-shift-fields-status', $fields, $this);
		return $this->filterByClass($fields);
	}

	public function fieldsPayment() {

		$fields = [];

		$fields = apply_filters('alexr-shift-fields-payment', $fields, $this);
		return $this->filterByClass($fields);
	}

	/**
	 * Convert meta_value to a normal array to return
	 *
	 * @return array|void
	 */
	public function toArray() {
		$data = parent::toArray();

		// Additional computed fields
		$data['tables_min_online_covers'] = $this->compute('tables_min_online_covers');
		$data['tables_max_online_covers'] = $this->compute('tables_max_online_covers');
		$data['tables_min_total_covers'] = $this->compute('tables_min_total_covers');
		$data['tables_max_total_covers'] = $this->compute('tables_max_total_covers');

		return $data;
	}

	public function compute($attribute) {

		//$request = Eva::make('request');
		//$restaurant_id = evavel_tenant_id();
		$restaurant_id = $this->restaurant_id;

		$tables = Table::where('restaurant_id', $restaurant_id)->get();

		$results = [
			'tables_min_online_covers' => 0,
			'tables_max_online_covers' => 0,
			'tables_min_total_covers' => 0,
			'tables_max_total_covers' => 0
		];

		foreach($tables as $table){
			$results['tables_min_total_covers'] += intval($table->min_seats);
			$results['tables_max_total_covers'] += intval($table->max_seats);
			$is_online = $table->bookable_online;
			if ($is_online) {
				$results['tables_min_online_covers'] += intval($table->min_seats);
				$results['tables_max_online_covers'] += intval($table->max_seats);
			}
		}

		return $results[$attribute];
	}

}
