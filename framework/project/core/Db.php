<?php
/*
Author:Jason Lai
Db类：基本的数据库操作，以及数据库连接的获取

变量：
$conn：连接，应该采用getConnection()方法获取，不应当直接获取

方法：
getDbObject()：获取与mysql数据库的连接并返回单例。返回：Db对象
get($where,$start,$end,$dbname,$tablename):
	概述：
		get后将查找出某个表依照一定条件的所有列的所有数据
	参数：
	$where 是关联数组，里面的键值对将变成 `key1`='value1' and `key2`='value2' 的where语句
	$start、$end 将在sql语句中变成  limit $start,$end 用于限制查找条目
	$dbname：database的名称
	$tablename: 要获取的表的名称
	返回：
		result键值对数组 或 sql_error() 

put($array,$dbname,$tablename)：
	概述：
		相当于insert操作，在`$dbname`.`$tablename` 中插入一条
	参数：
		$array: 关联数组，对应的是插入的表项的名称和值
	返回：
		1或者sql_error()

set($array,$where,$dbname,$tablename):
	概述：
		相当于简单的update操作，更改`$dbname`.`$tablename`中的一条数据
	参数：
	$array,$where ：参照上面的get（）参数解释
	返回：
		1或者sql_error()
*/

class Db{

	private $conn;

	private static $dbObject;

	private function __construct(){

			$this->conn = mysql_connect(Config::$database['server'],
			Config::$database['username'],Config::$database['password']);

			if (!$this->conn) {

				throw new Exception(mysql_error(), DATABASE_CONNECTION_ERROR);

			}
			mysql_query( "SET character_set_client = utf8 "  , $this->conn );
			mysql_query( " SET character_set_connection = utf8 "  , $this->conn );
			mysql_query( " SET character_set_database = utf8 "  , $this->conn );
			mysql_query( " SET character_set_results = utf8 "  , $this->conn );
			mysql_query( " SET character_set_server = utf8 "  , $this->conn );
			mysql_query( " SET collation_connection = utf8 "  , $this->conn );
			mysql_query( " SET collation_database = utf8 "  , $this->conn );
			mysql_query( " SET collation_server = utf8 ;"  , $this->conn );

	}

	public static function getDbObject(){

		if(is_null(self::$dbObject)){

			self::$dbObject = new Db();

		}

		return self::$dbObject;
	}

	public function getConnection(){

		return $this->conn;
	}

	public function __destruct(){

		mysql_close($this->conn);
	}

	public function autocommit($flag){

		if ($flag) {

			mysql_query('set autocommit=true',$this->conn);	

		}else{

			mysql_query('set autocommit=false',$this->conn);
		}
	}

	public function commit(){

			mysql_query('COMMIT',$this->conn);

	}

	public function rollback(){

			mysql_query('ROLLBACK',$this->conn);
	}

	public function query($statement,$database){
		
		if(!mysql_query('USE `' . $database . '`',$this->conn)){

			throw new Exception("DB_ERROR:" . mysql_error(), 1);
		}

		$re = mysql_query($statement,$this->conn);
		
		if(!$re){

			throw new Exception("DB_ERROR:" . mysql_error(), 1);
		}
		return $re;
	}

	public function dropTable($dbname , $tablename){

		$tablename = mysql_real_escape_string();

		if (!mysql_query('drop table' . '`'.$dbname . '`.`' . $tablename . '`')) {
			
			throw new Exception("DB_ERROR:" . mysql_error(), 1);
			
		}
		return true;
	}

