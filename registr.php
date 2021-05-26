<?php
/**
 * Copyright (c) 2021. Alexey Eremin
 * 26.05.21 9:23
 */

/**
 * Created by PhpStorm.
 * User: ae
 * Date: 26.05.2021
 * Time: 9:23
 */
/*
 * Регистрация нового пользователя
 */

// код верификации данных регистрации
define('VERIFY', '7389451959');
define('SUBJECT', 'Registration code for CRECARD');

require_once "common.php";

if($Uid > 0) {
  die("Вы зарегистрированы");
}

$title ="Регистрация пользователя";

$self = $_SERVER['PHP_SELF'];

// сообщение об ошибке
$error_message = errorMessage();
errorMessage(''); // сбросим сообщение об ошибке

$goto = $_REQUEST['goto'];  // куда переходить после успешной регистрации
// был аргумент goto - куда переходить?
if(empty($goto)) {
  $i = strrpos($self, '/');
  $goto = substr($self,0,$i);  // корневой каталог задачи
}

// код команды
$cmd = 0;
if(array_key_exists('cmd', $_REQUEST)) {
  $cmd = intval($_REQUEST['cmd']); // код команды
}

// начальный этап - ввод данных
if($cmd == 0) {
  printHeadPage($title);
  // если была сессионая переменная error_message
  if(!empty($error_message)) {
    echo "<p>$error_message</p>";
  } else {
    unset($_SESSION['badslogon']);
  }
  $ipadr = $_SERVER['REMOTE_ADDR'];
  // вывод формы
  echo <<<_EOF
  <h3>Регистрация</h3>
  <form action='$self' method='POST'><br>
  <table border="0">
  <tr><td>E-mail:</td><td><input type='text'            name='new_usr'></td></tr>
  <tr><td>Пароль:</td><td><input type='password'        name='new_pwd'></td></tr>
  <tr><td>Лимит:</td><td><input type='number'           name='new_lim' value="65000"></td></tr>
  <tr><td>расчетный день:</td><td><input type='number'  name='new_rday' value="1"></td></tr>
  <tr><td>грэйс период:</td><td><input type='number'    name='new_grace' value="25"></td></tr>
  <input type='hidden' name='cmd' value="102">
  <input type='hidden' name='goto' value="$goto">
  <tr><td></td><td align="right"><input type='submit' value='регистрация'></td></tr>
  </table>  
  </form>
  
  <p> <a href="$goto" class="inputoutput">продолжить без регистрации</a> </p>
_EOF;
  printEndPage();
  exit();
}

// второй этап - подтверждение почты
if($cmd == 102) {
  printHeadPage("Подтвердите электронную почту");

  // обработка ввода данных формы
  $usr   = str_replace("'", '', $_REQUEST['new_usr']);
  $pwd   = $_REQUEST['new_pwd'];
  $lim   = $_REQUEST['new_lim'];
  $rday  = $_REQUEST['new_rday'];
  $grace = $_REQUEST['new_grace'];

  if(empty($usr) || empty($pwd) || empty($lim) || empty($rday) || empty($grace)) {
    die("Введены некорректные данные. Повторите <a href='$self'>ввод</a></body></html>");
  }

  // проверим имя на дублирование
  $cnt = getVal("SELECT COUNT(*) FROM users WHERE email='$usr';");
  if($cnt > 0) {
    die("В системе уже зарегистрирован пользователь: $usr</body></html>");
  }

  // код подтверждения
  $trustcode = makeCode();
  // регистрационные данные
  $regdata = array(
      'usr'   => $usr,
      'pwd'   => $pwd,
      'lim'   => $lim,
      'rday'  => $rday,
      'grace' => $grace,
      'trustcode' => $trustcode,
      'verify' => VERIFY
  );
  // запомним в сессионной переменной данные регистрации
  sessionVal('RegData', $regdata, $regdata);

  // отправить проверочный код по электронной почте
  sendCode($usr, $trustcode);

  $sbj = SUBJECT; // Текст тема письма

  echo <<<_EOF
<table>
<tr>
<td>Вы указали следующие данные:</td>  
</tr>
<tr><td>E-mail:</td><td>$usr</td></tr>
<tr><td>Пароль:</td><td>***</td></tr>
<tr><td>Лимит:</td> <td>$lim</td></tr>
<tr><td>расчетный день:</td><td>$rday</td></tr>
<tr><td>грэйс период:</td><td>$grace</td></tr>
</table>

<p>На электронную почту выслан проверочный код.<br> 
 Тема письма "$sbj". 
 <br><small>Если письма не видно, то проверьте папку "Спам".</small></p>

<form action='$self' method='POST'><br>
  <table border="0">
  <tr><td>код из письма:</td><td><input type='number' name='new_trustcode'></td></tr>  
  <input type='hidden' name='cmd' value="103">
  <input type='hidden' name='goto' value="$goto">
  <tr><td></td><td align="right"><input type='submit' value='подтвердить'></td></tr>
  </table>  
</form>
  
_EOF;
  printEndPage();
  exit();
}

