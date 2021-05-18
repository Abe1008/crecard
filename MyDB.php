<?php
/**
 * (C) 2018. Aleksey Eremin
 * 04.09.18 23:08
 */
require_once ".mydb.php";
//$cccmydb = array (
// 'db'   => 'имя_базы',
// 'host' => 'адрес_хоста',
// 'usr'  => 'пользователь',
// 'pwd'  => 'пароль'
//);

class MyDB
    extends mysqli
{
  function __construct()
  {
    global $cccmydb;
    $c = $cccmydb;
    //parent::__construct($c['host'], $c['usr'], $c['pwd'], $c['db']);
    @ $this->connect($c['host'], $c['usr'], $c['pwd'], $c['db']);
    if($this->connect_errno) {
      die("?-Error-Ошибка открытия БД");
    }
    // $this->query("SET NAMES 'utf8';");
    // https://ru.stackoverflow.com/a/673331
    // https://www.php.net/manual/ru/mysqli.set-charset.php
    $this->set_charset('utf8'); // установить набор символов
  }

  function __destruct()
  {
    @ $this -> close();
    //echo " destruct ";
  }
}

