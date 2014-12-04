<?php
namespace Mongovel;

use MongoCollection;
use MongoInsertBatch;
use MongoUpdateBatch;
use MongoDeleteBatch;


class Bulk
{
	public function __construct(MongoCollection $collection, array $options = array())
	{
		$this->bulks = array(
			// operation => [hasOne, phpBatch]
			'insert' => [false, new MongoInsertBatch($collection, $options)],
			'update' => [false, new MongoUpdateBatch($collection, $options)],
			'remove' => [false, new MongoDeleteBatch($collection, $options)],
		);
	}
	
	public function find($query)
	{
		return new BulkOperation($this, $query);
	}
	
	public function insert($doc)
	{
		return $this->_add('insert', $doc);
	}
	
	public function _add($operation, $arg)
	{
		$this->bulks[$operation][0] = true;
		$this->bulks[$operation][1]->add($arg);
	}
	
	public function execute()
	{
		foreach ($this->bulks as $b) {
			if ($b[0]) {
				$b[1]->execute();
			}
		}
	}
}

class BulkOperation
{
	public function __construct(Bulk $bulk, array $query)
	{
		$this->bulk = $bulk;
		$this->call = array('q' => $query);
	}
	
	public function remove($limit = 0)
	{
		if (!isset($this->call['limit'])) {
			$this->call['limit'] = $limit;
		}
		return $this->bulk->_add('remove', $this->call);
	}
	
	public function removeOne()
	{
		return $this->remove(1);
	}
	
	public function update($u)
	{
		$this->call['multi'] = true;
		$this->call['u'] = $u;
		return $this->bulk->_add('update', $this->call);
	}
	
	public function updateOne($u)
	{
		$this->call['multi'] = false;
		$this->call['u'] = $u;
		return $this->bulk->_add('update', $this->call);
	}
	
	public function upsert()
	{
		$this->call['upsert'] = true;
		return $this;
	}

}