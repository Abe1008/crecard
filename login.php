<?php
/**
 * (C) 2018. Aleksey Eremin
 * 04.09.18 23:08
 *
 * Created by PhpStorm.
 * User: ae
 * Date: 27.02.2018
 * Time: 14:36
 */
/*
 * Авторизация пользователя в системе
 * аргумент goto - куда переходить после успешной регистрации
 * cmd - внутренний аргумент, определяющий логику обработки формы
 * error_message - надпись, выводимая перед формой login
 *
 */
require_once "funcs.php";
require_once "MyDB.php";
// объект базы данных
$My_Db = new MyDB() ;
session_start();
uID(0); // в начале - точно не зарегистрирован
dirtyDolg(1);

$title ="Авторизация пользователя";

$self = $_SERVER['PHP_SELF'];

$error_message = '';
if(array_key_exists(ERRORMESSAGE, $_SESSION)) {
  $error_message = $_SESSION[ERRORMESSAGE]; // текст сообщения об ошибке
}
unset($_SESSION[ERRORMESSAGE]);

$goto = $_REQUEST['goto'];  // куда переходить после успешной авторизации
// был аргумент goto - куда переходить?
if(empty($goto)) {
  $i = strrpos($self, '/');
  $goto = substr($self,0,$i);  // корневой каталог задачи
}

$cmd = intval($_REQUEST['cmd']); // код команды
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
  <h3>Авторизация</h3>
  <form action='$self' method='POST'><br>
  <table border="0">
  <tr><td>E-mail:</td><td><input type='text' name='new_user'></td></tr>
  <tr><td>Пароль:</td><td><input type='password'   name='new_pass'></td></tr>
  <input type='hidden' name='cmd' value="101">
  <input type='hidden' name='goto' value="$goto">
  <tr><td></td><td align="right"><input type='submit' value='войти'></td></tr>
  </table>  
  </form>
  
  <p> <a href="registr.php" class="nounder">Регистрация нового пользователя</a> </p>
  <p> <a href="renewpwd.php" class="nounder">Восстановить пароль</a> </p>
  <p> <a href="$goto" class="nounder">продолжить без авторизации</a> </p>
_EOF;
  printEndPage();
  exit();
}

// человеческий ввод данных формы, в пароле не должно быть апострофов.
if($cmd == 101) {
  // обработка ввода данных формы
  malogin($_REQUEST['new_user'], $_REQUEST['new_pass'], $goto, $self);
  exit();
}

echo "НЕИЗВЕСТНОЕ СОСТОЯНИЕ";

/**
 * Авторизация по имени пользователя и паролю
 * @param string $user  имя пользователя
 * @param string $pass  пароль пользователя (заранее обработанный насчет апосторофов)
 * @param string $goto  адрес, куда переходить
 * @param string $self  собственный адрес
 */
function malogin($user, $pass, $goto, $self)
{
  // обработка ввода данных формы
  $u = str_replace("'", "", strtolower($user));   // из имени уберем апострофы и в нижний регистр
  $p = str_replace("'", "", $pass);   // из пароля уберем апостофы
  $sql = "SELECT uid FROM users WHERE email='$u' AND pwd='$p';";
  $ui = intval(getVal($sql));
  if($ui) {
    // авторизация выполнена
    uID($ui);
    // запротоколируем вход
    $uip = s2s($_SERVER['REMOTE_ADDR']);
    $ugt = s2s($goto);
    execSQL("INSERT INTO logs(dat,uid,ip,url) VALUES(NOW(), $ui, '$uip', '$ugt')");
    //
    unset($_SESSION['badslogon']);
    // сбросим время отсчета бездействия на текущее
    test_timeout_user_actitiviti(0);
    //переводим на список операторов
    gotoLocation($goto);
  } else {
    // авторизация не прошла
    sleep(1);
    $_SESSION["cre_error_message"] = "<span style='color: red'>Неверное имя или пароль!</span>";
    //переводим на себя
    unset($_POST['cmd']);
    unset($_GET['cmd']);
    unset($_SESSION['creUid']);
    // считаем ошибки
    $badsLogon = intval($_SESSION['badslogon']); // кол-во неудачных попытотк
    if(++$badsLogon > 3) {
      // Если ошибок больше 3, то застынем на минуту
      sleep(60);
    }
    $_SESSION['badslogon'] = $badsLogon;
    //
    gotoLocation($self); // . "?cmd=0"
  }
}
