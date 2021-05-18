<?php
/**
 * (C) 2018. Aleksey Eremin
 * 04.09.18 23:07
 * 17.05.2021
 *
 */

/*
 * Библиотека общих функций
 */

/**
 * Преобразование даты из формата SQL в строку русского формата DD.MM.YYYY
 * @param string $dat строка в формате SQL YYYY-MM-DD
 * @return string дата DD.MM.YYYY
 */
function dat2str($dat)
{
  $str = null;
  if(preg_match("/(\d{4})-(\d{1,2})-(\d{1,2}).*/",$dat, $mah)) {
    $y = $mah[1];  $m = $mah[2];  $d = $mah[3];
    $str = sprintf("%02d.%02d.%04d", $d,$m,$y);
  }
  return $str;
}

/**
 * Преобразование строки русского формата DD.MM.[YY]YY в дату формата SQL YYYY-MM-DD
 * @param string $str строка в формате DD.MM.[YY]YY (вместо точки может быть запятая)
 * @return string дата  YYYY-MM-DD
 */
function str2dat($str)
{
  $dat = null;
  $d = 0; $y = '00';
  if (preg_match("/(\d{1,2})[\.,](\d{1,2})[\.,](\d{2,4}).*/", $str, $match)) {
    $d = $match[1];
    $m = $match[2];
    $y = $match[3];
  } else if (preg_match("/(\d{2,4})[\.,-](\d{1,2})[\.,-](\d{1,2}).*/", $str, $match)) {
    $d = $match[3];
    $m = $match[2];
    $y = $match[1];
  }
  if ($d > 0) {
    if ($y < 100) {
      $y = '20' . $y;
    }
    $dat = sprintf("%04d-%02d-%02d", $y, $m, $d);
  }
  return $dat;
}

/**
 * Проверяет корректность строки даты с заданным форматом
 * http://php.net/manual/ru/function.checkdate.php
 * http://php.net/manual/ru/datetime.createfromformat.php
 * @param string $dat     строка даты
 * @param string $format  формат строки даты
 * @return bool true - дата корректна, false - неправильная дата
 */
function validateDate($dat, $format = 'Y-m-d')
{
  $d = DateTime::createFromFormat($format, $dat);
  return ($d) && ($d->format($format) == $dat);
}

/**
 * Формирует начало страницы html
 * @param string $title    заголовок страницы
 * @param string $t        добавка в заголовок
 */
function printHeadPage($title, $t='')
{
  echo <<<_EOF
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>$title</title>
  <link rel="stylesheet" type="text/css" href="css/style.css">
  <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css">
  <link rel="stylesheet" type="text/css" href="css/stylefileload.css">
  <link rel="stylesheet" type="text/css" href="css/popup.css">
  <script type="text/javascript" language="javascript" src="js/jquery.min.js"></script>
  <script type="text/javascript" language="javascript" src="js/jquery.dataTables.min.js"></script>
$t
</head>
<body>

_EOF;
}

/**
 * Заголовок страницы с редактированием таблиц
 * @param string $title заголовок
 */
function printHeadPageEdt($title)
{
  $t = <<<_EOF
  <script type="text/javascript" language="javascript" src="js/jquery.jeditable.js"></script>
  <script type="text/javascript" language="javascript" src="js/vendor/jquery.ui.widget.js"></script>
  <script type="text/javascript" language="javascript" src="js/jquery.fileupload.js"></script>
_EOF;
  printHeadPage($title, $t);
}

/**
 * вывести конец страницы
 */
function printEndPage()
{
  echo "\n</body>\n</html>\n";
}

/**
 * Переход в указанное место URL
 * @param string $url  URL перехода
 */
function gotoLocation($url)
{
  header("HTTP/1.1 301 Moved Permanently");
  header("Location: " . $url);
}

/**
 * Возвращает первое поле в первой строке, заданного SQL-запроса
 * @param string $sql SQL запрос
 * @return null значение поля
 */
function getVal($sql)
{
  $val = null;
  $res = queryDb($sql);
  if ($row = fetchRow($res)) $val = $row[0];
  $res->close();
  return $val;
}

/**
 * Возвращает массив значений первой строки заданного SQL-запроса
 * @param string $sql запрос
 * @return null array цифровой массив значений
 */
function getVals($sql)
{
  $res = queryDb($sql);
  $row = fetchRow($res);
  $res->close();
  return $row;
}

/**
 * Простая обертка для функции выполнения запроса
 * @param string $sql   строка запроса
 * @return bool|mysqli_result результат запроса
 */
function queryDb($sql)
{
  global $My_Db;
  return $My_Db->query($sql);
}

/**
 * Простая обертка для функции загрузки числового массива полей строки запроса
 * @param mysqli_result $res    результат query
 * @return mixed    числовой массив результата
 */
