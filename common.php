<?php
/**
 * (C) 2018. Aleksey Eremin
 * 04.09.18 23:06
 * 17.05.2021
 *
 * Created by PhpStorm.
 * User: ae
 * Date: 20.02.2018
 * Time: 22:55
 */
/*
 * общие функции и классы
 */
require_once "MyDB.php";
// объект базы данных
$My_Db = new MyDB() ;
// время ожидания активности пользователя, сек (3 ч)
define('TIMEOUT_USER_ACTIVITI', 10800); //10800

// имя переменной сессии
// код пользователя
define('UID', 'cre_uid');
// признак - грязный долг (надо пересчитывать)
define('DIRTYDOLG', 'cre_dirty_dolg');
// значение долга
define('DOLG', 'cre_dolg');
// признак платежи-оплаты
define('PAYOFF', 'cre_payoff');

require_once "funcs.php";
// запуск сессии
session_start();
// идентификатор пользователя
if(!array_key_exists(UID, $_SESSION)) {
  // если пользователь не зарегистрирован - будет всё читать
  $_SESSION[UID] = 0;
}
// код пользователя
$Uid = intval($_SESSION[UID]);

//TODO для отладки
$Uid = 1;

if($Uid > 0) {
  // время ожидания активности пользователя
  test_timeout_user_actitiviti(TIMEOUT_USER_ACTIVITI);
}

// вид отображаемых данных 0 - покупки, 1 - погашения
$PayOff = 0;
if(array_key_exists('payoff', $_REQUEST)) {
  $_SESSION[PAYOFF] = intval($_REQUEST['payoff']);
}
if(array_key_exists(PAYOFF, $_SESSION)) {
  $PayOff = intval($_SESSION[PAYOFF]);
}
