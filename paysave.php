<?php
/**
 * (C) 2018. Aleksey Eremin
 * 04.09.18 23:09
 * 17.05.2021
 *
 * Created by PhpStorm.
 * User: ae
 * Date: 21.02.2018
 * Time: 14:55
 */
/*
 * сохранение результата редактирования в поле таблицы
 * https://appelsiini.net/projects/jeditable/
 * должны получить два аргумента id и value
 */

define('BAD_REGION', "?-Error-нельзя редактировать");

require_once "common.php";
// открытая БД $My_Db

require_once "Ifile.php";
$MyIfile = new Ifile(); // общий объект

if($Uid < 0) die("?-Error-требуется авторизация");

// Вставка новой записи?
// аргумент: newrecord - код оператора
if(array_key_exists('newrecord', $_REQUEST)) {
  $opId  = intval($_REQUEST['newrecord']);
  doNewrecord($opId);
  exit();
}

// удалить документ
// аргумент: delDoc номер кода записи
if(array_key_exists('delDoc', $_REQUEST)) {
  $idRec = intval($_REQUEST['delDoc']);  // код записи
  doDelDoc($idRec);
  exit();
}

// удалить запись
// аргумент: delRec номер кода записи
if(array_key_exists('delRec', $_REQUEST)) {
  $idRec = intval($_REQUEST['delRec']);  // код записи
  doDelRec($idRec);
  exit();
}

// добавить вложенный документ
// аргумент: addDoc номер кода записи
if(array_key_exists('addDoc', $_REQUEST)) {
  $idRec = intval($_REQUEST['addDoc']);  // код записи
  doAddDoc($idRec);
  exit();
}

// обработка редактирования
// параметры editable.js
$f_id  = $_REQUEST['id'];
$f_val = $_REQUEST['value'];
if(empty($f_id)) {
    die ("?-Error-Нет нужных аргументов [" . __FILE__ . " " . __LINE__ . ']');
}

// первая буква - поле D (dat) или S (subj)
$l1 = substr($f_id, 0, 1);  // первая буква
$Id = intval(substr($f_id,1));   // номер id записи в  табл. files

//
// $fldval - записываем в БД
// $f_val  - пишем на странице
switch ($l1) {
  // дата, из $f_value выделим дату
  case 'D':
    $fldnam = 'dat';
    $fldval = str2dat($f_val);  // записываем в БД
    if(!validateDate($fldval)) die("data error");
    $f_val  = dat2str($fldval); // пишем на странице
    break;
  // строка сумма
  case 'S':
    $fldnam = 'sm';
    $fldval = trim($f_val);   // записываем в БД
    $f_val  = sprintf('%.2f',$fldval);        // пишем на странице
    dirtyDolg(1);
    break;
  // строка примечание
  case 'P':
    $fldnam = 'prim';
    $fldval = trim($f_val);   // записываем в БД
    $f_val  = $fldval;        // пишем на странице
    break;
  default:
    die("?-Error-неверный формат идентификатора редактируемого поля");
}
$stmt = prepareSql("UPDATE pays SET $fldnam=? WHERE id=$Id;");
$stmt->bind_param('s', $fldval);
if(! $stmt->execute()) die("?-Error-Ошибка обновления записи");
// $stmt->close();
//
echo $f_val;

/**
 * Добавить новую запись для пользователя
 * @param int $userId  код оператора
 */
function  doNewrecord($userId)
{
  $f_Dat   = $_REQUEST['f_dat'];    // дата платежа
  $f_Sm    = $_REQUEST['f_sm'];     // сумма платежа
  $f_Prim  = $_REQUEST['f_prim'];   // примечание
  $f_payoff= $_REQUEST['f_payoff']; // признак покупка/платеж
  $sql = "INSERT INTO pays   (uid, dat, sm, prim, payoff)
                      VALUES (  ?,   ?,  ?,    ?,      ?);";
  $stmt = prepareSql($sql);
  $stmt->bind_param('isssi', $userId,$f_Dat, $f_Sm, $f_Prim, $f_payoff);
  if(! $stmt->execute() ) {
    die("Ошибка записи");
  }
  // $stmt->close(); //
  dirtyDolg(1);
  gotoIndex();
}

/**
 * Удалить запись
 * @param int $idRec  код записи
 */
function  doDelRec($idRec)
{
  $ifi = get_ifile($idRec); // если не тот регион - сеанс прервется
  if(!empty($ifi)) {
    die("Есть файл вложения");
  }
  execSQL("DELETE FROM pays WHERE id=$idRec;");
  //
  dirtyDolg(1);
  gotoIndex();
}

/**
 * Добавить вложенный документ в запись
 * @param int $idRec  код записи
 */
function  doAddDoc($idRec)
{
  global $MyIfile, $Uid;
  // получим код файла, которое хранится в записи
  $ifi = get_ifile($idRec);  // если не тот регион - процесс убьется
  // есть ли уже файл ?
  if ($ifi > 0) die("Файл уже есть");
  $ifile = $MyIfile->addLoadFile($Uid);  // индекс вставленной записи файла
  execSQL("UPDATE pays SET ifile=$ifile WHERE id=$idRec;");
  gotoIndex();
}

/**
 * Удалить документ в записи
 * @param int $idRec  код записи
 */
function  doDelDoc($idRec)
{
  global $MyIfile;
  $ifi = get_ifile($idRec); // не тот регион - прервать работу
  if($ifi > 0) {
    execSQL("UPDATE pays SET ifile=0 WHERE id=$idRec;");  // удалим код файла
    // проверим кол-во упоминаний этого файла в платежах
    $a = getVal("SELECT count(*) FROM pays   WHERE ifile=$ifi;");
    if(intval($a) == 0) {
      $MyIfile->deleteIfile($ifi);  // удалить запись о файле и сам файл
    }
  }
  gotoIndex();
}

/**
 * Получить код файла, код оператора из записи таблицы документов по коду документа
 * Если регион оператора не совпадает, то завершаем все
 * Если регион записи не совпадает с текущим, то завершить все.
 * Пример: list($iFile,$opId,$reg)=get_filename_opid($idFile)
 * @param int $idRec  код записи платежа
 * @return mixed  массив с именем файла, кодом оператора и номером региона
 */
function  get_ifile($idRec)
{
  global $Uid;
  $row = getVal("SELECT ifile FROM pays WHERE id=$idRec AND uid=$Uid;");
  $ifi = intval($row);
  // не совпадает - убиваем сеанс
  //if($ifi < 1) die(BAD_REGION);  // не совпадает регион - убиваем сеанс
  return $ifi;
}

/**
 * Перейти на страницу списка
 */
function  gotoIndex()
{
  gotoLocation("index.php");
}
