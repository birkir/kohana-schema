<?php
/**
 * Easily add columns and constraints to table with this blueprint class
 * 
 * @package    Kohana/Schema
 * @category   Base
 * @author     Birkir Gudjonsson
 * @copyright  (c) 2014 Gudjonsen
 * @license    http://kohanaphp.com/license
 */
class Blueprint {

	// Which is it
	const CREATE = 1;
	const ALTER = 2;

	/**
	 * @var string Table engine
	 */
	public $engine;

	// Table to create or alter
	protected $_table;

	// Raw database connection
	protected $_db;

	// Column added or modified
	protected $_columns = array();

	// Foreign relationships
	protected $_foreign = array();

	// Commands buffer
	protected $_commands = array();

	// Single statement return
	protected $_single_statement;

	/**
	 * Create a new schema blueprint.
	 *
	 * @param  string   $table
	 * @param  function $callback
	 * @return void
	 */
	public function __construct($table, $type = Blueprint::CREATE)
	{
		// Set table
		$this->_table = $table;

		// Set connection
		$this->_db = Database::instance(Schema::$database_instance);

		// Set type
		$this->_type = $type;
	}

	/**
	 * Compile everything to array of statements to execute
	 *
	 * @return array
	 */
	public function compile()
	{
		if (isset($this->_single_statement))
		{
			// Only output single statements
			return $this->_single_statement;
		}

		foreach ($this->_columns as $column)
		{
			foreach (array('primary', 'unique', 'index') as $index)
			{
				if (Arr::get($column, $index) === TRUE)
				{
					// Add index to column
					$this->$index($column['name']);

					continue 2;
				}
				elseif (isset($column[$index]))
				{
					// Add named index to column
					$this->$index($column['name'], $column[$index]);

					continue 2;
				}
			}
		}

		if ($this->_type === Blueprint::ALTER)
		{
			return $this->compile_alter();
		}

		return $this->compile_create();
	}

	/**
	 * Compile alter table SQL string
	 *
	 * @return string
	 */
	public function compile_alter()
	{
		// Get previously loaded columns
		$previous = array_keys($this->_db->list_columns($this->_table));

		foreach ($this->_columns as $i => $column)
		{
			// Check if column was previously loaded
			$this->_columns[$i][in_array($column['name'], $previous) ? 'modify' : 'add'] = TRUE;
		}

		// Get columns
		$columns = $this->get_columns();

		// Get constraints
		$constraints = $this->compile_constraints();

		// No SQL when no columns are modified
		if ((count($columns) + count($constraints)) === 0) return '';

		// Setup SQL statement
		$sql = 'alter table '.$this->_db->quote_table($this->_table);

		if (count($columns) > 0)
		{
			$sql .= ' ('.join(', ', $columns).')';
		}

		if (count($constraints) > 0)
		{
			$sql .= ' '.join(', ', $constraints);
		}

		return $sql;
	}

	/**
	 * Compile create table SQL string
	 *
	 * @return string
	 */
	public function compile_create()
	{
		if (count($this->_columns) === 0)
		{
			// Add increments to empty tables
			$this->increments('id');
		}

		// Get columns
		$columns = implode(', ', $this->get_columns());

		// Get constraints
		$constraints = $this->compile_constraints();

		//
		$sql = 'create table '.$this->_db->quote_table($this->_table).' ('.$columns.')';

		if (count($constraints) > 0)
		{
			$sql .= ' '.join(', ', $constraints);
		}

		return $sql;
	}

	/**
	 * Compile constraint SQL string
	 *
	 * @return string
	 */
	public function compile_constraints()
	{
		// Setup constraints array
		$constraints = array();

		foreach ($this->_foreign as $foreign)
		{
			// Append foreign to constraint
			$constraints[] = $this->add_foreign($foreign);
		}

		foreach ($this->_commands as $command)
		{
			// Get command name
			$name = Arr::get($command, 'name');

			// Get column
			$columns = Arr::get($command, 'columns', array());

			switch ($name)
			{
				case 'drop_foreign':
					foreach ($columns as $column)
					{
						$constraints[] = $this->drop_foreign_key($column);
					}
					break;

				case 'drop_index':
				case 'drop_unique':
					foreach ($columns as $column)
					{
						$constraints[] = $this->drop_index($column);
					}
					break;

				case 'drop_primary':
					$constraints[] = $this->drop_primary();
					break;

				case 'index':
					$constraints[] = $this->add_index(Arr::get($command, 'index'), $columns);
					break;

				case 'unique':
					$constraints[] = $this->add_unique(Arr::get($command, 'index'), $columns);
					break;
			}
		}

		return $constraints;
	}

	public function get_columns()
	{
		$columns = array();
		$alter = ($this->_type === Blueprint::ALTER);

		foreach ($this->_columns as $column)
		{
			$col = '';

			if ($alter)
			{
				if (Arr::get($column, 'add') === TRUE)
				{
					$col .= 'add ';
				}
				else if (Arr::get($column, 'modify') === TRUE)
				{
					$col .= 'change '.$this->_db->quote_column($column['name']);
				}
			}

			$col .= $this->_db->quote_column($column['name']);
			$col .= ' '.$this->parse_column_type($column).$this->parse_column_modifiers($column);

			if ($alter AND Arr::get($column, 'after'))
			{
				$col .= ' after '.$this->_db->quote_column($column['after']);
			}

			$columns[] = $col;
		}

		foreach ($this->_commands as $command)
		{
			if ($command['name'] === 'drop_column')
			{
				foreach ($command['columns'] as $column)
				{
					$columns[] = 'drop '.$this->_db->quote_column($column);
				}
			}
		}

		return $columns;
	}

