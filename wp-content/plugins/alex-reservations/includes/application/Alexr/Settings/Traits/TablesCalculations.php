<?php

namespace Alexr\Settings\Traits;

// To calculate tables available
use Alexr\Enums\BookingStatus;
use Alexr\Models\Booking;
use Alexr\Models\Combination;
use Alexr\Models\Table;
use Alexr\Settings\ClosedTable;
use Evavel\Query\Query;

trait TablesCalculations {

	/**
	 * Busca una mesa sencillo o un grupo
	 * Para asignar una mesa sencilla esta tiene que estar online
	 * Para un grupo este tiene que estar online
	 * El grupo puede requerir que las mesas individuales esten tambien online o no
	 *
	 * @param $date_string
	 * @param $time
	 * @param $guests
	 * @param $singleTablesRequireToBeOnline
	 *
	 * @return array|false
	 */
	public function isAvailableBy_SearchTables($date_string, $time, $guests, $singleTablesRequireToBeOnline = false, $areaIdToFilter = null)
	{
		//ray('isAvailableBy_SearchTables ' . $date_string.' ' . ($time/3600) . ' ' . $guests);

		if ($singleTablesRequireToBeOnline) {
			$result = $this->searchTableOrGroup_SingleTablesNeedToBeOnline($date_string, $time, $guests, $areaIdToFilter);
			//ray($result);
			return $result;
		} else {
			$result = $this->searchTableOrGroup_SingleTablesNoNeedToBeOnline($date_string, $time, $guests, $areaIdToFilter);
			//ray($result);
			return $result;
		}
	}

	public function searchTableOrGroup_SingleTablesNeedToBeOnline($date_string, $time, $guests, $areaIdToFilter = null)
	{
		// Busca mesas individuales libres online sin importar el numero de guests
		$check_number_of_guests = false;
		$listTablesFree = $this->getListTablesFree($date_string, $time, $guests, false, $check_number_of_guests);
		if ($areaIdToFilter)
			$listTablesFree = $this->filterListByArea($listTablesFree, $areaIdToFilter);

		// Si no hay mesas libres online no se puede reservar
		if (empty($listTablesFree)) return false;

		// Si hay mesas libres comprueba si alguna cumple con el numero de guests
		$table_found = $this->selectBestSingleTable($listTablesFree, $guests);
		if ($table_found){
			return [$table_found];
		}

		// Busca un grupo que este online y que tenga todas las mesas online
		$listGroupsFree = $this->getListTablesGroupsFree_WithAllTablesOnline($date_string, $time, $guests);
		if ($areaIdToFilter)
			$listGroupsFree = $this->filterListByArea($listGroupsFree, $areaIdToFilter);

		// Si no hay grupos libres no se puede reservar
		if (empty($listGroupsFree)) return false;

		// Busca el mejor grupo posible
		$group_found = $this->selectBestGroupTables($listGroupsFree, $guests);
		if ($group_found) {
			// Devuelve las mesas que componen el grupo
			return $this->getGroupTables($group_found);
		}

		return false;
	}

	public function searchTableOrGroup_SingleTablesNoNeedToBeOnline($date_string, $time, $guests, $areaIdToFilter = null)
	{
		// Busca solo mesas individuales online donde quepan el numero de guests
		$check_number_of_guests = true;
		$listTablesFree = $this->getListTablesFree($date_string, $time, $guests, false, $check_number_of_guests);
		if ($areaIdToFilter)
			$listTablesFree = $this->filterListByArea($listTablesFree, $areaIdToFilter);

		// Busco la mejor mesa
		if (!empty($listTablesFree)) {
			return [ $this->selectBestSingleTable($listTablesFree, $guests) ];
		}

		// Busca un grupo que este online aunque las mesas individuales no esten online
		$listGroupsFree = $this->getListTablesGroupsFree($date_string, $time, $guests);

		if ($areaIdToFilter)
			$listGroupsFree = $this->filterListByArea($listGroupsFree, $areaIdToFilter);

		// Si no hay grupos libres no se puede reservar
		if (empty($listGroupsFree)) return false;

		// Busca el mejor grupo posible
		$group_found = $this->selectBestGroupTables($listGroupsFree, $guests);
		if ($group_found) {
			// Devuelve las mesas que componen el grupo
			return $this->getGroupTables($group_found);
		}

		return false;
	}

