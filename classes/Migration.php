<?php

class Migration {

	public static function factory($model, $revision = 0)
	{
		$model = 'Migration_'.$model.'_'.$revision;

		return new $model($model);
	}

	public function __construct($model)
	{
		$this->_model = $model;
	}

} // End Migration
