<?php
/*
Author:Jason Lai
Config 类 用来记录配置参数，便于部署

变量：
$database：用于指明进行与mysql连接的三个参数
$PART_SET：用于记录用户可以更改的基础信息，名称是表中的项名。

*/

class Config{
	public static $database= array(
		'server' =>'localhost' ,
		'username' => 'laiyijie',
		'password' => 'laiyijie' );

	public static $PATH_TO_CONTROLLER=array(
		'account'=>'AccountController',
		'info'=>'InfoController'
		);
	public static $root_path ="C:/wamp/www/framework";
	public static $dbname = 'test';
	public static $debug = 0;
	public static $file_saving_path = './file';
};
