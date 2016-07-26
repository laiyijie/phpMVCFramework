<?php
/*
@auther: Jason Lai

this is the core Controller Class 

the Transaction start in __construct, commit in showData mechod , rollback in Exception catch;

*/


class Controller{
	public static $view_path;

	protected $error_string;
	private $dbObj;

	public function __construct(){

		$this->dbObj = Db::getDbObject();

		$this->dbObj->autocommit(false);

	} 

	public function __destruct(){

		$this->dbObj->autocommit(true);


	}

	protected function showData($paras=null,$status=null){

		if ( $status == "error" || ( is_array($paras)&&isset($paras['status'])&&$paras['status']=='error') ) {

			$this->dbObj->rollback();

		}else{

			$this->dbObj->autocommit(true);
		}

		if (is_null($paras)&&is_null($status)) {

			exit ;
		}

		if ($status==null) {

			if (isset($_GET['callback'])) {

				echo $_GET['callback'] . "(" . json_encode($paras) . ")";

			}else{
				
				echo json_encode($paras);
			}
		}else{
			
			$downdata = array('status' =>$status ,"message"=>$paras );

			echo json_encode($downdata);
		}

		exit ;

	}

};