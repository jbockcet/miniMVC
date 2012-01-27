<?php

class UserController extends Controller{

	public $user_id;
	public $logged_in;
	public $username;
	public $email;

	function __construct(){
		$this -> start();
	}

	function index(){
		if( $this -> get('logged_in'))
			$this -> useView();
		else
			$this -> useView('login');
	}

	function start(){
		session_start();
		$this -> user_id = session_id();
	}

	function login(){
		$username = $_POST['username'];
		$password = md5( $_POST['password'] );
		if ( ( isset($username) && isset($password) ) )
			$valid = $this -> model -> select('*', array( $username, $password), array('user','password'));
		if($valid)
			$this -> logged_in = true;	
		$this -> set('logged_in', $this -> logged_in);
		$this->index();
	}

	function register(){
		$username = $_POST['username'];
		$password = md5( $_POST['password'] );
		$password_repeat = md5( $_POST['verify_password'] );
		$email 	= $_POST['email'];
		if ( ( isset($username) && isset($password) && isset($password_repeat) && isset($email)) && ($password == $password_repeat) )
			if ($this -> model -> insert(array($username, $password,$email), array('user', 'password','email') ) )
				$this -> logged_in = true;	
		$this -> set('logged_in', $this -> logged_in);
		$this->index();
	}

	function members(){
			$data = $this -> model -> select('*');
	}

	function logout(){
		session_destroy();
	}

	function set($property,$value){
		$_SESSION[$property] = $value;
	}

	function get($property){
		if ( isset( $_SESSION[$property] ) )
			return $_SESSION[$property];
		else 
			return false;
	}

	function timeSince($action, $difference = null, $true_if_unset = true ){
		$time_set = $this -> get($action);
		if ( ( $time_set != false ) && ( isset( $difference ) ) && ( ( time() - $time_set ) > $difference ) ) 
			return true;
		elseif( !isset($difference) ) {
			$this -> set( $action, time() );
			return false;
		} elseif( ($time_set == false ) && ( $true_if_unset == true) ) 
			return true;
	}
}

?>