function fetchRow($res)
{
  return $res->fetch_row();
}

/**
 * Простая обертка для функции загрузки ассоциативного массива полей строки запроса
 * @param mysqli_result $res     результат query
 * @return mixed    ассоциативный массив результата
 */
function fetchAssoc($res)
{
  return $res->fetch_assoc();
}

/**
 * Простая обертка для функции загрузки числового и ассоциативного массива полей строки запроса
 * @param mysqli_result $res  результат query
 * @return mixed  числовой и ассоцитивный массив строки
 */
function fetchArray($res)
{
  return $res->fetch_array();
}

/**
 * Выполнить SQL-запрос
 * @param string $sql  SQL-запрос
 * @return boolean|mixed результат выполнения оператора типа INSERT, DELETE, UPDATE
 */
function execSQL($sql)
{
  global $My_Db;
  $r = $My_Db->query($sql);
  return $r;
}

/**
 * Подготавливает оператор для выполнения подстановок в SQL запросе
 * @param string $sql  строка SQL запроса
 * @return mysqli_stmt подготовленный оператор
 */
function prepareSql($sql)
{
  global $My_Db;
  return $My_Db->prepare($sql);
}

/**
 * Преобразовывает символы кавычек и других символов входной строки в безопасные символы
 * @param string $str входная строка
 * @return string строка без кавычек
 */
function s2s($str)
{
  return htmlspecialchars($str, ENT_QUOTES);
}

/**
 * Проверить время активности пользователя (тайм-аут активности) сек
 * Если tmout равен нулю, то сбрасываем время метки в текущее без проверки.
 * @param int $tmout время допустимой неактивности, сек
 */
function test_timeout_user_actitiviti($tmout)
{
  // время ожидания активности пользователя
  $tsnow = date('U'); // текущее время
  $tsses = intval($_SESSION['cre_datatime_work_metka']);
  $_SESSION['cre_datatime_work_metka'] = $tsnow;
  if($tmout > 0 && $tsses > 0 && ($tsnow - $tsses) > $tmout) {
    unset($_SESSION['creUid']);
    $_SESSION["error_message"] = "<span style='color: blue'>Истекло время ожидания...</span>";
  }
}

/**
 * Вернуть числовое значение параметра из формы или сессионной переменной
 * и задать этот параметр в сессию,
 * а если параметр формы не задан, то прочитать этот параметр из сессии.
 * @param string $namePar  имя параметра
 * @return int числовое значение параметра
 */
function getIntPar($namePar)
{
  $par = 0;
  if(array_key_exists($namePar, $_REQUEST)) {
    // вызвали форму
    $par = $_REQUEST[$namePar];
    $_SESSION[$namePar] = $par;
  } else {
    // форму не вызывали, проверим сессионную переменную
    if(array_key_exists($namePar, $_SESSION))
      $par = $_SESSION[$namePar];
  }
  return intval($par);
}

/**
 * Сделать форму логина в зависимости от состояния лога
 * @param string $UrlGoto куда перейти после регистрации
 * @return string текст формы
 */
function makeFormLogin($UrlGoto = null)
{
  global $Uid;
  $s = 'вход';
  $t = '';
  if($Uid > 0) {
    $s = 'выход';
    $t = getVal("SELECT email FROM users WHERE uid=$Uid");
    $t = "title='$t'";
  }
  if(empty($UrlGoto)) $UrlGoto = $_SERVER['PHP_SELF'];
  $r = "<form method='post' action='../login.php' $t>" .
       "<input type='hidden' name='goto' value='$UrlGoto'>" .
       "<input type='submit' class='inputoutput' value='$s'>" .
       "</form>";
  return $r;
}

/**
 * Поставить признак "грязного долга", который надо пересчитать
 * @param int $flag признак 0-не пересчитывать, 1-пересчитывать, если аргумент не задан
 *                  вернуть текущее значение
 * @return int текущее значение притзнак грязного долга
 */
function dirtyDolg($flag = null)
{
  if(is_null($flag)) {
    if(!array_key_exists(DIRTYDOLG, $_SESSION)) {
      $_SESSION[DIRTYDOLG] = 1;
    }
  } else {
    $_SESSION[DIRTYDOLG] = $flag;
  }
  return $_SESSION[DIRTYDOLG];
}

/**
 * Вернуть значение долга или установить его
 * @param null|double $val установить значение долга или пусто, тогда вернуть значение долга
 * @return int сумма долга
 */
function Dolg($val = null)
{
  if(is_null($val)) {
    if(!array_key_exists(DOLG, $_SESSION)) {
      $_SESSION[DOLG] = 0;
    }
  } else {
    $_SESSION[DOLG] = $val;
  }
  return $_SESSION[DOLG];
}
