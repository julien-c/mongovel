<?php
namespace Mongovel\Relationships;

use Mongovel\Cursor;

abstract class MongoRelation
{
	/**
	 * The field the relation's in
	 *
	 * @var string
	 */
	protected $field;

	/**
	 * The Model having the relation
	 *
	 * @var Model
	 */
	protected $model;

	/**
	 * The model the relation refers to
	 *
	 * @var string
	 */
	protected $relationModel;

	/**
	 * The results from the relation
	 *
	 * @var mixed
	 */
	protected $results;

	/**
	 * Build a new Relationship
	 *
	 * @param Model  $model         The model having the relation
	 * @param string $relationModel The name of the model it refers to
	 * @param string $field         The field the relation's in
	 */
	public function __construct($model, $relationModel, $field = null)
	{
		$this->model         = $model;
		$this->relationModel = $relationModel;
		$this->field         = $field;

		// Fetch items
		$items = $this->getItems();
		if (!$items) return false;

		// Transform results
		$results = $this->fetchResults($items);
		$results = $this->intoModels($results);

		$this->results = $results;
	}

	/**
	 * Get the results from the relation
	 *
	 * @return mixed
	 */
	public function getResults()
	{
		return $this->results;
	}

  ////////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS /////////////////////////////
  ////////////////////////////////////////////////////////////////////

	/**
	 * Get the items to work on
	 *
	 * @return array
	 */
	protected function getItems()
	{
		// Get the field to use
		if (!$this->field) {
			$collection = new $this->relationModel;
			$this->field = $collection->getCollectionName();
		}

		return isset($this->model->attributes[$this->field])
			? $this->model->attributes[$this->field]
			: null;
	}

	/**
	 * Transform results into models
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	protected function intoModels($items)
	{
		// Return directly items from Cursor as it'll transform
		// results into models by itself
		if ($items instanceof Cursor) {
			return $items->all();
		}

		$relationModel = $this->relationModel;

		// If it's a single result, don't loop
		if (!is_array($items)) return new $relationModel($items);

		foreach ($items as $key => $item) {
			$items[$key] = new $relationModel($item);
		}

		return $items;
	}

	abstract protected function fetchResults($items);
}