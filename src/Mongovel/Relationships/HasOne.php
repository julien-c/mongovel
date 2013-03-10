<?php
namespace Mongovel\Relationships;

class HasOne extends MongoRelation
{
	protected function fetchResults($item)
	{
		$relationModel = $this->relationModel;

		return $relationModel::findOne($item);
	}
}