	protected function filterListByArea($list, $area_id)
	{
		return array_filter($list, function($item)use($area_id){
			return $item['area_id'] == $area_id;
		});
	}

	protected function filterGroupsWithOnlySpecificTables($groups, $listTablesIds)
	{
		$list = [];
		foreach($groups as $group){
			$group_is_valid = true;
			foreach ( $group['table_ids_all'] as $table_id){
				if (!in_array($table_id, $listTablesIds)){
					$group_is_valid = false;
				}
			}
			if ($group_is_valid) {
				$list[] = $group;
			}
		}
		return $list;
	}


	// HELPERS ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	/** TESTED
	 * Return simple array with online tables data that can be used by this shift
	 * because the availability_type = tables or specific_tables
	 *
	 * $force_get_all_tables = true
	 * means will get all tables anyway
	 * @return array
	 */
	public function getListTablesArray($use_static = true, $force_get_all_tables = false, $only_online_tables = true)
	{
		// Faster if querying several times to calculate slots
		// in the same request
		$use_cache = true;
		static $listTablesCache;

		$key = $this->id.'_tables_'.($force_get_all_tables ? 'yes': 'no');
		if ($use_cache && $use_static && isset($listTablesCache[$key])) {
			return $listTablesCache[$key];
		}


		// Only tables that are online
		$tables = [];
		if ($this->availability_type == self::AVAILABILITY_ALL_TABLES || $force_get_all_tables)
		{
			if ($only_online_tables) {
				$tables = Table::where('restaurant_id', $this->restaurant_id)
				               ->where('bookable_online', 1)
				               ->orderBy('ordering', 'ASC')
				               ->get();
			} else {
				$tables = Table::where('restaurant_id', $this->restaurant_id)
				               ->orderBy('ordering', 'ASC')
				               ->get();
			}
			//ray($tables);

		}
		else if ($this->availability_type == self::AVAILABILITY_SPECIFIC_TABLES)
		{
			$list_of_tables = $this->list_of_tables;
			if ($list_of_tables == '[]' || (is_array($list_of_tables) && empty($list_of_tables))){
				return [];
			}
			else
			{
				$tables = Table::where('restaurant_id', $this->restaurant_id)
				               ->whereIn('id', $this->list_of_tables)
				               ->orderBy('ordering', 'ASC')
				               ->get()
				               ->filter(function($table) use($only_online_tables)
				               {
								   if ($only_online_tables) {
									   return $table->bookable_online == 1;
								   }
					               return true;
				               });

			}
		}
		else {
			return [];
		}

		// Filter by area online
		$tables = $tables->filter(function($table){
			return $table->area->bookable_online == 1;
		});

		$listTables = [];

		foreach($tables as $table)
		{
			$tableArr = $table->toArray();
			unset($tableArr['canvas']);

			$tableArr['mode'] = 'single';
			$tableArr['booking_id'] = null;
			$listTables['table-'.$table->id] = $tableArr;
		}

		$result = $listTables;
		//ray($result);
		return $listTables;
	}

	/**
	 * Solo grupos que esten online
	 * Return an array list with the combinations
	 * Has a field called table_ids_all and table_ids_online
	 * @return array
	 */
	public function getListTablesGroupsArray($use_static = true)
	{
		static $result;

		if ($use_static && $result) {
			return $result;
		}

		// Obtener todas las combinaciones online
		$combinations = Combination::where('restaurant_id', $this->restaurant_id)
			->where('bookable_online', 1)
			->orderBy('ordering', 'ASC')
			->get()
			->map(function($combination)
			{
				// Añade las mesas e indica cuales estan online
				$tables = $combination->listTables;
				$table_ids_all = [];
				$table_ids_online = [];

				foreach($tables as $table){
					$table_ids_all[] = intval($table->id);
					if ($table->bookable_online == 1){
						$table_ids_online[] = intval($table->id);
					}
				}
				$combination->table_ids_all = $table_ids_all;
				$combination->table_ids_online = $table_ids_online;
				return $combination;
			});

		$list = [];
		foreach($combinations as $combination)
		{
			// Descarta la combiacion si el area no esta online
			if ($combination->area->bookable_online == 1) {
				$group = $combination->attributes;
				$group['mode'] = 'group';
				$list[] = $group;
			}
		}

		// Filtrar combinaciones que solo cumplen las mesas seleccionadas
		if ($this->availability_type == self::AVAILABILITY_SPECIFIC_TABLES) {
			$list = $this->filterGroupsWithOnlySpecificTables($list, $this->list_of_tables);
		}

		$result = $list;
		return $list;
	}

