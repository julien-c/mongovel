<?php
namespace Mongovel\Relationships;

use MongoDBRef;
use MongoId;
use Mongovel\Mongovel;

class HasMany extends MongoRelation
{
	/**
	 * Transform references into models
	 *
	 * @return array
	 */
	protected function fetchResults($items)
	{
		// Fetch references
		if (isset($items[0]['$ref'])) {
			foreach ($items as $key => $item) {
				$items[$key] = MongoDBRef::get(Mongovel::db(), $item);
			}
		}

		// Fetch by ID
		if (!is_array($items[0])) {

			// Convert to MongoId if necessary
			if (is_string($items[0])) {
				foreach ($items as $key => $item) {
					$items[$key] = new MongoId($item);
				}
			}

			$relationModel = $this->relationModel;
			$items = $relationModel::find(array(
				'_id' => array('$in' => $items)
			));
		}

		return $items;
	}
}