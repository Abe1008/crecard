<?php
/**
 * Copyright (c) 2021. Alexey Eremin
 * 20.05.21 10:42
 */

/**
 * Created by PhpStorm.
 * User: ae
 * Date: 20.05.2021
 * Time: 10:42
 */

/*
 * Вычислить долг по платежам
 *
 * Справки:
 * https://www.tinkoff.ru/cards/credit-cards/tinkoff-platinum/faq/how-to-use-a-credit-card/grace-period/
 * https://journal.tinkoff.ru/grace-period/
 *
 */

require_once "common.php";

$starttime = microtime(true); // начало скрипта

list($r,$g, $lim) = getVals("SELECT rday, gracedays, lim  FROM users WHERE uid=$Uid");
$RDay       = intval($r); // расчетный день
$GraceDays  = intval($g); // безпроцентный период

if($RDay < 1 || $GraceDays < 1) echo("Нет данных для расчета ");

$ssql = "CREATE TEMPORARY TABLE tmp_tabl (
              id int auto_increment primary key,
              uid int,
              dat datetime,
              sm  double,
              ost double default 0
          );          
        ";
execSQL("DELETE FROM tmp_tabl WHERE uid=$Uid;");

// вычислим общую сумму оплат
$suop = 0.0 + getVal("SELECT SUM(sm) FROM pays WHERE uid=$Uid AND payoff=1;");
$sost = 0.0 + $suop; // остаток от суммы оплат
// составим таблицу долга и даты погашения каждой покупки
$sql = "SELECT id,dat,sm FROM pays WHERE uid=$Uid AND payoff=0 ORDER BY dat,id;";
$res = queryDb($sql); //
$sdolg = 0.0;
while(list($id,$dat,$sm) = fetchRow($res)) {
  $s = 0.0 + $sm;
  $suop = $suop - $s;     // баланс счета
  if($s <= $sost) {
    $sost = $sost - $s;   // остаток суммы погашения
    $s = 0;               // долг по покупке
  } else {
    $s = $s - $sost;      // долг по покупке
    $sost = 0;
  }
  $sdolg = $sdolg + $s;   // общая сумма долга
  $do = dolgDay($dat);    // день оплаты
  $sql = "INSERT INTO tmp_tabl (uid,dat,sm,ost,dato) VALUES ($Uid, '$dat', $sm, $s, '$do');";
  execSQL($sql);
}
$res->close();

$datpay = getVal("SELECT MIN(dato) from tmp_tabl WHERE uid=$Uid AND ost > 0.005");   // дата ближайшей оплаты
$minpay = 0.01 * intval(getVal("SELECT 100*SUM(ost) FROM tmp_tabl WHERE uid=$Uid AND dato='$datpay';"));  // ближайшая сумма оплаты

// выставим флаги, суммы
dirtyDolg(0);               // признак пересекта долга
Dolg($sdolg);                   // сумма долга
minimalPay($minpay);            // сумма минимального платежа
datePay(date2rus($datpay));     // дата минимального платежа
Ostatok($lim + $suop);      // остаток на счета

// время выполнения
$finishtime = microtime(true);
$str = sprintf('%0.3f', $finishtime - $starttime);

// отобразим на экране строку с времен выполнения
echo "<small>calculation $str sec</small>\n";

/**
 * Расчет даты оплаты долга без процентов
 * @param string $dat  дата покупки
 * @return string дата оплаты
 */
function dolgDay($dat)
{
  global  $RDay, $GraceDays;
  // http://old.code.mu/books/php/base/rabota-s-datami-v-php.html
  $dat1 = date_create($dat);
  list($y1, $m1, $d1) = date2ymd($dat1); // дата год, месяц, число
  $yr = $y1;
  $mr = $m1;
  // если число операции больше расчетного дня, то расчетный день операции будет
  // в следующем месяце
  if($d1 > $RDay) {
    $s = sprintf('%04d-%02d-01', $y1, $m1);
    $a = date_create($s)->modify('1 month');
    $yr = $a->format('Y');  // год расчетного дня операции
    $mr = $a->format('m');  // месяц расчетного периода
  }
  https://www.tinkoff.ru/cards/credit-cards/tinkoff-platinum/faq/how-to-use-a-credit-card/grace-period/
  $rd = onmonthday($yr,$mr, $RDay);  // дата расчетного периода для данного платежа
  $do = $rd -> modify("$GraceDays days");  // прибавляем грэйс период
  $sd = $do -> format('Y-m-d');
  return $sd;
}

/**
 * Вернуть дату, если день больше кол-ва дней в месяце,
 * то возращает последний день месяца
 * @param int $y  год
 * @param int $m  месяц
 * @param int $d  число
 * @return DateTime дата месяца или последний день месяца
 */
function  onmonthday($y, $m, $d)
{
  $y = intval($y);  $m = intval($m);  $d = intval($d);
  $s = sprintf('%04d-%02d-%02d', $y, $m, $d);
  $dat = date_create($s);
  $m1 = intval($dat->format('m'));  // месяц
  if($m1 != $m) {
    // если месяц различается, значит число больше числа дней в месяце
    $s = sprintf('%04d-%02d-01', $y,$m);
    $d1 = date_create($s);              // первый день месяца даты
    //$d2 = $d1 -> modify('1 month');
    //$dat = $d2 -> modify('-1 day');
    $s = $d1 -> format('Y-m-t');  // последний день месяца
    $dat = date_create($s);
  }
  return $dat;
}

/**
 * Возращает год, месяц, число даты в виде массива целых чисел
 * @param DateTime $dat
 * @return array год, месяц, число даты
 */
function  date2ymd(DateTime $dat)
{
  $y = $dat->format('Y');  // год даты
  $m = $dat->format('m');  // месяц даты
  $d = $dat->format('d');  // число даты
  return array(intval($y),intval($m),intval($d));
}

/**
 * Преобразовать дату из SQL формат в русский
 * @param string $strdat дата в формате SQL YYYY-MM-DD
 * @return string дата в русском формате ДД.ММ.ГГГГ
 */
function  date2rus($strdat)
{
  $d = date_create($strdat);
  $s = $d->format('d.m.Y');
  return $s;
}
