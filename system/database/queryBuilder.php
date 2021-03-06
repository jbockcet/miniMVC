<?php
/**
 * QueryBuilder class file.
 *
 * @author Z. Alem <info@alemmedia.com>
 */

/**
 * The QueryBuilder class allows a simplified abstraction layer for
 * constructing and running queries. This allows for simpler sequential construction of queries.
 *
 * Example
 * -------
 * QueryBuilder -> select ('*') -> from('table') -> where('value','column') -> run();
 *
 *
 * @todo Make each partial query database agnostic. Many queries will not work with all database types.
 */
class QueryBuilder extends DbQuery
{

	/**
	 * @var array Count-keeper for each query builder.
	 */
	public $counter = array( 
		'insert' => 0,
		'update' => 0,
		'remove' => 0,
		'select' => 0,
		'from' 	 => 0,
		'joining' => 0,
		'where'  => 0,
		'order'  => 0,
		'limit'  => 0,
		'show' 	 => 0
	);


	/**
	 * page() - Executes query and returns paged result
	 *
	 * Alternative to run(), returns an pagination-friendly array with the format:
	 * 	pages ( # pages), 
	 * 	paged ( single page result), 
	 *	total (total # rows in column).
	 *
	 * @param int   $page           		The page to start on or SQL offset
	 * @param int   $items_per_page 		Number of rows to return which determines the number of results per page
	 * @return array  				The pagination-friendly array.
	 * @uses 	QueryBuilder::saveQuery() 	Saves query before running first query
	 * @uses 	QueryBuilder::restoreQuery() 	Restores query to for use in following paged query
	 * @uses 	QueryBuilder::limit()		Used to limit the paged query.
	 */
	public function page($page, $items_per_page)
	{

		$this -> saveQuery();
		$total_result = count( $this -> run() );
		$this -> restoreQuery();

		$starting_row = ( $page != 1 ) ? ( ($page - 1) * $items_per_page ) : 0;
		$this -> limit( $starting_row, $items_per_page );
		$paged_query_result = $this -> run();

		return array(
			'total' => $total_result,
			'pages' => ceil( $total_result/$items_per_page ), 
			'paged' => $paged_query_result 
		);
	}


	/**
	 * whitelist () - Checks if value is a valid column.
	 *
	 * Retrieves list of columns from SQL table.
	 * Parses through table columns to check if supplied column name is present in the table.
	 * 
	 * REMOVED [ Or uses Model::custom_whitelist as an optional alternate whitelist array. ]
	 *
	 * @param mixed  $column 			The column values to check.
	 * @param string $table 			The table whose columns will be checked against
	 * @uses  	 QueryBuilder::saveQuery() 	Saves orignal query before running getColumns
	 * @uses 	 QueryBuilder::getColumns() 	Retrieves tables columns to whitelist $column
	 * @uses 	 Controller::prg() 		Redirects to default controller if invalid column is given
	 */
	public function whitelist( $column, $table = null )
	{

		if( $prefix = $this -> getPrefix( $column ) )
		{
			$table = $prefix;
			$this -> unprefix( $column );
		}
		elseif ( empty ( $table ) )
			$table =& $this -> table;

		$this -> saveQuery() -> clearQuery();
		$list = $this -> getColumns( $table );
		$this -> restoreQuery();

		if ( is_array ( $column ) ) 
		{
			foreach ( $column as $single_column )
				$this -> whitelist( $single_column, $table );
		}
		else
		{
			if ( in_array ( $column, $list ) )
				return true;
			else
			{
				Logger::error('Whitelist',"Column '$column' not found in table '$table'");
				$this -> clearQuery();
				$this -> query(' -- ' );
			}
		}
	}


	/**
	 * seperator - Generates a partial query construct from arrays
	 *
	 * Generates a query for multiple columns by appending the ' = ? ' for parameterization
	 * and seperating each "column = ? " by a defined seperator
	 * and returns constructed query
	 *
	 * Only used by where() " column=? AND column=?  "  and update(), needs to be improved for others
	 *
	 * @param  array  &$columns  	The array of columns
	 * @param  string $seperator 	The string that seperates the 'column = ?' statements
	 * @return string 		Returns the constructed query fragment
	 */
	public function seperator( &$columns, $seperator )
	{

		for( $i = 0, $j = count($columns), $construct = null; $i < $j ; $i++ )
		{
			if ( $i > 0 )
				$construct .= $seperator;
			$construct .= $columns[$i] . " = ? ";
		}

		return $construct;
	}


