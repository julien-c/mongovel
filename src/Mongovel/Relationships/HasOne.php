<?php
namespace Mongovel\Relationships;

class HasOne extends MongoRelation
{
	/**
	 * Get the field the relation's in
	 *
	 * @return string
	 */
	protected function getForeignKey()
	{
		return strtolower($this->relationModel);
	}

	protected function fetchResults($item)
	{
		$relationModel = $this->relationModel;

		return $relationModel::findOne($item);
	}
}