<?php

/*
@auther: Jason Lai
testing in local:
http://127.0.0.1/framework/info/test
output:
{"status":"done","message":"testing"}

*/

class InfoController extends Controller{
	public function test(){
		$this->showData('testing','done');
	}
}