	public function get($where, $start, $end,$dbname,$tablename,$colums=null,$orderby=null,$lock=false ){

		$dbname = '`' . $dbname . '`';

		$tablename = '`' . $tablename . '`';

		$query;

		if ($colums == null) {

			$query = " SELECT * FROM $dbname.$tablename " ;

		}else{

			$query = " SELECT ";

			foreach ($colums as $key => $value) {

				$query .= '`' . mysql_real_escape_string($value) . '`,';

			}

			$query = substr($query, 0,strlen($query)-1);

			$query .= " FROM $dbname.$tablename ";
		}

		$qwhere = ' WHERE ';

		if (!is_null($where)) {

			if(is_array($where)){

				foreach ($where as $key => $value) {

					$qwhere .= '`' . mysql_real_escape_string($key) . '`=' . "'" . mysql_real_escape_string($value) . "' AND ";
				}

					$qwhere = substr($qwhere, 0,strlen($qwhere)-4);

			}else{

				if (!is_string($qwhere)) {

					throw new Exception("Db error! where is not right!", 1);
					
					exit;
				}

				$qwhere .= $where;
			}

		}else{

			$qwhere = '';

		}

		$qlimit = " LIMIT " . mysql_real_escape_string($start) . ',' . mysql_real_escape_string($end);

		$qorder = ' ORDER BY ';

		if ($orderby!=null) {

			foreach ($orderby as $value) {

				$qorder .= '`' . mysql_real_escape_string($value[0]) . '` ' . mysql_real_escape_string($value[1]) . ' , ';
			}
			
			$qorder = substr($qorder, 0,strlen($qorder)-2);

		}else{

			$qorder = '';
		}

		$query .= $qwhere . $qorder . $qlimit;

		if ($lock) {

			$query .= " FOR UPDATE ;";
		}else{
			$query .=" ;";
		}

		$result = mysql_query($query, $this->conn);

		if($result){

			$return = null;

			while($row=mysql_fetch_assoc($result)) {

       			$return[] = $row;
   			}

   			return $return;

		}else{

			throw new Exception("Get error:" . mysql_error(), 1);

			return false;
		}
	}


	//where:二维数组，长度是条件数量，每项为
	//array('tbleft'=>tbleft,'colleft'=>colleft,'tbright'=>tbright,'colright'=>colright,'cmp'=>cmp)
	//tables 为数组，为要联合查询的表
	//sTables 为要显示的table的列表
	//sColums为要显示的cols，二维数组，array(array('table'=>tablename,'col'=>colname))

