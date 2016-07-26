<?php
session_start(); //此处开启session
//init.php 将lib的查找路径进行添加，将project下有类的文件夹全部包含，并且设置了autoload，使得以后的文件可以不用每次都包含所有的类文件
require_once('./project/core/init.php'); 

$uri = $_SERVER['REQUEST_URI'];

//创建一个Dispatcher对象，其中 uri是访问的路径，后面的两个参数是默认的主页uri（次模型只有两级结构）
$dis = new Dispatcher($uri,'account','login');

//调用函数对访问请求进行分配
$dis->dispatch();

