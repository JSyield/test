<?php

namespace Evavel\Models;

use Evavel\Interfaces\ToJsonSerialize;
use Evavel\Models\Database\Pivot\Pivot;
use Evavel\Models\Traits\HasAttributes;
use Evavel\Models\Traits\HasEvents;
use Evavel\Models\Traits\HasMeta;
use Evavel\Models\Traits\HasRelationships;
use Evavel\Models\Traits\HasTimestamps;
use Evavel\Models\Traits\HidesAttributes;
use Evavel\Query\Query;

abstract class Model implements ToJsonSerialize {
	use HasAttributes;
	use HidesAttributes;
	use HasRelationships;
	use HasTimestamps;
	use HasEvents;
	use HasMeta;

	public static $table_name;
	public static $table_meta = false;
	public static $pivot_tenant_field;

	/**
	 * Name of the created column
	 */
	const CREATED_AT = 'date_created';

	/**
	 * Name of the updated column
	 */
	const UPDATED_AT = 'date_modified';

	/**
	 * @var bool
	 */
	public $exists = false;

	// @todo: borrar completamente
	public $id = null;

	/**
	 * Booted models
	 *
	 * @var array
	 */
	protected static $booted = [];

	public $pivot_model = null;

	public function __construct( array $attributes = [] ) {
		// @todo: SHOULD NOT HAPPEN, debug track to see where is this created
		// Used by HasEvents (27) for registering observers
		// HasRelationships (21)
		/*if (is_null($attributes)){
			ray('Passed NULL '.$attributes);
			ray(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
		}*/

		// @todo: no deberia hacer falta
		//if (count($attributes) == 0) {
		//	return;
		//}

		// @todo: SHOULD NOT HAPPEN, debug track to see where is this created
		/*if (!is_array($attributes)) {
			//ray('Passed ID '.$attributes);
			//ray(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
			$attributes = static::queryAttributes($attributes);
		}*/

		$this->bootIfNotBooted();

		$this->initializeTraits();

		$this->fill( $attributes );

		$this->syncOriginal();
	}

	/**
	 * Static constructor
	 *
	 * @param $data
	 *
	 * @return static
	 */
	public static function make( $data ) {
		if ( ! is_array( $data ) ) {
			$data = static::queryAttributes( $data );
		}

		return new static( $data );
	}

	/**
	 * Create from ID
	 *
	 * @param $id
	 *
	 * @return static
	 */
	public static function withId( $id ) {
		return static::make( $id );
	}

	/**
	 * Get the attributes array from the database
	 *
	 * @param $id
	 *
	 * @return \Evavel\Query\stdClass|mixed|null
	 */
	public static function queryAttributes( $id ) {
		return Query::table( static::$table_name )->toArray()->find( intval( $id ) );
	}

	/**
	 * Check if model need to be booted
	 *
	 * @return void
	 */
	protected function bootIfNotBooted() {
		if ( ! isset( static::$booted[ static::class ] ) ) {
			static::$booted[ static::class ] = true;

			$this->fireModelEvent( 'booting' );

			static::booting();
			static::boot();
			static::booted();

			$this->fireModelEvent( 'booted' );
		}
	}

	protected static function booting() {
		//
	}

	protected static function boot() {
		// @todo: pending boot traits
	}

	protected static function booted() {
		//
	}

	/**
	 * Initialize any traits needed
	 *
	 * @return void
	 */
	protected function initializeTraits() {
		// @todo: pending
	}

	/**
	 * Fill the array of attributes
	 *
	 * @param array $attributes
	 *
	 * @return $this
	 */
	public function fill( array $attributes ) {

		//if ( ! empty( $attributes ) ) {
		//	$this->exists = true;
		//}

		foreach ( $attributes as $key => $value ) {
			$this->setAttribute( $key, $value );
		}

		$this->fillId();

		// Only if id exists is because the record exists
		if ($this->id) {
			$this->exists = true;
		}

		return $this;
	}

	/**
	 * Assign the id field
	 *
	 * @return $this
	 */
	public function fillId()
	{
		if (array_key_exists('id', $this->getAttributes())){
			$this->id = $this->getAttributes()['id'];
		}

		return $this;
	}

	/**
	 * Get the id field name
	 *
	 * @return string
	 */
	public function getKeyName() {
		// @todo: make it variable
		return 'id';
	}

	/**
	 * Resource name used for the dashboard
	 *
	 * @return false|string
	 */
	public static function getResourceName() {
		return evavel_singular( static::$table_name );
	}

	/**
	 * Access attribute
	 *
	 * @param $key
	 *
	 * @return \Carbon\Carbon|int|mixed|void|null
	 */
	public function __get( $key ) {

		// Es un pivot model?
		if ($key == 'pivot') {
			return $this->getPivot();
		}

		return $this->getAttribute( $key );
	}

