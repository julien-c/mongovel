<?php
namespace Mongovel;

use MongoClient;

class DB
{
	/**
	 * Connection name (e.g., 'default')
	 * 
	 * @var string
	 */
	public $connection;
	
	/**
	 * DSN-like server string
	 *
	 * @var string
	 */
	public $server;
	
	/**
	 * DSN-like database name
	 * 
	 * @var string
	 */
	public $database;
	
	public function __construct($server = null, $database = null)
	{
		$this->connection = 'default';
		
		$this->setDatabaseDSN($server, $database);
	}
	
	public function setConnection($name)
	{
		$this->connection = $name;
		
		$this->setDatabaseDSN();
	}
	
	public function setDatabaseDSN($server = null, $database = null)
	{
		if (!is_null($server)) {
			$this->server   = $server;
			$this->database = $database;
		}
		else {
			// Fetch config data:
			
			$dsn = Mongovel::getContainer('config')->get('database.mongodb.' . $this->connection);
			
			$this->dsn      = $dsn;
			$this->database = $dsn['database'];
			
			if (isset($dsn['username']) && isset($dsn['password'])) {
				$this->server = sprintf('mongodb://%s:%s@%s:%d/%s', $dsn['username'], $dsn['password'], $dsn['host'], $dsn['port'], $dsn['database']);
			}
			else {
				$this->server = sprintf('mongodb://%s:%d', $dsn['host'], $dsn['port']);
			}
		}
	}
	
	public function getDatabaseDSN()
	{
		return $this->dsn;
	}
	
	/**
	 * Return a properly-configured MongoDB object
	 * 
	 * @return MongoDB
	 */
	public function db()
	{
		$m = new MongoClient($this->server);
		
		return $m->{$this->database};
	}
}