	public function getView($where,$dbname,$tables,$sTables=null,$sColums=null,$orderby=null,$start=null, $end=null ){

		$dbname = '`' . $dbname . '`';
		
		$qtb = '';
		
		foreach ($tables as $key => $value) {

			$qtb .= $dbname . '.`' . mysql_real_escape_string($value) . '`,';

		}

		$qtb = substr($qtb, 0 , strlen($qtb)-1);

		$qcols= '';

		if (!is_null($sTables)) {

			foreach ($sTables as $key => $value) {

				$qcols .= $dbname . '.`' . mysql_real_escape_string($value) . '`.*,';
			}

		}

		if (!is_null($sColums)) {

			foreach ($sColums as $key => $value) {

				$qcols .= $dbname . '.`' . mysql_real_escape_string($value['table']) . '`.`' . mysql_real_escape_string($value['col']) . '`,';
			}
		}

		if (is_null($sTables) && is_null($sColums)) {

			$qcols .= '*';
		
		}else{

			$qcols  = substr($qcols, 0 , strlen($qcols) - 1);
		}

		$qwhere = ' WHERE ';

		if (!is_null($where)) {

			if(is_array($where)){

				foreach ($where as $key => $value) {

					$value['cmp'] = strtoupper($value['cmp']);

					switch ($value['cmp']) {

						case 'LIKE':
						case 'NOT LIKE':
						case 'RLIKE':
						case 'NOT RLIKE':

							if (!isset($value['val'])) {
								
								throw new Exception("Db error: getView Error! Input not right!", 1);
							}

							$qwhere .= '`' . mysql_real_escape_string($value['tbleft']) . '`.`' . mysql_real_escape_string($value['colleft']) . '` ' . mysql_real_escape_string($value['cmp']) . " '" . mysql_real_escape_string($value['val']) . "' AND ";
						
							break;

						case 'IN':
						case 'NOT IN':

							if (!is_array($value['val'])) {
								
								throw new Exception("Db error: getView Error! Input not right!", 1);
							}

							$qwhere .= '`' . mysql_real_escape_string($value['tbleft']) . '`.`' . mysql_real_escape_string($value['colleft']) . '` ' . mysql_real_escape_string($value['cmp']) . ' (';

							foreach ($value['val'] as $in_element) {
								
								$qwhere .= mysql_real_escape_string($in_element) . ',';
							}

							$qwhere = substr($qwhere, 0,strlen($qwhere)-1);

							
							$qwhere .=  ") AND ";;
							
							break;


						default:

							if (isset($value['val'])) {
								
								$qwhere .= '`' . mysql_real_escape_string($value['tbleft']) . '`.`' . mysql_real_escape_string($value['colleft']) . '`' . mysql_real_escape_string($value['cmp']) . "'" . mysql_real_escape_string($value['val']) . "' AND ";

							}else{

								$qwhere .= '`' . mysql_real_escape_string($value['tbleft']) . '`.`' . mysql_real_escape_string($value['colleft']) . '`' . mysql_real_escape_string($value['cmp']) . "`" . mysql_real_escape_string($value['tbright']) . '`.`' . mysql_real_escape_string($value['colleft']) . "` AND ";

							}

							break;
					}
				}

					$qwhere = substr($qwhere, 0,strlen($qwhere)-4);

			}else{

				if (!is_string($qwhere)) {

					throw new Exception("Db error! where is not right!", 1);
					
					exit;
				}

				$qwhere .= $where;
			}

		}else{

			$qwhere = '';

		}

		$qlimit = '';
		
		if ($start) {

			$qlimit = " LIMIT " . mysql_real_escape_string($start) . ',' . mysql_real_escape_string($end) . ';';
		}

		$qorder = ' ORDER BY ';

		if ($orderby!=null) {

			foreach ($orderby as $value) {

				$qorder .= '`' . mysql_real_escape_string($value[0]) . '` ' . mysql_real_escape_string($value[1]) . ' , ';
			}
			
			$qorder = substr($qorder, 0,strlen($qorder)-2);

		}else{

			$qorder = '';
		}

		$query = 'SELECT ' . $qcols . ' FROM ' . $qtb . ' ' . $qwhere . ' ' . $qorder . ' ' . $qlimit;

		// return $query;
		$result = mysql_query($query, $this->conn);

		return $result;
		/*if($result){

			$return = null;

			while($row=mysql_fetch_assoc($result)) {

       			$return[] = $row;
   			}

   			return $return;

		}else{

			throw new Exception("Get error:" . mysql_error(), 1);

			return false;
		}*/
	}

	public function search($where, $start, $end,$dbname,$tablename,$colums=null,$orderby = null ){

		if (is_null($where)) {
	
			return $this->get($where, $start, $end,$dbname,$tablename,$colums,$orderby);
	
		}

		$qwhere = '';

		if (is_array($where)) {

				foreach ($where as $value) {

					$qwhere .= '`' . mysql_real_escape_string($value[0]) . '`'. mysql_real_escape_string($value[1]) . "'" . mysql_real_escape_string($value[2]) . "' AND ";
				}

					$qwhere = substr($qwhere, 0,strlen($qwhere)-4);
		}

		return $this->get($qwhere, $start, $end,$dbname,$tablename,$colums,$orderby);
	}