	/**
	 * setPrefix - Prefixes names. 
	 *
	 * Used for prefixing column with table name, table with database name, etc.
	 *
	 * @param string $name         Word to prefix
	 * @param string $prefix_a     Primary Prefix (ex. table name or database name )
	 * @param string $prefix_b     Secondary prefix (ex. '.' or '_' )
	 * @param bool   $check        If set to true, checks if either prefix is already present in column before prefixing
	 * @return array|string        Returns the prefixed column or columns.
	 */
	public function setPrefix( &$name , $prefix_a = null, $prefix_b = '.', $check = true )
	{

		if ( !isset($prefix_a) )
			$prefix_a = $this -> table;

		if( is_array( $name ) )
		{
			foreach( $name as $single_name)
				$prefixed_array[] = $this -> setPrefix ( $single_name, $prefix_a, $prefix_b, $check );
			if ( isset ( $prefixed_array ) )
				return $name = $prefixed_array;

		}
		elseif ( ( strpos( $name, $prefix_a ) === false ) && ( strpos( $name, $prefix_b ) === false ) && $check )
			return $name = $prefix_a . $prefix_b . $name;	
		else
			return $name;
	}


	/**
	 * getPrefix - Retrieve prefix name.
	 *
	 * @param  string $name         Word to prefix
	 * @param  string $prefix_b     Secondary prefix (ex. '.' or '_' )
	 * @return string|bool         Returns the prefix name.
	 */
	public function getPrefix( $name, $prefix_b = '.' )
	{
		if ( is_array( $name ) )
			return null;
		$position = strpos( $name, $prefix_b );
		if ( $position !== false )
			return $prefix_a = substr( $name, 0, $position );
		else
			return null;
	}


	/**
	 * unPrefix - Remove prefix from name.
	 *
	 * @param string $name         Prefixed word.
	 * @param string $prefix_b     Secondary prefix (ex. '.' or '_' )
	 * @return string  	       Returns un-prefixed name.
	 */
	public function unPrefix( &$name , $prefix_b = '.' )
	{
		if ( is_array( $name ) )
			return $name;
		$position = strpos( $name, $prefix_b );
		if ( $position !== false )
			return $name = substr( $name, ( $position + 1 ) , strlen( $name ) );
	}


	/**
	 * alias - Aliases column names 
	 *
	 * Uses the form: PREFIX_column
	 *
	 * @param $column The column to alias
	 * @param $prefix The prefix to use. ( Typically a table )
	 */
	public function alias( &$column, $prefix )
	{
		if ( is_array( $column ) )
		{
			foreach ( $column as &$single_column )
				$this -> alias( $single_column, $prefix );
		}
		else
		{
			$position = strpos( $column, '.' ); // If prefixed, gets the un-prefixed column name
			if ( $position !== false )
				return $column = $column . ' AS ' . $prefix . '_' . substr( $column,($position + 1));
			else
				return $column = $column . ' AS ' . $prefix . '_' . $column;
		}
	}


	/**
	 * select - Performs a database select query
	 *
	 * Recieves the column to select
	 *
	 * @param mixed  $column 			Column of interest,  defaults to '*'
	 * @param string $table 			Table of interest, defaults to $this -> table 
	 * @param bool   $autoalias 			If set to true, aliases columns with form: table_column
	 * @param bool   $is_distinct 			If set to true, includes 'distinct' in query
	 * @return 	 QueryBuilder 			The current QueryBuilder object.
	 * @uses 	 QueryBuilder::whitelist() 	Whitelists column to ensure it is a valid table column
	 * @uses 	 QueryBuilder::setPrefix() 	Prefixes table columns
	 * @uses 	 QueryBuilder::query() 		Holds partial query
	 */
	public function select( $column = '*', $table = null, $autoalias = false, $is_distinct = false)
	{
		$this -> counter['select']++;

		if ( !isset($table)  )
			$table = $this -> table;

		$distinct = ( $is_distinct )   ? 'DISTINCT' : null ;

		if ( $column !== '*' )
		{
			$this -> whitelist($column, $table);
			$this -> setPrefix( $column, $table );
			if ( $autoalias )
				$this -> alias( $column, $table );
		}

		if(is_array($column))
			$column = implode( $column, ',');

		if ( $this -> counter['select'] === 1 )
			$this -> query ("SELECT $distinct $column ");
		else
			$this -> query (", $column ");
		return $this;
	}


