# Schema

Utility that extends Kohana's Database module with table and column query builder. Then we add schema builder on top of it, so we can easily create and modify tables with ease. What we do next, is create migration class on top of schema, so we can revision our database with ups and downs.

**Warning** This module is in extreme development so don't expect it to work correctly.

### How to

**application/bootstrap.php**

 - Add module **ABOVE** MODPATH.'database'!

<code>'schema' => MODPATH.'schema',</code>


**classes/Migration/User/0.php:**
    <?php
    Migration_User_0 extends Migration {

        public function up()
        {
            Schema::create('users', function ($table) {
                $table->increments('id');
                $table->string('email');
                $table->string('password');
                $table->enum('gender', ['male', 'female']);
                $table->datetime('createdAt');
            });
        }

        public function down()
        {
            Schema::drop('users');
        }

    } // End User Migration



### Schema table API

<table>
<thead>
<tr>
  <th>Command</th>
  <th>Description</th>
</tr>
</thead>
<tbody>
<tr>
  <td><code>$table-&gt;increments('id');</code></td>
  <td>Incrementing ID to the table (primary key).</td>
</tr>
<tr>
  <td><code>$table-&gt;string('email');</code></td>
  <td>VARCHAR equivalent column</td>
</tr>
<tr>
  <td><code>$table-&gt;string('name', 100);</code></td>
  <td>VARCHAR equivalent with a length</td>
</tr>
<tr>
  <td><code>$table-&gt;integer('votes');</code></td>
  <td>INTEGER equivalent to the table</td>
</tr>
<tr>
  <td><code>$table-&gt;bigInteger('votes');</code></td>
  <td>BIGINT equivalent to the table</td>
</tr>
<tr>
  <td><code>$table-&gt;smallInteger('votes');</code></td>
  <td>SMALLINT equivalent to the table</td>
</tr>
<tr>
  <td><code>$table-&gt;float('amount');</code></td>
  <td>FLOAT equivalent to the table</td>
</tr>
<tr>
  <td><code>$table-&gt;decimal('amount', 5, 2);</code></td>
  <td>DECIMAL equivalent with a precision and scale</td>
</tr>
<tr>
  <td><code>$table-&gt;boolean('confirmed');</code></td>
  <td>BOOLEAN equivalent to the table</td>
</tr>
<tr>
  <td><code>$table-&gt;date('created_at');</code></td>
  <td>DATE equivalent to the table</td>
</tr>
<tr>
  <td><code>$table-&gt;datetime('created_at');</code></td>
  <td>DATETIME equivalent to the table</td>
</tr>
<tr>
  <td><code>$table-&gt;time('sunrise');</code></td>
  <td>TIME equivalent to the table</td>
</tr>
<tr>
  <td><code>$table-&gt;timestamp('added_on');</code></td>
  <td>TIMESTAMP equivalent to the table</td>
</tr>
<tr>
  <td><code>$table-&gt;text('description');</code></td>
  <td>TEXT equivalent to the table</td>
</tr>
<tr>
  <td><code>$table-&gt;binary('data');</code></td>
  <td>BLOB equivalent to the table</td>
</tr>
<tr>
  <td><code>$table-&gt;enum('choices', array('foo', 'bar'));</code></td>
  <td>ENUM equivalent to the table</td>
</tr>
<tr>
  <td><code>-&gt;nullable()</code></td>
  <td>Designate that the column allows NULL values</td>
</tr>
<tr>
  <td><code>-&gt;_default($value)</code></td>
  <td>Declare a default value for a column</td>
</tr>
<tr>
  <td><code>-&gt;unsigned()</code></td>
  <td>Set INTEGER to UNSIGNED</td>
</tr>
<tr>
  <td><code>$table-&gt;primary('id');</code></td>
  <td>Adding a primary key</td>
</tr>
<tr>
  <td><code>$table-&gt;primary(array('first', 'last'));</code></td>
  <td>Adding composite keys</td>
</tr>
<tr>
  <td><code>$table-&gt;unique('email');</code></td>
  <td>Adding a unique index</td>
</tr>
<tr>
  <td><code>$table-&gt;index('state');</code></td>
  <td>Adding a basic index</td>
</tr>
</tbody>
</table>


**Adding A Foreign Key To A Table**

    $table->foreign('user_id')->references('id')->on('users');



### Thanks

 - Laravel 4 for the idea.
 - DBForge for column query builder.
