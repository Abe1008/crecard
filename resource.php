<?php
/**
 * Copyright (c) 2021. Alexey Eremin
 * 27.05.21 9:09
 */

/**
 * Created by PhpStorm.
 * User: ae
 * Date: 27.05.2021
 * Time: 9:09
 */
/*
 * Ресурсный файл
 * строки для писем и т.д.
 */

define('SUBJECTREGISTRATION', 'Code for Registration of CRECARD');
define('SUBJECTRENEWPWD',     'Code for Renew password of CRECARD');

/**
 * Текст письма о верификации электронной почты
 * @param string $code код верификации, вставляемый в письмо
 * @return string текст
 */
function MsgVerification($code)
{
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
    vertical-align: center;
    padding: 10px; margin: 10px;
  }
</style>
<body>
<TABLE>
 <TR>
  <TD>
   <p>Verification code</p>
   <p>Код подтверждения</p>
  </TD>
   <TD WIDTH="20%" class="a1">$code</TD></TR>
 <TR>
</TABLE>
</body>
</html>

_EOF;
  return $str;
}
