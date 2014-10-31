<?php
/**
 * This class extends the base blueprint driver for generating mysql statements
 */
class Blueprint_MySQL extends Blueprint {

	/**
	 * Rename table
	 *
	 * @param  string Table name from
	 * @param  string Table name to
	 * @return void
	 */
	public function rename_table($from, $to)
	{
		$this->_single_statement = 'rename table '.$this->_db->quote_table($from).' to '.$this->_db->quote_table($to);
	}

	/**
	 * Drop table
	 *
	 * @return void
	 */
	public function drop_table()
	{
		$this->_single_statement = 'drop table '.$this->_db->quote_table($this->_table);
	}

	/**
	 * Drop table if exists
	 *
	 * @return void
	 */
	public function drop_table_if_exists()
	{
		$this->_single_statement = 'drop table if exists '.$this->_db->quote_table($this->_table);
	}

	/**
	 * Drop foreign key
	 *
	 * @return string
	 */
	public function drop_foreign_key($name)
	{
		return 'alter table '.$this->_db->quote_table($this->_table).' drop foreign key '.$this->_db->quote_column($name);
	}	

	/**
	 * Drop index
	 *
	 * @return string
	 */
	public function drop_index()
	{
		return 'drop index '.$this->_db->quote_column($column);
	}

	/**
	 * Drop primary keys
	 *
	 * @return string
	 */
	public function drop_primary()
	{
		return 'drop primary key';
	}

	/**
	 * Add foreign
	 *
	 * @return string
	 */
	public function add_foreign($foreign)
	{
		// Allowed on_update and on_delete actions
		$actions = array('cascade', 'restrict', 'set null', 'set default', 'no action');

		// Set constraint name
		$name = $this->_table.'_'.$foreign['name'];

		// Setup SQL statement
		$sql = 'add constraint '.$this->_db->quote_column($name).' foreign key ('.$this->_db->quote_column($foreign['name']).')';
		$sql .= ' references '.$this->_db->quote_table($foreign['on']).'('.$this->_db->quote_column($foreign['references']).')';

		foreach (array('delete', 'update') as $method)
		{
			$do = Arr::get($foreign, 'on_'.$method);

			if ( ! in_array($do, $actions))
				$do = 'no action';

			$sql .= ' on '.$method.' '.$do;
		}

		return $sql;
	}

	/**
	 * Add index
	 *
	 * @return string
	 */
	public function add_index($name, $columns)
	{
		return 'add index '.$this->_db->quote_column($name).' ('.$this->_db->quote_column(join('`, `', $columns)).')';
	}

	/**
	 * Add unique key
	 *
	 * @return string
	 */
	public function add_unique($name, $columns)
	{
		return 'add constraint '.$this->_db->quote_column($name).' unique ('.$this->_db->quote_column(join('`, `', $columns)).')';
	}

	/**
	 * Parse column types
	 *
	 * @param  $column string 
	 * @return string
	 */
	public function parse_column_type($column)
	{
		// Get column type
		$type = Arr::get($column, 'type');

		switch ($type)
		{
			case 'char':
				return 'char('.Arr::get($column, 'max_length').')';

			case 'string':
				return 'varchar('.Arr::get($column, 'max_length').')';

			case 'text':
				return 'text';

			case 'integer':
				return 'int';

			case 'float':
			case 'double':
				if ($total = Arr::get($column, 'total') AND $places = Arr::get($column, 'places'))
					return 'double('.$total.','.$places.')';
				return 'double';

			case 'decimal':
				return 'decimal('.Arr::get($column, 'total').','.Arr::get($column, 'places').')';

			case 'boolean':
				return 'tinyint(1)';

			case 'enum':
				return 'enum(\''.implode('\', \'', Arr::get($column, 'allowed', array())).'\')';

			case 'timestamp':
				if (Arr::get($column, 'nullable') === TRUE)
					return 'timestamp default 0';
				return 'timestamp';

			case 'binary':
				return 'blob';

			default:
				return $type;
		}
	}

	/**
	 * Parse column modifiers
	 *
	 * @param  array  $column
	 * @return string
	 */
	public function parse_column_modifiers($column)
	{
		// Setup modifiers array
		$modifiers = array();

		if (isset($column['unsigned']) AND $column['unsigned'] === TRUE)
		{
			$modifiers[] = ' unsigned';
		}

		if (isset($column['nullable']))
		{
			$modifiers[] = ($column['nullable'] === TRUE) ? ' null' : 'not null';
		}

		if (isset($column['default']) AND ! empty($column['default']))
		{
			$modifiers[] = ' default '.$this->_db->quote($column['default']);
		}

		if (isset($column['auto_increment']) AND $column['auto_increment'] === TRUE)
		{
			$modifiers[] = ' auto_increment primary key';
		}

		if (isset($column['comment']) AND ! empty($column['comment']))
		{
			$modifiers[] = ' comment '.$this->_db->quote($column['comment']);
		}

		return join(NULL, $modifiers);
	}

}
