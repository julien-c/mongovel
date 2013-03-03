<?php
namespace Mongovel;

use MongoClient;
use Config;

class DB
{
	/**
	 * MongoDB database object
	 *
	 * @var MongoDB
	 */
	public $db;

	public function __construct()
	{
		$dsn = Config::get('database.mongodb.default');

		$server = sprintf('mongodb://%s:%d', $dsn['host'], $dsn['port']);

		$db = new MongoClient($server);

		$this->db = $db->{$dsn['database']};
	}
}
