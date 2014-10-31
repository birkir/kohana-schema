<?php
/**
 * Create database Schema easily
 *
 * You can create database schema quickly with identical api as the laravel
 * database driver does.
 *
 * @package    Kohana/Schema
 * @category   Base
 * @author     Birkir Gudjonsson
 * @copyright  (c) 2014 Gudjonsen
 * @license    http://kohanaphp.com/license
 */
class Schema {

	// Execute queries
	public static $execute = TRUE;

	// Database instance name
	public static $database_instance;

	// Blueprint class name
	public static $driver = 'Blueprint_MySQL';

	/**
	 * Construct new Schema instance
	 *
	 * @param  string $name
	 * @return void
	 */
	public function __construct($name)
	{
		// Set database instance name
		Schema::$database_instance = $name;

		// Get database config
		$config = Kohana::$config->load('database')->$name;

		if ( ! isset($config['type']))
		{
			throw new Kohana_Exception('Database type not defined in :name configuration',
				array(':name' => $name));
		}

		// Set the driver class name
		Schema::$driver = 'Blueprint_'.ucfirst($config['type']);
	}

	/**
	 * Setup database connection
	 *
	 * @param  string $name
	 * @return Schema
	 */
	public static function db($name = NULL)
	{
		return new Schema($name);
	}

	/**
	 * Compile SQL Schema from blueprint driver
	 *
	 * @param  Blueprint $blueprint
	 * @return array
	 */
	public static function compile(Blueprint $blueprint)
	{
		// Get database instance
		$db = Database::instance(Schema::$database_instance);

		// Compile SQL schema
		$sql = $blueprint->compile();

		if (Schema::$execute === TRUE)
		{
			$db->query(Database::INSERT, $statement);
		}

		return $sql;
	}

	/**
	 * Create a new table on the schema.
	 *
	 * @param  string    $table
	 * @param  Closure   $callback
	 * @return void
	 */
	public static function create($table, $callback = NULL)
	{
		// Get driver name
		$driver = Schema::$driver;

		// Bootstrap blueprint wrapper
		$blueprint = new $driver($table);

		if (is_callable($callback))
		{
			$callback($blueprint);
		}

		return static::compile($blueprint);
	}

	/**
	 * Modify a table on the schema.
	 *
	 * @param  string    $table
	 * @param  Closure   $callback
	 * @return void
	 */
	public static function table($table, $callback)
	{
		// Get driver name
		$driver = Schema::$driver;

		// Bootstrap blueprint wrapper
		$blueprint = new $driver($table, Blueprint::ALTER);

		if (is_callable($callback))
		{
			$callback($blueprint);
		}

		return static::compile($blueprint);
	}

	/**
	 * Rename a table on the schema.
	 *
	 * @param  string  $from
	 * @param  string  $to
	 * @return void
	 */
	public static function rename($from, $to)
	{
		// Get driver name
		$driver = Schema::$driver;

		// Bootstrap blueprint wrapper
		$blueprint = new $driver($from);

		// Rename table
		$blueprint->rename_table($from, $to);

		return static::compile($blueprint);
	}

	/**
	 * Drop a table from the schema.
	 *
	 * @param  string  $table
	 * @return void
	 */
	public static function drop($table)
	{
		// Get driver name
		$driver = Schema::$driver;

		// Bootstrap blueprint wrapper
		$blueprint = new $driver($table);

		// Rename table
		$blueprint->drop_table();

		return static::compile($blueprint);
	}

	/**
	 * Drop a table from the schema if it exists.
	 *
	 * @param  string  $table
	 * @return void
	 */
	public static function drop_if_exists($table)
	{
		// Get driver name
		$driver = Schema::$driver;

		// Bootstrap blueprint wrapper
		$blueprint = new $driver($table);

		// Rename table
		$blueprint->drop_table_if_exists();

		return static::compile($blueprint);
	}

	/**
	 * Determine if the given table exists.
	 *
	 * @param  string  $table
	 * @return bool
	 */
	public static function has_table($table)
	{
		// Get connection
		$db = Database::instance(Schema::$database_instance);

		return (count($db->list_tables($table)) > 0);
	}

	/**
	 * Determine if the given table has a given column.
	 *
	 * @param  string  $table
	 * @param  string  $column
	 * @return bool
	 */
	public static function has_column($table, $column)
	{
		// Get connection
		$db = Database::instance(Schema::$database_instance);

		return (count($db->list_columns($table, $column)) > 0);
	}
}
