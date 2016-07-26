<?php
require_once 'config/Config.php';
	set_include_path(get_include_path().PATH_SEPARATOR . Config::$root_path."/project/core/");
	set_include_path(get_include_path().PATH_SEPARATOR . Config::$root_path."/project/core/config/");
	set_include_path(get_include_path().PATH_SEPARATOR . Config::$root_path."/project/controller/");
	set_include_path(get_include_path().PATH_SEPARATOR . Config::$root_path."/project/model/util/");
	set_include_path(get_include_path().PATH_SEPARATOR . Config::$root_path."/project/model/interfaces/");
	set_include_path(get_include_path().PATH_SEPARATOR . Config::$root_path."/project/model/");
	set_include_path(get_include_path().PATH_SEPARATOR . Config::$root_path."/project/lib/");

	function __autoload($object){
		
  		require_once("{$object}.php");
	}
