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
// имя сессионной переменной для хранения промежуточных данных
define('DATANAME', 'RenewData');

require_once "common.php";
require_once "resource.php";

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
  <h3>Регистрация нового пользователя</h3>
  <form action='$self' method='POST'><br>
  <table border="0">
  <tr><td>E-mail:</td><td><input type='text'            name='new_usr'></td></tr>
  <tr><td>Пароль:</td><td><input type='password'        name='new_pwd'></td></tr>
  <tr><td>Лимит:</td><td><input type='number'           name='new_lim' value="65000"></td></tr>
  <tr><td>расчетный день:</td><td><input type='number'  name='new_rday' value="30"></td></tr>
  <tr><td>грэйс период:</td><td><input type='number'    name='new_grace' value="25"></td></tr>
  <input type='hidden' name='cmd' value="102">
  <input type='hidden' name='goto' value="$goto">
  <tr><td></td><td align="right"><input type='submit' value='регистрация'></td></tr>
  </table>  
  </form>
  
  <p> <a href="$goto" class="nounder">продолжить без регистрации</a> </p>
_EOF;
  printEndPage();
  exit();
}

// второй этап - подтверждение почты
if($cmd == 102) {
  sleep(2);

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
  if(intval($cnt) > 0) {
    die("В системе уже зарегистрирован пользователь: $usr. $repeat</body></html>");
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
  sessionVal(DATANAME, $regdata, $regdata);

  // отправить проверочный код по электронной почте
  sendCode($usr, $trustcode);

  sleep(4);

  $sbj = SUBJECTREGISTRATION; // Текст тема письма

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
 <p>Введите код из письма</p>

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
  $regdata = sessionVal(DATANAME, 0);
  // код верификации данных
  $verify = intval($regdata['verify']);
  // код подтверждения
  $trustcode = intval($regdata['trustcode']);

  printHeadPage("Подтверждение регистрации");

  // mсверим коды
  if(VERIFY != $verify || $new_trustcode != $trustcode) {
    die("<h4>Неправильные данные</h4> $repeat</body></html>");
  }

  // обработка данных
  $usr   = str_replace("'", '', strtolower($regdata['usr']));
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
    echo "<p>Ошибка записи нового пользователя. $repeat</p>";
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
  mail($email, SUBJECTREGISTRATION, MsgVerification($code), $head);
}
