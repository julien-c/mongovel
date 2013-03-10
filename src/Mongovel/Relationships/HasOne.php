<?php
namespace Mongovel\Relationships;

class HasOne extends MongoRelation
{
	protected function fetchResults($item)
	{
		return $this->relationModel::findOne($item);
	}
}