// третий этап - подтверждение почты
if($cmd == 103) {
  // введенный код подтверждения
  $new_trustcode = intval($_REQUEST['new_trustcode']);

  // данные регистрации
  $regdata = sessionVal('RegData', 0);
  // код верификации данных
  $verify = intval($regdata['verify']);
  // код подтверждения
  $trustcode = intval($regdata['trustcode']);

  printHeadPage("Подтверждение регистрации");

  // mсверим коды
  if(VERIFY != $verify || $new_trustcode != $trustcode) {
    die("<h3>Неправильные данные</h3><a href='$self'>повторить</a></body></html>");
  }

  // обработка данных
  $usr   = str_replace("'", '', $regdata['usr']);
  $pwd   = str_replace("'", '', $regdata['pwd']);
  $lim   = intval($regdata['lim']);
  $rday  = intval($regdata['rday']);
  $grace = intval($regdata['grace']);
  // записать регистрационные данные
  $sql = "INSERT INTO users(email,pwd,lim,rday,grace) VALUES('$usr', '$pwd', $lim, $rday, $grace);";
  $a = execSQL($sql);
  if($a == 1) {
    echo <<<_EOF
      <h4>Данные регистрации подтверждены</h4>
      <p>Новый пользователь $usr записан в систему</p>
      <p><a href="login.php">Зарегистрируйтесь в системе, используя свои учетные данные -
      электронную почту и пароль</a></p>
_EOF;
  } else {
    echo "<p>Ошибка записи нового пользователя</p>";
  }

  printEndPage();
  exit();
}

echo "НЕИЗВЕСТНОЕ СОСТОЯНИЕ";

/**
 * @return string проверочный код, случайное число
 */
function makeCode()
{
  $d = date('gs');
  $r = rand(1001,9999);
  return $d . $r;
}


/**
 * Отправить письмо с проверочным кодом
 * @param string $email   электронный адрес
 * @param int    $code    прповерочный код
 */
function sendCode($email, $code)
{
  //echo "$email - $code";
  $str = <<<_EOF
  <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verification code</title>
</head>
<style>
  .a1 {
    font-family: Arial,serif; 
    font-weight: bold;
    background: #f8f888; 
    text-align: center;
    padding: 10px; margin: 10px;
  }
</style>
<body>
<TABLE>
  <TR><TD>Verification code for registration</TD>
    <TD ROWSPAN="2" WIDTH="20%" class="a1">$code</TD></TR>
  <TR><TD>Код подтверждения для регистрации</TD></TR>
</TABLE>
</body>
</html>

_EOF;
  $head[] = 'MIME-Version: 1.0';
  $head[] = 'Content-type: text/html; charset=UTF-8';
  //$head[] = 'To: <'. $email . '>';
  $head[] = 'From: <crecard>';
  //$str = "Verification code for registration: $code";
  $hdr = implode("\r\n", $head);
  mail($email, SUBJECT, $str, $hdr);
}