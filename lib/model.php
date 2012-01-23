<?php

class Model{


	// __construct 
	//
	// Assigns the model the 'table' property, lowercase pluralized name of the controller class
	// and the table's main column the lowercase singular name of the controller class.

	function __construct(){
		$main_column =  strtolower(get_class($this));
		$table = $main_column . 's';
		$this -> table = $table;
		$this -> column = $main_column;
	}
	

	// db_connect() - Establishes database connection

	public function db_connect(){
		mysql_select_db( DB_DATABASE, mysql_connect( DB_SERVER, DB_USERNAME, DB_PASSWORD ));
	}


	// db_disconnect() - Closes database connection

	public function db_disconnect(){
		mysql_close( mysql_connect( DB_SERVER, DB_USERNAME, DB_PASSWORD	) );
	}


	// query() - Basic database query wrapper
	//	
	// Simplifies database queries by provide a single method that recieves the query, 
	// and the number of rows to return ( *** the $row param actually defines what the query_array's first index key is.)
	// If the query is a select statement then it returns the results as an array.
	//
	// $query - Regular database query statement.
	// $row - ***

	public function query( $query, $row = 1 ){
		$this -> db_connect();
		$result = mysql_query($query) or die("Query failed : $query. <br/><br/>Reason: ".mysql_error());
		if ( preg_match('/select/', $query) ){
			while( $query = mysql_fetch_assoc($result) ) {
				$query_array[$row] = $query;
				$row++;
			}
			$this -> db_disconnect();
			if ( isset($query_array)){
				return $query_array;
			}
		}
		$this -> db_disconnect();
	}


	// select - Performs a database select query
	//
	// Recieves the value to select, the column it belongs to, the order to display them and the ***
	// and returns the results as an array. The value defaults to * which then directs to a query that 
	// excludes the column, table is by default the model's table property, and orderby defaults to null.
	//
	// $value - Value to select for
	// $column - The column the value belongs to
	// $orderby - The type of ordering, column and sort. (ex. date, DESC)
	// $row - ***

	function select( $value = '*', $column = 'id', $orderby = array( 'col' => null, 'sort' => null ), $row = 1 ){
		$table = $this -> table;
		if ( isset($orderby) ){
			$orderby = 'order by ' . $orderby['col'] . ' ' . $orderby['sort'];
		}else{
			$orderby = '';	
		}
		if ($value == '*'){
			return $result = $this -> query("select * from $table $orderby;");
		}else{
			return $result = $this -> query("select $column from $table where $column='$value' $orderby);");
		}
	}


	// insert - uses query to insert into table
	//
	// Note: If using two insert a forms POST data, ensure the order of $_POST array keys (order in HTML form)
	//  match the order of the specified columns.
	//
	// $value - the value to be inserted. Can accept multiple values to be inserted into multiple columns by passing it 
	// an $value array and $column array. 
	// $column - the  table column to be inserted into. Defaults to model -> column, the lower-case name of controller.

	function insert($value, $column = null ){
		$table = $this -> table;
		$column = ( isset($column) ) ?  $column : $this -> column;
		if ( is_array($value) ){
			if ( ( count($value) > 1) && (count($column) > 1) ){
				$values = implode("','", $value);
				$columns = implode(",", $column);
				$this -> query ("insert into $table ($columns) values('$values');");
			}else{
				$value = current($value);
				$column = current($column);
				$this -> query ("insert into $table ($column) values('$value');");
			}
		}else{
			$this -> query ("insert into $table ($column) values('$value');");
		}
	}


	// form - manages insertion of POST data into single row
	//
	// Inserts POST data from form into their corresponding columns defined by the $form_fields array. 
	// Could use POST keys but those can be tampered. Otherwise insert directly. 
	//
	// $form_fields - An array with a single field (ex. array('username') ) or multiple fields same order as 
	// 		HTML form (ex. array('username','password','type'). 
	//
	// CRITICAL ASSUMPTIONS - form_fields names = column names, html fields order (POST key order) = $form_fields order.
	// OBSOLETE/PENDING

	function form($form_fields){
		$this -> sanitize($_POST);
		$this -> insert($_POST, $form_fields);
	}


	// update - uses query to update table
	//
	// $ref - Reference value 
	// $new - New value to replace an existing value
	// $column_ref - The columnn of the reference value
	// $column_new - The columnn of for the new value

	function update($ref, $new, $column_ref = null, $column_new = null ){
		$table = $this -> table;
		$column_ref = ( isset($column_ref) ) ?  $column_ref :  'id';
		$column_new = ( isset($column_new) ) ?  $column_new :	$column_old;
		$this -> query("update $table set $column_new = '$new' where $column_ref = '$ref';");
	}


	// remove - uses query to remove from table 
	//
	// $value - the value to be removed.
	// $column - the table column to be removed from. Defaults to model -> column, the lower-case name of controller.

	function remove($value, $column = null ){
		$table = $this -> table;
		$column = ( isset($column) ) ?  $column : 'id';
		$this -> query("delete from $table where $column='$value';");
	}


	// sanitize - Sanitizes any user data.
	//
	// Borrowed heavily from some blog, source later.

	function sanitize($data, $html = false ){
		if (empty($data)){
			return $data;
		} elseif (is_array($data)) {
			foreach($data as $key => $value)
				$this -> sanitize( $data[$key], $html);
		} else {
			$data = stripslashes($data);
			if (is_array($html)) 
				$data = strip_tags($data, implode('', $html));
			elseif (preg_match('|<([a-z]+)>|i', $html)) 
				$data = strip_tags($data, $html);
			elseif ($html !== true) 
				$data = strip_tags($data);
			$data = trim($data);
		}
		return $data;
	}

}

?>