	/**
	 * from - The 'from table' fragment of SQL query
	 *
	 * @param string $table 
	 * @return QueryBuilder 	The current QueryBuilder object.
	 * @uses 	 QueryBuilder::query() 		Holds partial query
	 */
	public function from ( $table = null)
	{
		$this -> counter['from']++;
		if ( !isset($table) )
			$table = $this -> table;
		$this -> query (" FROM $table ");
		return $this;
	}


	/**
	 * insert - uses query to insert into table
	 *
	 * Takes a value and its column and performs insert. 
	 * Can accept multiple values to be inserted into multiple columns by passing it 
	 * Note: If using to insert POST data, ensure the order of $_POST array keys (order in HTML form) match $column array order
	 *
	 * @param mixed  $value  The value to be inserted.  
	 * @param string $column The  table column to be inserted into. Defaults to model -> column, the lower-case name of controller.
	 * @param string $table  The table to insert into.
	 * @return QueryBuilder 	The current QueryBuilder object.
	 * @uses 	 QueryBuilder::whitelist() 	Whitelists column to ensure it is a valid table column
	 * @uses 	 QueryBuilder::query() 		Holds partial query
	 * @uses 	 QueryBuilder::query_data() 	Holds the partial query's data
	 */
	public function insert( $value, $column = null, $table =  null)
	{
		$this -> counter['insert']++;

		if ( !isset ($table) )
			$table = $this -> table;
		$this -> whitelist($column, $table);

		$value_string = ' ? ';
		if ( is_array($value) )
		{
			for ( $i = 1, $j = count($value); $i <= $j ; $i++ )
			{
				if ($i >  1)		
					$value_string .= ", ? ";
			}
			$column_string = implode(',', $column );
		}
		else
			$column_string =& $column;

		$this -> query ("INSERT INTO $table ($column_string) VALUES( $value_string )");
		$this -> query_data($value);
		return $this;
	}


	/**
	 * update - uses query to update table
	 *
	 * @param mixed $new        New value to replace an existing value
	 * @param mixed $new_column The columnn of for the new value
	 * @param string $table     The table to update.
	 * @return QueryBuilder 	The current QueryBuilder object.
	 * @uses 	 QueryBuilder::whitelist() 	Whitelists column to ensure it is a valid table column
	 * @uses 	 QueryBuilder::query() 		Holds partial query
	 * @uses 	 QueryBuilder::query_data() 	Holds the partial query's data
	 */
	public function update($new, $new_column = null, $table = null )
	{
		$this -> counter['update']++;
		if ( !isset($table) )
			$table = $this -> table;
		$this -> whitelist($new_column, $table);

		if ( is_array($new) )
			$values = $this -> seperator( $new_column, ' , ');
		else
			$values = $new_column . ' = ? ';
		$this -> query ("UPDATE $table SET $values");
		$this -> query_data( $new );
		return $this;
	}


	/**
	 * remove - uses query to remove from table 
	 *
	 * @param  string 	$table 			The table to be removed from. Defaults to model -> table
	 * @return QueryBuilder 			The current QueryBuilder object.
	 * @uses 	 	QueryBuilder::query() 	Holds partial query
	 */
	public function remove($table = null )
	{
		$this -> counter['remove']++;
		if ( !isset($table) )
			$table = $this -> table;
		if ( !isset($column) )
			$column = 'id';
		$this -> query ("DELETE FROM $table");
		return $this;
	}


	/**
	 * where 
	 *
	 * Recieves the value of a known/reference column, the name of the known/reference column.
	 *
	 * @param mixed  $ref        			Reference value 
	 * @param mixed  $ref_column 			The column the value belongs to
	 * @param string $table      			The table to whitelist the columns against
	 * @uses 	 QueryBuilder::whitelist() 	Whitelists column to ensure it is a valid table column
	 * @uses 	 QueryBuilder::setPrefix() 	Prefixes table columns
	 * @uses 	 QueryBuilder::seperator() 	Correctly formats multiple columns 
	 * @uses 	 QueryBuilder::query() 		Holds partial query
	 * @uses 	 QueryBuilder::query_data() 	Holds the partial query's data
	 */
	public function where( $ref, $ref_column = null , $table = null )
	{
		$this -> counter['where']++;
		if ( !isset($table) )
			$table = $this -> table;
		$this -> whitelist($ref_column, $table );
		$this -> setPrefix( $ref_column, $table );

		if ( isset($ref) && !is_array($ref) )
			$where = $ref_column . " = ? ";
		elseif ( is_array($ref) )
			$where = $this -> seperator( $ref_column, ' AND ');

		if ( isset( $where ) ) 
		{
			if ( $this -> counter['where']  > 1 )
				$statement = ' AND ' . $where;
			else 
				$statement = ' WHERE ' .$where;
			$this -> query ( $statement );
			$this -> query_data ($ref);
		}
		return $this;
	}


