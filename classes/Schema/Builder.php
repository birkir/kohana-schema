<?php

class Schema_Builder {

	protected $_column;

	protected $_foreign_key;

	protected function attach()
	{
		$this->table->add_column($this->_column);

		return $this;
	}

	protected function column($type, $name)
	{
		$col = Database_Column::factory($type);
		$col->name = $name;

		return $col;
	}

	public static function factory($table)
	{
		return new Schema_Builder($table);
	}

	public $table;

	public function __construct($table)
	{
		$this->table = $table;

		return $this;
	}

	public function increments($name)
	{
		$this->_column = $this->column('int', $name);
		$this->_column->auto_increment = TRUE;
		$this->_column->unsigned = TRUE;

		$this->primary($name);

		return $this->attach();
	}

	public function integer($name, $size = NULL, $unsigned = TRUE)
	{
		$this->_column = $this->column('int', $name);

		if ($size !== NULL) $this->_column->parameters($size);

		return $this->attach();
	}

	public function string($name, $length = 255)
	{
		$this->_column = $this->column('varchar', $name);

		if ($length !== NULL) $this->_column->parameters($length);

		return $this->attach();
	}

	public function float($name)
	{
		$this->_column = $this->column('float', $name);

		return $this->attach();
	}

	public function decimal($name, $size, $precision = 0)
	{
		$this->_column = $this->column('decimal', $name);
		$this->_column->exact = TRUE;
		$this->_column->parameters(array(DB::expr($size.','.$precision), $precision));

		return $this->attach();
	}

	public function boolean($name)
	{
		$this->_column = $this->column('tinyint', $name);
		$this->_column->parameters(1);

		return $this->attach();
	}

	public function date($name)
	{
		$this->_column = $this->column('date', $name);

		return $this->attach();
	}

	public function datetime($name)
	{
		$this->_column = $this->column('datetime', $name);

		return $this->attach();
	}

	public function time($name)
	{
		$this->_column = $this->column('time', $name);

		return $this->attach();
	}

	public function timestamp($name)
	{
		$this->_column = $this->column('timestamp', $name);

		return $this->attach();
	}

	public function text($name)
	{
		$this->_column = $this->column('text', $name);

		return $this->attach();
	}

	public function binary($name)
	{
		$this->_column = $this->column('blob', $name);

		return $this->attach();
	}

	public function enum($name, array $options)
	{
		$this->_column = $this->column('enum', $name);
		$this->_column->parameters(DB::expr("'".implode("', '", $options)."'"));

		return $this->attach();
	}

	public function unsigned()
	{
		// attach unsigned flag
		$this->_column->unsigned = TRUE;

		return $this->attach();
	}

	public function _default($value)
	{
		$this->_column->default = $value;

		return $this->attach();
	}

	public function auto_increment()
	{
		$this->_column->auto_increment = TRUE;

		return $this->attach();
	}

	public function nullable()
	{
		$this->_column->nullable = TRUE;

		return $this->attach();
	}

	public function primary($name)
	{
		$primary_key = Database_Constraint::primary_key($name, $this->table->name);

		$this->table->add_constraint($primary_key);
	}

	public function unique($name)
	{
		$unique = Database_Constraint::unique($name, $this->table->name);

		$this->table->add_constraint($unique);
	}

	public function index($name)
	{
		$index = Database_Constraint::index($name, $this->table->name);

		$this->table->add_constraint($index);
	}

	public function foreign($name)
	{
		$this->foreign_key = Database_Constraint::foreign($name, $this->table->name);

		$this->table->add_constraint($this->foreign_key);

		return $this;
	}

	public function references($name)
	{
		if ($this->foreign_key instanceof Database_Constraint_Foreign)
		{
			$this->foreign_key->references($name);

			$this->table->add_constraint($this->foreign_key);

			return $this;
		}
	}

	public function on($name)
	{
		if ($this->foreign_key instanceof Database_Constraint_Foreign)
		{
			$this->foreign_key->on($name);

			$this->table->add_constraint($this->foreign_key);

			return $this;
		}
	}

	public function on_update($name)
	{
		if ($this->foreign_key instanceof Database_Constraint_Foreign)
		{
			$this->foreign_key->on_update($name);

			$this->table->add_constraint($this->foreign_key);

			return $this;
		}
	}

	public function on_delete($name)
	{
		if ($this->foreign_key instanceof Database_Constraint_Foreign)
		{
			$this->foreign_key->on_delete($name);

			$this->table->add_constraint($this->foreign_key);

			return $this;
		}
	}

} // End Schema_Builder