	public function getLike($where, $start, $end,$dbname,$tablename,$colums=null ){
		
		$dbname = '`' . mysql_real_escape_string($dbname) . '`';

		$tablename = '`' . mysql_real_escape_string($tablename) . '`';

		$query;

		if ($colums == null) {

			$query = " SELECT * FROM $dbname.$tablename " ;

		}else{

			$query = " SELECT ";

			foreach ($colums as $key => $value) {

				$query .= '`' . mysql_real_escape_string($value) . '`,';
			}

			$query = substr($query, 0,strlen($query)-1);

			$query .= " FROM $dbname.$tablename ";
		}

		$qwhere = ' WHERE ';

		if (!is_null($where)) {

			foreach ($where as $key => $value) {

				$qwhere .= '`' . mysql_real_escape_string($key) . '` LIKE ' . "'%" . mysql_real_escape_string($value) . "%' OR ";
			}

				$qwhere = substr($qwhere, 0,strlen($qwhere)-4);

		}else{

			$qwhere = '';
		}

		$qlimit = " LIMIT " . mysql_real_escape_string($start) . ',' . mysql_real_escape_string($end) . ';';

		$query .= $qwhere . $qlimit;

		$result = mysql_query($query, $this->conn);

		if($result){

			$return = null;

			while($row=mysql_fetch_assoc($result)) {

       			$return[] = $row;
   			}

   			return $return;

		}else{

			throw new Exception("getLike error:" . mysql_error(), 1);
			
			return false;
		}
	}

	public function put($array,$dbname,$tablename){

		$dbname = '`' . mysql_real_escape_string($dbname) . '`';

		$tablename = '`' . mysql_real_escape_string($tablename) . '`';

		$query = "INSERT INTO $dbname.$tablename ";

		$columname = "(";

		$values = " VALUES (";

		foreach ($array as $key => $value) {

			$columname .= '`'. mysql_real_escape_string($key) . '`' . ',';

			$value = mysql_real_escape_string($value);
			
			$values .= "'" . $value . "'" . ",";

		}

		$columname[strlen($columname)-1] = ")";

		$values[strlen($values)-1] = ")"; 

		$query .= $columname . $values . ';';

		if(mysql_query($query, $this->conn)){

			return true;

		}else{
			
			throw new Exception("Put error:" . mysql_error(), 1);
			
			return false;
		}

	}

	public function set($array,$where,$dbname,$tablename){

		$dbname = '`' . mysql_real_escape_string($dbname) . '`';

		$tablename = '`' . mysql_real_escape_string($tablename) . '`';

		$query = "UPDATE $dbname.$tablename SET ";

		foreach ($array as $key => $value) {

			$value = mysql_real_escape_string($value);

			$query .= '`' . mysql_real_escape_string($key) . '`=' . "'" . $value . "'," ;
		}

		$query[strlen($query)-1] = ' ';

		$query .= ' WHERE ';

		foreach ($where as $key => $value) {

			$query .= '`' . mysql_real_escape_string($key) . '`=' . "'" . mysql_real_escape_string($value) . "' AND ";
		}

		$query = substr($query, 0,strlen($query)-4);

		$query .= ';';

		if(mysql_query($query, $this->conn)){

			return true;

		}else{

			throw new Exception("set error:" . mysql_error(), 1);

			return false;
		}
	}

	public function del($where,$dbname,$tablename ){

		$dbname = '`' . mysql_real_escape_string($dbname) . '`';

		$tablename = '`' . mysql_real_escape_string($tablename) . '`';

		$query = " DELETE FROM $dbname.$tablename " ;

		$qwhere = ' WHERE ';

		if (!is_null($where)) {

			foreach ($where as $key => $value) {

				$qwhere .= '`' . mysql_real_escape_string($key) . '`=' . "'" . mysql_real_escape_string($value) . "' AND ";
			}

				$qwhere = substr($qwhere, 0,strlen($qwhere)-4);

		}else{

			throw new Exception("Delete must have the where clause!", 1);
		}

		$query .= $qwhere;

		$result = mysql_query($query, $this->conn);

		if($result){

			return true;

		}else{

			throw new Exception("Delete error:" . mysql_error(), 1);
			
			return false;
		}
	}

