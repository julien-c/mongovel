<?php
namespace Mongovel\Relationships;

class BelongsToMany extends HasMany
{
	/**
	 * Fetch the relation's results
	 *
	 * @param array $item
	 *
	 * @return array
	 */
	protected function fetchResults($item)
	{
		$relationModel = $this->relationModel;

		return $relationModel::find(array(
			$this->field => array($this->model->id),
		));
	}
}