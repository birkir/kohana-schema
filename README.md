# Schema 

This is a clone of Laravel's Schema class built kohana style.

The schema module needs to be enabled before you can use it. To enable, open your `application/bootstrap.php` file and modify the call to [Kohana::modules] by including the schema module like so:

    Kohana::modules(array(
        ...
        'schema' => MODPATH.'schema',
        ...
    ));

The database module is also required for Schema module to work. This module works wonderfully with [task-migrations](github.com/kohana-minion/tasks-migrations) so you can schema in migrations files like so:

	public function up()
	{
		Schema::create('users', function (Blueprint $table) {

			$table->increments('id');
			$table->string('email')
			$table->string('password', 60);
			$table->integer('logins')->unsigned()->defaults(0);
			$table->timestamp('last_login')->defaults('CURRENT_TIMESTAMP');

			$table->unique('email');
		});
	}

You can refer to the laravel documentation for ideas of whats possible.

Work in process!