	/** TESTED
	 * For every table fill in the booking_id so I know which ones are already occupied
	 * @param $listTables
	 * @param $date_string
	 * @param $time
	 * @param $guests
	 *
	 * @return array
	 */
	public function fillTablesWithBookings($listTables, $date_string, $time, $guests)
	{
		//ray('fillTablesWithBookings');

		$use_cache = true;

		// Cache bookings query for the same request
		static $listBookings;
		$key = $this->id.'_'.$date_string.'_'.$time.'_'.$guests;

		if ($use_cache && isset($listBookings[$key])){
			$bookings = $listBookings[$key];
		} else {
			$bookings = Booking::where('restaurant_id', $this->restaurant_id)
								// no limitar al turno actual, puede haber turnos solapados
								//->where('shift_event_id', $this->id)
			                   ->where('date', $date_string)
			                   ->whereIn('status', BookingStatus::occupied())
			                   ->get();

			if (!is_array($listBookings)){
				$listBookings = [];
				$listBookings[$key] = $bookings;
			}
		}

		//ray($bookings);
		foreach($bookings as $booking){
			$tables = $booking->tables;
			$list = '';
			foreach($tables as $table){
				$list .= $table->name.'('.$table->id.'),';
			}
			//ray('booking ' . $booking->id.' => '.$list);
		}


		// Calculate time range needed for the booking requested
		$required_time_start = $time;
		$required_time_end = $required_time_start + $this->getDuration($guests);

		// Find tables id occupied for each booking that fits with the same time range
		foreach($bookings as $booking)
		{
			$tables_id_used = $booking->tables->map(function($table){
				return intval($table->id);
			})->toArray();

			if (empty($tables_id_used)) continue;

			$booking_start_time = $booking->time;
			$booking_end_time = $booking->time + $booking->duration;
			//ray('BOOKING '.$booking->id.' : '.$this->toHour($booking_start_time).' - '. $this->toHour($booking_end_time). ' T:' . implode(',', $tables_id_used));

			// Is overlapping? then attach to the table
			//$is_overlapping = alexr_is_range_overlapping($required_time_start, $required_time_end, $booking_start_time, $booking_end_time);
			//ray('Overlapping ? '. ($is_overlapping ?'yes':'no') .' (' .$this->toHour($required_time_start).'-'.$this->toHour($required_time_end).') => ('.$this->toHour($booking_start_time).'-'.$this->toHour($booking_end_time).')');

			if ( alexr_is_range_overlapping($required_time_start, $required_time_end, $booking_start_time, $booking_end_time)
			     || ($this->cannotDuplicateTables($booking))
			)
			{
				foreach($tables_id_used as $table_id)
				{
					//ray('ATTACHING TABLE ' . $table_id);
					$listTables['table-'.$table_id]['booking_id'] = intval($booking->id);
				}
			}
		}

		// Add also blocked tables
		$listTables = $this->fillTablesWithBlockedTables($listTables, $date_string, $time);
		//ray($listTables);

		return $listTables;
	}

	// Check if this option is enabled
	// In that case cannot accept more than 1 reservation during the full shift
	public function cannotDuplicateTables($booking) {
		//ray($booking);
		// Solo se comprueba dentro del propio turno
		// Hay que ver si la reserva pertenece a este turno
		if ($booking->shift_event_id != $this->id) return false;
		$active = $this->cannot_duplicate_tables;
		if ($active == 1) return true;
		return false;
	}