	/**
	 * order - Recieves the column to use for ordering, the type of sort.
	 *
	 * @param string $orderby - The column used for ordering
	 * @param string $sort - The type of sort (ex. DESC)
	 * @param string $table - The table to whitelist the columns against
	 * @uses 	 QueryBuilder::whitelist() 	Whitelists column to ensure it is a valid table column
	 * @uses 	 QueryBuilder::query() 		Holds partial query
	 */
	public function order( $orderby, $sort, $table = null)
	{
		$this -> counter['order']++;
		if ( !isset($table) )
			$table = $this -> table;
		if ( isset( $orderby ) && ( stristr( $sort, 'asc' ) || stristr( $sort, 'desc' ) ) )
		{
			$this -> whitelist($orderby,$table);
			$order = " ORDER BY $table.$orderby $sort";
			$this -> query ( $order ); 
		}
		return $this;
	}


	/**
	 * limit - Recieves the limit of rows to return
	 *
	 * @param  integer 	$limit  		Number of rows to return
	 * @param  integer 	$offset 		The record to start at.
	 * @return QueryBuilder 			The current QueryBuilder object.
	 * @uses 	 	QueryBuilder::query() 	Holds partial query
	 */
	public function limit($limit, $offset = null)
	{
		$this -> counter['limit']++;
		if ( isset( $limit) )
		{
			$limit = " LIMIT $limit";
			if ( isset( $offset)  )
				$limit .= ',' . $offset;
			$this -> query ( $limit ); 
		}
		return $this;
	}


	/**
	 * joining -  Joins two tables
	 *
	 * Takes the table 'a' and 'b', their respective columns and the type of join.
	 *
	 * @param string $table_b  			The table to join to.
	 * @param mixed  $columns_a 			The columns of table_a to match with columns of table b
	 * @param mixed  $columns_b 		 	The columns of table_b
	 * @param string $type      			Join type: INNER, LEFT, RIGHT, OUTER
	 * @param string $table_a   			The main table, defaults to $this -> table.
	 * @return QueryBuilder 			The current QueryBuilder object.
	 * @uses 	 QueryBuilder::whitelist() 	Whitelists columns to ensure it is a valid table column
	 * @uses 	 QueryBuilder::setPrefix() 	Prefixes table columns
	 * @uses 	 QueryBuilder::seperator() 	Correctly formats multiple columns 
	 * @uses 	 QueryBuilder::query() 		Holds partial query
	 */
	public function joining( $table_b, $columns_a, $columns_b , $type = 'LEFT OUTER', $table_a = null ) {
		$this -> counter['joining']++;
		if ( !isset($table_a) )
			$table_a = $this -> table;
		$this -> whitelist($columns_a, $table_a);
		$this -> whitelist($columns_b, $table_b);
		$this -> setPrefix ( $columns_a, $table_a );
		$this -> setPrefix ( $columns_b, $table_b );

		if  ( is_array ($columns_a) )
		{
			for ( $i = 0 , $j = count ( $column_a ), $conditions = null; $i <= $j; $i ++ )
				$conditions .= $column_a[$i] . '=' . $column_b[$i];
		}
		else
			$conditions = $columns_a . '=' . $columns_b;

		$statement = " $type JOIN $table_b ON $conditions";
		$this -> query ( $statement );
		return $this;
	}


	/**
	 * show - Retrieve table information
	 *
	 * @param string 	$table 			The database table
	 * @return 		QueryBuilder 		The current QueryBuilder object.
	 * @uses 		QueryBuilder::query() 	Holds partial query
	 */
	public function show ( $table )
	{
		$this -> counter['show']++;
		$this -> query("SHOW COLUMNS FROM $table");
		return $this;
	}


	/**
	 * getColumns - Retrieve table columns
	 *
	 * @param string $table The database table
	 * @return array        The columns of the table.
	 * @uses 		QueryBuilder::show() 	Retrieves table information
	 */
	public function getColumns ( $table )
	{
		if ( ! isset ( $this -> table_columns[$table] ) )
		{
			$this -> table_info[$table] = $this -> show( $table ) -> run();
			foreach( $this -> table_info[$table] as $table_row => $table_column)
				$this -> table_columns[$table][] = $table_column['Field'];
		}
		return $this -> table_columns[$table];
	}
}
?>
