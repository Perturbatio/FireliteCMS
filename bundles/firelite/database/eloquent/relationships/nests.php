<?php namespace Firelite\Database\Eloquent\Relationships;

use Laravel\Database\Eloquent\Model;
use Laravel\Database\Eloquent\Relationships\Relationship;
/**
 * The Nests Relationship is intended to model a nested set node relationship
 * 
 */
class Nests extends Relationship {
	
	protected $col_left = 'lft';
	protected $col_right = 'rgt';
	
	/**
	 *
	 * @param Model $model
	 * @param string $associated
	 * @param string $foreign
	 * @param string $col_left
	 * @param string $col_right
	 */
	public function __construct($model, $associated, $foreign, $col_left = null, $col_right = null){
		if ( !is_null( $col_left ) ){ $this->col_left = $col_left; }
		if ( !is_null( $col_right ) ){ $this->col_right = $col_right; }

		parent::__construct( $model, $associated, $foreign );
	}

	/**
	 * Insert a new record for the association.
	 *
	 * If save is successful, the model will be returned, otherwise false.
	 *
	 * @param  Model|array  $attributes
	 * @return Model|false
	 */
	public function insert($attributes)
	{
		if ($attributes instanceof Model)
		{
			$attributes->set_attribute($this->foreign_key(), $this->base->get_key());
			
			return $attributes->save() ? $attributes : false;
		}
		else
		{
			$attributes[$this->foreign_key()] = $this->base->get_key();

			return $this->model->create($attributes);
		}
	}

	/**
	 * Update a record for the association.
	 *
	 * @param  array  $attributes
	 * @return bool
	 */
	public function update(array $attributes)
	{
		if ($this->model->timestamps())
		{
			$attributes['updated_at'] = new \DateTime;
		}

		return $this->table->update($attributes);
	}

	/**
	 * Set the proper constraints on the relationship table.
	 *
	 * @return void
	 */
	protected function constrain()
	{
		return $this->table
			->where($this->col_left, '>', $this->base->{$this->col_left})
			->where($this->col_right, '<', $this->base->{$this->col_right})
			->order_by($this->col_left);
	}
	
	/**
	 * Get the properly hydrated results for the relationship.
	 *
	 * @return array
	 */
	public function results()
	{
		return parent::get();
	}
	
	/**
	 * Set the proper constraints on the relationship table for an eager load.
	 *
	 * @param  array  $results
	 * @return void
	 */
	public function eagerly_constrain($results)
	{
		$this->table->where_in($this->foreign_key(), $this->keys($results))
			->order_by($this->col_left);
	}

}