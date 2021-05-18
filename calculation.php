<?php
/**
 * Copyright (c) 2021. Alexey Eremin
 * 17.05.21 17:02
 */

/**
 * Created by PhpStorm.
 * User: ae
 * Date: 17.05.2021
 * Time: 17:02
 */

/*
 * Расчет долга и даты платежа
 */

require_once "common.php";

$s = 0;
// покупки
$sql = "SELECT id,dat,sm,ost FROM pays 
        WHERE uid=$Uid AND payoff = 0 
        ORDER BY dat,id";
$res = queryDb($sql); //
while (list($id,$dat,$sm,$ost) = fetchRow($res)) {
  $s = $s + $sm;
  echo "$id $s<br>";
}

// покупки
$sql = "SELECT id,dat,sm,ost FROM pays 
        WHERE uid=$Uid AND payoff = 1 
        ORDER BY dat,id";
$res = queryDb($sql); //
while (list($id,$dat,$sm,$ost) = fetchRow($res)) {
  $s = $s - $sm;
  echo "$id $s<br>";
}

// выставим флаги, суммы
dirtyDolg(0);
Dolg($s);