	public function boolean($column)
	{
		return $this->add_column('boolean', $column);
	}

	public function string($column, $length = 255)
	{
		return $this->add_column('string', $column, array('max_length' => $length));
	}

	public function integer($column, $auto_increment = FALSE, $unsigned = FALSE)
	{
		return $this->add_column('integer', $column, compact('auto_increment', 'unsigned'));
	}

	public function text($column)
	{
		return $this->add_column('text', $column);
	}

	public function char($column, $length = 255)
	{
		return $this->add_column('char', $column, array('max_length' => $length));
	}

	public function float($column, $total = 8, $places = 2)
	{
		return $this->add_column('float', $column, compact('total', 'places'));
	}

	public function double($column, $total = NULL, $places = NULL)
	{
		return $this->add_column('double', $column, compact('total', 'places'));
	}

	public function decimal($column, $total = 8, $places = 2)
	{
		return $this->add_column('decimal', $column, compact('total', 'places'));
	}

	public function enum($column, array $allowed)
	{
		return $this->add_column('enum', $column, compact('allowed'));
	}

	public function date($column)
	{
		return $this->add_column('date', $column);
	}

	public function datetime($column)
	{
		return $this->add_column('datetime', $column);
	}

	public function time($column)
	{
		return $this->add_column('time', $column);
	}

	public function timestamp($column)
	{
		return $this->add_column('timestamp', $column);
	}

	/**
	 * Indexes
	 */
	public function index($columns, $name = NULL)
	{
		return $this->index_command('index', $columns, $name);
	}

	public function primary($columns, $name = NULL)
	{
		return $this->index_command('primary', $columns, $name);
	}

	public function unique($columns, $name = NULL)
	{
		return $this->index_command('unique', $columns, $name);
	}

	public function after($reference)
	{
		if ($this->_type === Blueprint::ALTER)
		{
			$this->_columns[count($this->_columns) - 1]['after'] = $reference;

			return;
		}

		$last = $this->_columns[count($this->_columns) - 1];
		$pos = -1;

		foreach ($this->_columns as $i => $column)
		{
			if ($column['name'] === $reference)
			{
				$pos = $i;
			}
		}

		if ($pos > -1)
		{
			$cols = $this->_columns;
			$before = array_slice($cols, 0, $pos + 1, TRUE);
			$after = array_slice($cols, $pos + 1, count($cols) - $pos - 2);
			$this->_columns = Arr::merge($before, Arr::merge(array($last), $after));
		}
	}

	public function defaults($str)
	{
		$this->_columns[count($this->_columns) - 1]['default'] = $str;
	}

	public function nullable()
	{
		$this->_columns[count($this->_columns) - 1]['nullable'] = TRUE;
	}

	public function unsigned()
	{
		$this->_columns[count($this->_columns) - 1]['unsigned'] = TRUE;
	}

	/**
	 * Altering
	 */
	public function drop($type, $columns = NULL)
	{
		$allowed = array('column', 'index', 'primary', 'unique', 'foreign');
		$columns = (array) $columns;

		if ( ! in_array($type, $allowed))
		{
			throw new Kohana_Exception('Type :type not allowed to be dropped.', array(
				':type' => $type));
		}

		$this->add_command('drop_'.$type, compact('columns'));

		return $this;
	}

	public function rename($from, $to)
	{
		$this->add_command('rename', compact('from', 'to'));
		return $this;
	}

	/**
	 * Foreign relationships
	 */
	public function foreign($name)
	{
		$this->_foreign[$name] = array('name' => $name);

		return $this;
	}

	public function references($column)
	{
		$this->_foreign[Arr::get(end($this->_foreign), 'name')]['references'] = $column;

		return $this;
	}

	public function on($table)
	{
		$this->_foreign[Arr::get(end($this->_foreign), 'name')]['on'] = $table;

		return $this;
	}

	public function on_delete($method)
	{
		$this->_foreign[Arr::get(end($this->_foreign), 'name')]['on_delete'] = $method;

		return $this;
	}

	public function on_update($method)
	{
		$this->_foreign[Arr::get(end($this->_foreign), 'name')]['on_update'] = $method;

		return $this;
	}

	/**
	 * Quick and handy helpers
	 */
	public function increments($column)
	{
		return $this->integer($column, TRUE, TRUE);
	}

	public function timestamps()
	{
		$this->timestamp('created_at');
		$this->timestamp('updated_at');
	}

	protected function add_command($name, $parameters)
	{
		$attributes = array_merge(compact('name'), $parameters);

		$this->_commands[] = $attributes;

		return $this;
	}

	public function index_command($type, $columns, $index)
	{
		$columns = (array) $columns;

		if (is_null($index))
		{
			$index = 'key_'.str_replace(array('-', '.'), '_', strtolower($this->_table.'_'.implode('_', $columns).'_'.$type));
		}

		return $this->add_command($type, compact('index', 'columns'));
	}

	protected function add_column($type, $name, array $parameters = array())
	{
		$attributes = array_merge(compact('type', 'name'), $parameters);

		$this->_columns[] = $attributes;

		return $this;
	}

}