	// CLOSED BLOCKED TABLES
	//============================================================================
	public function fillTablesWithBlockedTables($listTables, $date_string, $time)
	{
		// Get closed tables
		$tables_blocked = $this->restaurant->getBlockedTables($date_string, $time);
		if (empty($tables_blocked)) return $listTables;

		//ray($tables_blocked);
		foreach($listTables as $key => $data){
			if ($data['booking_id'] == null && in_array($data['id'], $tables_blocked)) {
				$listTables[$key]['booking_id'] = 99999999;
			}
		}

		//ray($listTables);
		return $listTables;
	}

	//============================================================================



	/** TESTED
	 * Extract the tables that are not occupied by any booking
	 *
	 * @param $force_get_all_tables
	 * true means that does not matter if availability type is not tables/specific_tables
	 * will get all tables anyway
	 * @param $date_string
	 * @param $time
	 * @param $guests
	 *
	 * @return array
	 */
	public function getListTablesFree($date_string, $time, $guests, $force_get_all_tables = false, $check_tables_allowed_guests = false)
	{
		//ray('>>> getListTablesFree service: '. $this->id.' '. $date_string.' '.$time.' '.$guests.' '.$force_get_all_tables);
		// Get all tables available online
		$listTables = $this->getListTablesArray(true, $force_get_all_tables);
		//ray($listTables);

		// Tables with Bookings attached + if table is blocked assign a booking 99999999
		$listTables = $this->fillTablesWithBookings($listTables, $date_string, $time, $guests);

		// Tables free
		$listTablesFree = [];
		foreach ($listTables as $key => $table)
		{
			if ($table['booking_id'] === null)
			{
				if (!$check_tables_allowed_guests) {
					$listTablesFree[$key] = $table;
				}
				else if ($guests >= $table['min_seats'] && $guests <= $table['max_seats']) {
					$listTablesFree[$key] = $table;
				}
			}
		}

		return $listTablesFree;
	}


	/**
	 * Devuelve las combinaciones online que cumplen que las mesas estan libres
	 * aunque la mesa no este disponible online de manera individual
	 * @param $date_string
	 * @param $time
	 * @param $guests
	 *
	 * @return array
	 */
	public function getListTablesGroupsFree($date_string, $time, $guests)
	{
		// Todas las combinaciones que estan online
		// No importa si las mesas individuales estan online o no
		$combinations = $this->getListTablesGroupsArray();

		// Filtra por numero de asientos
		$combinations = array_filter($combinations, function($combination) use ($guests) {
			return $guests >= $combination['min_seats'] && $guests <= $combination['max_seats'];
		});

		// Busco las mesas ocupadas basandome en la duration segun guests
		$duration = $this->getDuration($guests);
		$tables_id_occupied = $this->getListTablesIdOccupied($date_string, $time, $duration);
		//ray($tables_id_occupied);

		// Grupos bloqueados
		$blocked_groups = $this->restaurant->getBlockedCombinations($date_string, $time);

		// Filtra por las que tengan todas las mesas libres
		$list = [];
		foreach($combinations as $combination)
		{
			$can_add_combination = false;

			$count_not_occupied = 0;
			foreach($combination['table_ids_all'] as $table_id)
			{
				if (!in_array($table_id, $tables_id_occupied)){
					$count_not_occupied += 1;
				}
			}

			if ($count_not_occupied == count($combination['table_ids_all'])) {
				$can_add_combination = true;
			}

			// Comprobar si la combinacion no esta bloqueada
			if ($can_add_combination) {
				if (in_array($combination['id'], $blocked_groups)){
					$can_add_combination = false;
				}
			}

			// La asigna
			if ($can_add_combination) {
				$list[] = $combination;
			}

		}

		return $list;
	}

	protected function getGroupTables($group)
	{
		$tables = $this->getListTablesArray(true, true, false);

		$list = [];
		foreach($group['table_ids_all'] as $table_id){
			$key = 'table-'.$table_id;
			if (isset($tables[$key])){
				$list[] = $tables[$key];
			}
		}

		return $list;
	}

