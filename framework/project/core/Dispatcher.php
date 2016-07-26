<?php
/*
author: Jason Lai
Dispatcher类用于解析路径并分配访问到不同的控制器
__construct（）方法是构造函数，在构造的同时将解析路径，
主要分为$controller,和$action两个部分，例如/account/getaccout，解析后 controller=account，action=getaccount
dispatch（）方法是用于分配访问，如上解析后，dispatch将构造AccountController,并调用其getaccount方法
在这个方法中，还包含了一个try catch模块，将捕捉到的异常转发给/core/ExceptionManager进行处理
*/
class Dispatcher{

	private $path;
	private $projectname;
	private $controller;
	private $action;
	private $map;
	private $indexcontroller;
	private $indexaction;

	public function __construct($path , $indexcontroller , $indexaction){
		
		$this->path=$path;
		
		$temp = explode('?', $path);
		
		if($temp[0]==''){
			
			header('HTTP/1.1 404 Not Found'); 

			header("status: 404 Not Found"); 

			exit;
		}

		//可以下载js和css 以及map文件
		$filefullname = $_SERVER['DOCUMENT_ROOT'] . $temp[0];

		if(preg_match("/^.*\.js$/", $temp[0])){

			Header("Content-type: text/javascript");

			readfile($filefullname);

			exit;
		}
		if (preg_match("/^.*\.css$/", $temp[0])) {
			
			Header("Content-type: text/css");
			
			readfile($filefullname);

			exit;
		}
		if (preg_match("/^.*\.map$/", $temp[0])) {
			
			// Header("Content-type: text/javascript");
			
			readfile($filefullname);

			exit;
		}
		if (preg_match("/^.*\.jpg$/", $temp[0])||preg_match("/^.*\.jpeg$/", $temp[0])) {


			Header("Content-type: image/jpeg");

			readfile($filefullname);

			exit();
		}

		if (preg_match("/^.*\.gif$/", $temp[0])) {
			

			Header("Content-type: image/gif");

			readfile($filefullname);

			exit();
		}
		if (preg_match("/^.*\.png$/", $temp[0])) {
			

			Header("Content-type: image/png");

			readfile($filefullname);

			exit();
		}
		if (preg_match("/^.*\.bmp$/", $temp[0])) {
			

			Header("Content-type: image/bmp");

			readfile($filefullname);

			exit();
		}
		$values=explode('/', $temp[0]);

		$this->map = Config::$PATH_TO_CONTROLLER;
		$this->projectname = $values[1];
		$this->controller = isset($this->map[$values[2]])?$this->map[$values[2]]:null;
		$this->action = isset($values[3])?$values[3]:null;
		$this->indexcontroller = isset($this->map[$indexcontroller])?$this->map[$indexcontroller]:null;
		$this->indexaction = $indexaction;
	}



	public function dispatch(){

		if (is_null($this->controller)||is_null($this->action)) {

			$this->controller = $this->indexcontroller;

			$this->action = $this->indexaction;
		}
		
		$controllerfile = Config::$root_path . "/project/controller/{$this->controller}.php";
		
		if (file_exists($controllerfile)){
			//此try-catch模块将捕捉到的异常转发到ExceptionManager进行处理
			try{

      			$app = new $this->controller();

      			$app->{$this->action}();

      		}catch(Exception $e){

      			$exmanager = new ExceptionManager($e);

      			$exmanager->manage();

      			exit;
      		}

     	}else{

			header('HTTP/1.1 404 Not Found'); 

			header("status: 404 Not Found"); 

			exit;
     	}
	}
}
