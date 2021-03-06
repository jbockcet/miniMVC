<?php
/**
 * Model class file.
 *
 * @author Z. Alem <info@alemmedia.com>
 */


/**
 * The model class is the parent for all application models.
 *
 * It serves to retrieve data and encapsulate data.
 * It is provided SQL access by the SQL() method which utilizes the QueryBuilder class.
 *
 */
class Model
{

	/**
	 * @var string The name of the Model object's database table.
	 */
	public $table;

	/**
	 * @var string The name of the Model object's epynomous table column.
	 */
	public $column;

	/**
	 * @var object Instance of Query class. Allows building database queries.
	 */
	public $sql;


	/**
	 * __construct
	 *
	 * Assigns the model the 'table' property, lowercase pluralized name of the controller class
	 * and the table's main column the lowercase singular name of the controller class.
	 */
	function __construct()
	{
		$column =  strtolower( get_class($this) );
		$table = $column . 's';
		$this -> table = $table;
		$this -> column = $column;
	}


	/**
	 * set - Assigns property to model
	 *
	 * @oaram mixed $property The name of the property to be assigned OR an array containing multiple property/value pairs
	 * @param mixed $value    The value of the property
	 */
	function set($property, $value = null)
	{
		if ( is_array( $property) )
		{
			foreach($property as $single_property => $single_value)
				$this -> set( $single_property, $single_value);
		}else
			$this -> $property = $value;
	}


	/**
	 * get - Returns the named property of the model
	 *
	 * @param  mixed $property 	The name of the property to be returned OR array containing multiple properties who's values are to be returned
	 * @return mixed 		The requested property.
	 */
	function get($property)
	{
		if ( is_array( $property) )
		{
			foreach($property as $single_property)
				$get_array[] = $this -> get( $single_property);
			return $get_array;
		}
		return $this -> $property;
	}


	/**
	 * SQL - A simple singleton wrapper for a QueryBuilder object
	 *
	 * Allows model to use single instance of QueryBuilder to 
	 * build queries in a sequential manner. 
	 * 
	 * @return QueryBuilder 	The loaded QueryBuilder object
	 * @uses   QueryBuilder 	Assigned as a property for automated loading of single instance.
	 */
	function SQL()
	{
		if ( !isset( $this -> sql ) )
		{
			$database = new Database();
			$this -> sql = new QueryBuilder( $database, $this -> table );
		}
		return $this -> sql;
	}

}

?>
