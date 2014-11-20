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
	
	public function __construct($server = null, $database = null, $options = null)
	{
		$this->connection = 'default';
		
		$this->setDatabaseDSN($server, $database, $options);
	}
	
	public function setConnection($name)
	{
		$this->connection = $name;
		
		$this->setDatabaseDSN();
	}
	
	public function setDatabaseDSN($server = null, $database = null, $options = null)
	{
		if (!is_null($server)) {
			$dsn             = $options ?: array();
			$dsn['server']   = $server;
			$dsn['database'] = $database;
		}
		else {
			// Fetch config data:
			$dsn = Mongovel::getContainer('config')->get('database.mongodb.' . $this->connection);
		}
		
		$this->dsn      = $dsn;
		$this->database = array_pull($dsn, 'database');
		
		if (isset($dsn['username']) && isset($dsn['password'])) {
			$this->server = sprintf(
				'mongodb://%s:%s@%s:%d/%s/',
				array_pull($dsn, 'username'),
				array_pull($dsn, 'password'),
				array_pull($dsn, 'host'),
				array_pull($dsn, 'port'),
				$this->database
			);
		}
		else {
			$this->server = sprintf(
				'mongodb://%s:%d/',
				array_pull($dsn, 'host'),
				array_pull($dsn, 'port')
			);
		}
		
		$this->options = $dsn;
		
		if (!isset($this->options['connect'])) {
			$this->options['connect'] = true;
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
		$m = new MongoClient($this->server, $this->options);
		
		return $m->{$this->database};
	}
}
