<?php
/**
 * Copyright (c) 2021. Alexey Eremin
 * 27.05.21 10:21
 */

/**
 * Created by PhpStorm.
 * User: ae
 * Date: 27.05.2021
 * Time: 10:21
 */
/*
 *  Восстановление пароля
 */

// код верификации данных регистрации
define('VERIFY', '6348955746');
// имя сессионной переменной для хранения промежуточных данных
define('DATANAME', 'RenewData');

require_once "common.php";
require_once "resource.php";

if($Uid > 0) {
  die("Вы зарегистрированы");
}

$title ="Восстановление пароля пользователя";

$self = $_SERVER['PHP_SELF'];

// сообщение об ошибке
$error_message = errorMessage();
errorMessage(''); // сбросим сообщение об ошибке

$goto = $_REQUEST['goto'];  // куда переходить после успешной обработки
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

// ссылка на себя
$repeat = "<a href='$self'>повторить</a>";

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
  <h3>Восстановление пароля</h3>
  <p>Введите электронный адрес, указанный при регистрации</p>
  <form action='$self' method='POST'><br>
  <table border="0">
  <tr><td>E-mail:</td><td><input type='text'            name='new_usr'></td></tr>
  <input type='hidden' name='cmd' value="102">
  <input type='hidden' name='goto' value="$goto">
  <tr><td></td><td align="right"><input type='submit' value='продолжить'></td></tr>
  </table>  
  </form>
  
  <p> <a href="$goto" class="gotopg">продолжить без регистрации</a> </p>
_EOF;
  printEndPage();
  exit();
}

// второй этап - подтверждение почты
if($cmd == 102) {
  sleep(2);

  printHeadPage("Восстановление пароля");

  // обработка ввода данных формы
  $usr   = str_replace("'", '', strtolower($_REQUEST['new_usr']));

  if(empty($usr)) {
    die("Введены некорректные данные. $repeat</body></html>");
  }

  // проверим имя на дублирование
  list($cnt,$uid) = getVals("SELECT COUNT(*), MAX(uid) FROM users WHERE email='$usr';");
  if(intval($cnt) < 1 || empty($uid)) {
    die("В системе нет указанной электронной почты: $usr. $repeat</body></html>");
  }

  // код подтверждения
  $trustcode = makeCode();
  // регистрационные данные
  $renewdata = array(
      'usr'   => $usr,
      'uid'   => intval($uid),
      'trustcode' => $trustcode,
      'verify' => VERIFY
  );
  // запомним в сессионной переменной данные регистрации
  sessionVal(DATANAME, $renewdata, $renewdata);

  // отправить проверочный код по электронной почте
  sendCode($usr, $trustcode);

  sleep(5);

  $sbj = SUBJECTRENEWPWD; // Текст тема письма

  echo <<<_EOF
<table>
<tr>
<td>Вы указали следующие данные:</td>  
</tr>
<tr><td>E-mail:</td><td>$usr</td></tr>
</table>

<p>На электронную почту выслан проверочный код.<br> 
 Тема письма "$sbj". 
 <br><small>Если письма не видно, то проверьте папку "Спам".</small></p>
 
 <p>Введите новый пароль и код из письма</p>

<form action='$self' method='POST'><br>
  <table border="0">
  <tr><td>Новый пароль:</td><td><input type='text' name='new_pwd1'></td></tr>
  <tr><td>еще раз Новый пароль:</td><td><input type='text' name='new_pwd2'></td></tr>
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
  // пароли
  $new_pwd1 = $_REQUEST['new_pwd1'];
  $new_pwd2 = $_REQUEST['new_pwd2'];

  // данные регистрации
  $renewdata = sessionVal(DATANAME, 0);
  // код верификации данных
  $verify = intval($renewdata['verify']);
  // код подтверждения
  $trustcode = intval($renewdata['trustcode']);
  // код пользователя
  $uid = intval($renewdata['uid']);

  printHeadPage("Подтверждение регистрации");

  // mсверим коды
  if(VERIFY != $verify || $new_trustcode != $trustcode || $uid < 1 || strcmp($new_pwd1,$new_pwd2) != 0) {
    die("<h4>Неправильные данные</h4> $repeat</body></html>");
  }

  // обработка данных
  $pwd   = str_replace("'", '', $new_pwd1);
  // обновить пароль
  $sql = "UPDATE users SET pwd='$pwd' WHERE uid=$uid;";
  $a = execSQL($sql);
  if($a == 1) {
    $usr = $renewdata['usr'];
    echo <<<_EOF
      <h4>Новый пароль установлен</h4>
      <p>Для пользователя $usr установлен новый пароль</p>
      <p><a href="login.php">Зарегистрируйтесь</a> в системе, используя 
      свои учетные данные: электронную почту и пароль</p>
_EOF;
  } else {
    echo "<p>Ошибка записи нового пароля</p>";
  }

  printEndPage();
  exit();
}

echo "НЕИЗВЕСТНОЕ СОСТОЯНИЕ";

/**
 * Отправить письмо с проверочным кодом
 * @param string $email   электронный адрес
 * @param int    $code    прповерочный код
 */
function sendCode($email, $code)
{
  //echo "$email - $code";
  $head = "MIME-Version: 1.0\r\n";
  $head .= "Content-type: text/html; charset=UTF-8\r\n";
  $head .= 'From: <crecard>' . "\r\n";
  mail($email, SUBJECTRENEWPWD, MsgVerification($code), $head);
}
