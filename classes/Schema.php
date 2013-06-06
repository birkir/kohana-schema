<?php

class Schema {

	/**
	 * Create table
	 * 
	 * @param  [type] $name     [description]
	 * @param  [type] $callback [description]
	 * @return [type]           [description]
	 */
	public static function create($name, $callback)
	{
		$table = Database_Table::factory($name);

		// create new schema builder
		$builder = Schema_Builder::factory($table);

		// run callback
		$callback($builder);

		// builder returns table
		echo Debug::vars($builder->table->create());
	}

	/**
	 * Update table
	 * 
	 * @param  [type] $name     [description]
	 * @param  [type] $callback [description]
	 * @return [type]           [description]
	 */
	public static function table($name, $callback) {

		$table = Database_Table::instance($name);

		// create new schema builder
		$builder = Schema_Builder::factory($table);

		// run callback
		$callback($builder);

		// builder returns table
		echo Debug::vars($builder->table->create());
	}

	/**
	 * Drop table
	 * 
	 * @param  [type] $name      [description]
	 * @param  [type] $if_exists [description]
	 * @return [type]            [description]
	 */
	public static function drop($name, $if_exists = FALSE)
	{
		try
		{
			DB::drop('table', $name)->execute();
		}
		catch (Database_Exception $e)
		{
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Rename table
	 * 
	 * @param  string   $from Table to rename
	 * @param  string   $to   New table name
	 * @return Database
	 */
	public static function rename($from, $to)
	{
		$query = DB::query(Database::SELECT, 'RENAME TABLE :from TO :to');

		$query->parameters(array(
			':from' => $from,
			':to' => $to
		));

		return $query->execute();
	}
}