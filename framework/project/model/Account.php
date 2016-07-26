<?php
/*
Author:Jason Lai
Account类：用于控制账户的创建、更改以及权限控制
*/

class Account{

	const TABLE_NAME = 'account';

	public $username;
	public $nickname = null;
	public $password;
	public $level = null;


	public function __construct($uname=null)
	{
		if ($uname) {

				$this->username = $uname;

				if (!Db::load($this, array('username' => $uname), Config::$dbname, self::TABLE_NAME)) {

					throw new Exception('user_have_not_registered', 1);
				}
		}

	}

	public static function register($username,$nickname,$password){

		$acc = new Account();

		if (Db::load($acc, array('username' => $username),Config::$dbname,self::TABLE_NAME)) {

			throw new Exception("user_already_registered", 1);	
		}

		$newAccount = new Account();

		$newAccount->username = $username;

		$newAccount->nickname = $nickname;

		$newAccount->password = $password;

		$newAccount->saveToDb();
		
		return $newAccount();

	}

	public function saveToDb(){
	
		return Db::save($this,array('username'=>$this->username),Config::$dbname,'account');
	
	}

};