	// Generar un model Pivot sobre la marcha
	// para acceder a los datos de las tablas relacionadas belongsToMany
	public function getPivot() {

		if ($this->pivot_model != null) {
			return $this->pivot_model;
		}

		$this->pivot_model = new Pivot();

		$arr = [];
		// Solo atributos que empiezan por pivot_...
		foreach($this->attributes as $key => $value) {
			if (substr($key, 0, 5) == 'pivot') {
				$key_clean = str_replace('pivot_', '', $key);
				$arr[$key_clean] = $value;
			}
		}

		$this->pivot_model->attributes = $arr;
		$this->pivot_model->original = $arr;

		return $this->pivot_model;
	}


	/**
	 * Set an attribute
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return void
	 */
	public function __set( $key, $value ) {
		$this->setAttribute( $key, $value );
	}

	public function getForeignKey() {
		return static::getResourceName() . '_' . $this->getKeyName();
	}

	/**
	 * Call a Query method
	 *
	 * @param $method
	 * @param $params
	 *
	 * @return Collections\Collection
	 */
	public static function __callStatic( $method, $params ) {
		$result = ( self::DB() )->$method( ...$params );

		if ( ! is_array( $result ) ) {
			return $result;
		}

		return evavel_collect( $result );
	}

	// SRR_Booking::DB()->get()
	// SRR_Customer::DB()->get()
	public static function DB() {
		$query = Query::table( static::$table_name, static::class );

		return static::scopedGlobal( $query );
	}

	public static function Query() {
		return self::DB();
	}

	public static function scopedGlobal( Query $query ) {
		return $query;
	}


	// @todo: REVISAR EL FUNCIONAMIENTO

	/**
	 * Save
	 *
	 * @param array $options
	 *
	 * @return $this
	 */
	public function save( array $options = [] )
	{
		$this->fireModelEvent( 'saving' );

		if ( $this->exists ) {

			$dirty = $this->getDirty();

			if ( count( $dirty ) > 0 )
			{
				$this->performUpdate();
				$saved = true;
			}
			else
			{
				if ( $this->metaIsDirty ) {
					$this->saveMeta();
				}
				$saved = false;
			}

		} else {
			$saved = $this->performInsert();
		}

		if ( $saved ) {
			$this->finishSave( $options );
		}

		return $this;
	}

	/**
	 * Update current record
	 *
	 * @return bool
	 */
	protected function performUpdate() {
		$this->fireModelEvent( 'updating' );

		if ( $this->usesTimestamps() ) {
			$this->updateTimestamps();
		}

		$dirty = $this->getDirty();

		if ( count( $dirty ) > 0 ) {

			Query::table( static::$table_name )
			     ->where( 'id', $this->id )
				 //->debug()
			     ->update( $this->dataToJson($dirty) );

			if ( $this->metaIsDirty ) {
				$this->saveMeta();
			}

			$this->syncChanges();

			$this->fireModelEvent( 'updated' );
		}

		return true;
	}

	/**
	 * Create a new record
	 *
	 * @return bool
	 */
	protected function performInsert() {
		$this->fireModelEvent( 'creating' );

		if ( $this->usesTimestamps() ) {
			$this->updateTimestamps();
		}

		$attributes = $this->getAttributesForInsert();

		if ( empty( $attributes ) ) {
			return true;
		}

		$result = Query::table( static::$table_name, static::class )
			//->debug()
			->insert( $this->dataToJson($this->attributes) );

		if ( ! $result ) {
			return false;
		}

		$key = $this->getKeyName();
		$this->setAttribute( $key, $result->{$key} );
		// Store id
		$this->{$key} = $result->{$key};
		$this->syncOriginal();

		if ( $this->metaIsDirty ) {
			$this->saveMeta();
		}

		$this->exists = true;

		$this->fireModelEvent( 'created', false );

		return true;
	}

	protected function dataToJson(array $data)
	{
		foreach($data as $key => $value){
			if (is_array($value)){
				$data[$key] = evavel_json_encode($value);
			}
		}
		return $data;
	}


	/**
	 * Finish saving
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	protected function finishSave( array $options ) {
		$this->fireModelEvent( 'saved' );

		$this->syncOriginal();
	}


	public static function create( $attributes ) {
		$model = new static;

		foreach ( $attributes as $key => $value ) {
			$model->setAttribute( $key, $value );
		}

		$model->save();

		return $model;
	}

	public static function updateOrCreate(array $attributes, array $values = [])
	{
		//Query::setDebug(true);
		// Este metodo no lo tengo implementado
		//$instance = static::where($attributes)->first();
		//ray($instance);

		// Hago el metodo directo
		$query = null;
		foreach ($attributes as $key => $value) {
			if ($query == null) {
				$query = static::where($key, $value);
			} else {
				$query->where($key, $value);
			}
		}
		$instance = $query->first();
		//ray($instance);


		if ($instance === null) {
			return static::create(array_merge($attributes, $values));
		}

		// Para que haga el cast
		foreach ($values as $key => $value) {
			$instance->{$key} = $value;
		}
		//$instance->fill($values);
		$instance->save();

		return $instance;
	}


	public function toArray()
	{
		return array_merge($this->attributesToArray(), $this->relationsToArray());
	}

	public function toJson($options = 0)
	{
		return json_encode($this->toJsonSerialize(), $options);
	}

	public function toJsonSerialize() {

		return $this->toArray();
	}
}
