<?php

class TestController extends Controller{

	function __construct(){
	}
	
	function index(){
		$data['content'] = "This is a test controller";
		$this -> useView('test',$data);
	}

	function countto($num){
		for($x=1; $x <= $num; $x++){
			$data['content'] .= $x;
		}
		$this -> useView('test',$data);
	}
	
	function say($phrase){
		$data['content'] = $phrase;
		$this -> useView('test',$data);
	}
	
}

?>