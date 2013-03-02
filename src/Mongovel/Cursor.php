<?php
namespace Mongovel;

use MongoCursor;

class Cursor
{
  /**
   * The MongoCursor instance
   *
   * @var MongoCursor
   */
  protected $cursor;

  /**
   * Create a new Mongovel Cursor instance
   *
   * @param MongoCursor $cursor [description]
   */
  public function __construct(MongoCursor $cursor)
  {
    $this->cursor = $cursor;
  }

  /**
   * Dispatches calls to the MongoCursor instance
   *
   * @param string $method
   * @param array $parameters
   *
   * @return mixed
   */
  public function __call($method, $parameters)
  {
    return call_user_func_array(array($this->cursor, $method), $parameters);
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// HELPERS /////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Convert results to an array
   *
   * @return array
   */
  public function toArray()
  {
    return $this->map(function($value) {
      return $value;
    });
  }

  /**
   * Applies a callback to the results
   *
   * @param Callable $callback
   *
   * @return array
   */
  public function map($callable)
  {
    $results = array();
    foreach($this->cursor as $key => $value) {
      $results[$key] = $callable($value, $key);
    }

    return $results;
  }
}