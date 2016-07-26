<?php

class AccountController extends Controller{


	public function register(){

		$username = $_GET['username'];

		$nickname = $_GET['nickname'];

		$password = $_GET['password'];

		$acc = Account::register($username,$nickname,$password);

		if ($acc) {

			$this->showData($acc,'done');
		}else{

			$this->showData('regiseter_failed','error');
		}

	}


};