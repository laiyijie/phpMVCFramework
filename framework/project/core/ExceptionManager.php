<?php
class ExceptionManager extends Controller {

	private $mod;	
	const DEBUG = 1;
	const RELEASE = 0;

	public function __construct($e){

		parent::__construct();

		$this->mod = Config::$debug;
		
		$this->e = $e;
	}

	public function __destruct(){
		
	}

	public function isDebug(){

		if ($this->mod == self::DEBUG) {

			return true;

		}else{
			
			return false;
		}
	}

	public function manage(){

		$err ;
		$err['status'] = 'error';
		$err['message'] = $this->e->getMessage();
		$this->showData($err);

		if ($this->isDebug()) {

			var_dump($this->e);
		}

		exit;
	}



}