	public function getFields($dbname,$tablename){

		$dbname = '`' . mysql_real_escape_string($dbname) . '`';

		$tablename = '`' . mysql_real_escape_string($tablename) . '`';

		$result = mysql_query("show columns from $dbname.$tablename ", $this->conn);

		if($result){

			$return = null;

			while($row=mysql_fetch_assoc($result)) {

       			$return[] = $row['Field'];
   			}

   			return $return;

		}else{

			throw new Exception("getFieds error:" . mysql_error(), 1);
			
			return false;
		}
	}

	public static function load(&$obj,$where,$dbname,$tablename,$lock=false){

		$dbObj = Db::getDbObject();

		$re = $dbObj->get($where,0,1,$dbname,$tablename,null,null,$lock);

		if (!$re) {
			return false;
		}

		$className = get_class($obj);
		
		foreach (array_pop($re) as $key => $value) {
			
			if (property_exists($className, $key)) {
				
				if (is_array($obj->{$key})) {

					$obj->{$key} = json_decode($value,1);
				
				}else{
				
					$obj->{$key} = $value;
				}
			}
		}

		return true;
	}

	public static function loadObjects(&$objs,$className,$where,$dbname,$tablename){

		$dbObj = Db::getDbObject();

		$re = $dbObj->get($where,0,1000,$dbname,$tablename);

		if (!$re) {
			return false;
		}
		foreach ($re as $key1 => $value1) {

			$obj = new $className();
			
			foreach ($value1 as $key => $value) {
				
				if (property_exists($className, $key)) {
					
					if (is_array($obj->{$key})) {

						$obj->{$key} = json_decode($value,1);
					
					}else{
					
						$obj->{$key} = $value;
					}
				}
			}

			array_push($objs, $obj);
			// var_dump($obj);
			// var_dump($objs);
		}
	
		return true;
	}

	public static function save($obj,$where,$dbname,$tablename){

		$dbObj = Db::getDbObject();

		$re = $dbObj->get($where,0,1,$dbname,$tablename);
		
		$className = get_class($obj);

		if (!$re) {
			
			$fields = $dbObj->getFields($dbname,$tablename);

			$temp_arr;
			
			foreach ($fields as $key => $value) {

				if (is_null($obj->{$value}) || (!property_exists($className, $value))) {
					
					continue;
				}

				$data;
				
				if (is_array($obj->{$value})) {
					
					$data = json_encode($obj->{$value});
				
				}else{
				
					$data = $obj->{$value};
				}

				$temp_arr[$value] = $data;

			}

			if( $dbObj->put($temp_arr,$dbname,$tablename)){

				$re = mysql_query('SELECT LAST_INSERT_ID()',$dbObj->getConnection());

				if ($re) {
					
					$r = mysql_fetch_array($re)[0];

					if (!$r){
						return true;
					}
					return $r;
					// var_dump(mysql_fetch_array($re));
				}else{

					return true;
				}

			}else{

				return false;
			}

		}else{

			$temp_arr;

			foreach (array_pop($re) as $key => $value) {
		
				if (property_exists($className, $key)) {

					$data;
					
					if (is_array($obj->{$key})) {

						$data = json_encode($obj->{$key});
					
					}else{
					
						$data = $obj->{$key};
					}

					$temp_arr[$key] = $data;
			
				}
			}

			return $dbObj->set($temp_arr,$where,$dbname,$tablename);
		}
	}

};
// $test = array('username' =>'11111' ,'password' =>'1234111114' ,'name' =>'12344' ,'uid' =>'12344' ,
// 		'level' =>'12344' ,'email' =>'12344' ,'team' =>'12344' ,'hiredate' =>'12344' ,
// 		'permission' =>'12344' ,'state' =>'12344' ,'firstleader' =>'12344' ,'secondleader' =>'12344' );
// echo Db::set($test,array('username' => 11111),'hradmin','account');
//  var_dump(Db::get(array('username' => '11111'),0,100,'hradmin','account'));
// echo $_SERVER['DOCUMENT_ROOT'];