	// SOlo grupos donde todas las mesas estan online
	protected function getListTablesGroupsFree_WithAllTablesOnline($date_string, $time, $guests)
	{
		$groups = $this->getListTablesGroupsFree($date_string, $time, $guests);

		// Solo los grupos con todas las mesas online
		$list = [];

		foreach($groups as $group) {
			if (count($group['table_ids_all']) == count($group['table_ids_online']))
			{
				$list[] = $group;
			}
		}

		return $list;
	}


	/**
	 * Free tables for a specific area
	 *
	 * @param $area_id
	 * @param $date_string
	 * @param $time
	 * @param $guests
	 * @param $force_get_all_tables
	 *
	 * @return array
	 */
	public function getListTablesFreeForArea($area_id, $date_string, $time, $guests, $force_get_all_tables = false)
	{
		$listTables = $this->getListTablesFree($date_string, $time, $guests, $force_get_all_tables, true);

		$list = [];
		foreach($listTables as $key => $table){
			if ($table['area_id'] == $area_id){
				$list[$key] = $table;
			}
		}

		return $list;
	}

	public function isTableFreeForGuests($date_string, $time, $guests, $table_id)
	{
		// Tiene en cuenta guests
		$tables = $this->getListTablesFree($date_string, $time, $guests,true, true);
		$freeTables = array_map(function($table){ return $table['id']; }, $tables);
		return in_array($table_id, $freeTables);
	}

	public function isTableFree($date_string, $time, $table_id)
	{
		// NO Tiene en cuenta guests
		$tables = $this->getListTablesFree($date_string, $time, 1,true, false);
		$freeTables = array_map(function($table){ return $table['id']; }, $tables);
		return in_array($table_id, $freeTables);
	}

	/**
	 * Restrinje las mesas segun el numero de guests
	 *
	 * Usando por ajax-floorplan-actions.php
	 * @param $tables
	 * @param $guests
	 *
	 * @return array
	 */
	public function restrictTablesForGuests($tables, $guests)
	{
		$list = [];
		foreach($tables as $table){
			if ($guests >= $table['min_seats'] && $guests <= $table['max_seats'])
			{
				$list[] = $table;
			}
		}
		return $list;
	}

	protected function selectBestSingleTable($list, $guests)
	{
		// Encontrar la mesa que ocupe mas asientos
		$table_found = null;
		foreach ($list as $table){
			if ($guests >= $table['min_seats'] && $guests <= $table['max_seats']) {
				if (!$table_found) {
					$table_found = $table;
				} else {
					if ($table['max_seats'] < $table_found['max_seats']) {
						$table_found = $table;
					}
				}
			}
		}
		return $table_found;
	}

	protected function selectBestGroupTables($list, $guests)
	{
		$group_found = null;
		foreach ($list as $group){
			if ($guests >= $group['min_seats'] && $guests <= $group['max_seats']) {
				if (!$group_found) {
					$group_found = $group;
				} else {
					if ($group['max_seats'] < $group_found['max_seats']) {
						$group_found = $group;
					}
				}
			}
		}
		return $group_found;
	}

	/**
	 * For specific date, slot and duration get the tables that are already occupied
	 *
	 * @param $date
	 * @param $slot
	 * @param $duration
	 *
	 * @return int[]
	 */
	public function getListTablesIdOccupied($date_string, $slot, $duration)
	{
		// Get bookings for this shift and date
		// Check slot and duration overlaps the booking
		// If overlaps and has tables then add the table to the list

		$bookings = Booking::where('restaurant_id', $this->restaurant_id)
							// No limitar el turno, puede haver turnos solapados
							//->where('shift_event_id', $this->id)
		                   ->where('date', $date_string)
		                   ->whereIn('status', BookingStatus::occupied())
		                   ->get();

		$list_occupied = [];

		foreach($bookings as $booking) {

			if (alexr_is_range_overlapping(
				intval($slot), intval($slot) + intval($duration),
				intval($booking->time), intval($booking->time) + intval($booking->duration) )
			) {
					$tables_list = $booking->tablesList;
					if (is_array($tables_list)){
						foreach($tables_list as $table_id) {
							$list_occupied[] = $table_id;
						}
					}
			}
		}

		return array_unique($list_occupied);
	}


}
