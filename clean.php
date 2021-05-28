<?php
/**
 * Copyright (c) 2021. Alexey Eremin
 * 28.05.21 13:02
 */

/**
 * Created by PhpStorm.
 * User: ae
 * Date: 28.05.2021
 * Time: 13:02
 */
/*
 * Очистка БД
 * очищает старые записи безимянного пользователя Uid=0
 *
 */

require_once "common.php";

require_once "Ifile.php";
$MyIfile = new Ifile(); // общий объект

// очистим старые записи от безимянного пользователя
$sql = "DELETE FROM pays WHERE uid=0 AND ADDDATE(wdat, INTERVAL 3 DAY) < NOW()";
$a = execSQL($sql);
echo "удалено старых записей $a<br>";

// удалить ненужные файлы
$sql = "SELECT ifile FROM p_files WHERE ifile NOT IN (SELECT ifile FROM pays WHERE not ISNULL(ifile));";
$res = queryDb($sql); //
$cnt = 0;
while (list($ifile) = fetchRow($res)) {
  $i = intval($ifile);
  $MyIfile->deleteIfile($i);
  $cnt++;
}
echo "удалено ненужных файлов $cnt<br>";