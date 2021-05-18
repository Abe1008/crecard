<?php
/**
 * Copyright (c) 2019. Aleksey Eremin
 * 08.02.2019
 * 01.04.2019
 * 06.12.2019 в конструкторе задаются таблица и каталог
 * 17.05.2021
 *
 */
/*
 * Работа с файлами вложения для приложений оправданий операторов
 * Дублирующие файлы выдаются с тем же индексом
 */

// имя таблицы БД для хранения информации о файлах приложений
define("FILETABLE", 'p_files');
define("FILES_DIR", 'files');

class Ifile
{
  private $filetable;   // имя таблицы БД для хранения информации о файлах
  private $files_dir;   // каталог для хранения файлов

  /**
   * Ifile constructor.
   * @param null|string $table     имя таблицы БД для хранения информации о файлах
   * @param null|string $directory каталог для хранения файлов
   */
  function __construct($table = null, $directory = null)
  {
    // имя таблицы БД для хранения информации о файлах
    $this->filetable = empty($table)? FILETABLE: $table;
    // каталог для хранения файлов
    $this->files_dir = empty($directory)? FILES_DIR: $directory;
  }

  // function __destruct()  { }

  /**
   * Добавить в файлы загруженный файл
   * @param   int $metka метка имени файла (код оператора)
   * @return  int код записи файла, 0 - не загружен
   */
  function addLoadFile($metka)
  {
    // параметры загруженного файла
    // http://php.net/manual/ru/features.file-upload.post-method.php
    // php.ini :
    // Maximum allowed size for uploaded files.
    // Максимальный разрешенный к загрузке размер файла
    // http://php.net/upload-max-filesize
    // upload_max_filesize = 5M
    $f_name = $_FILES['filename']['name'];      // имя файла
    $f_type = $_FILES['filename']['type'];      // тип файла
    $f_tmpn = $_FILES['filename']['tmp_name'];  // имя временного файла
    $f_size = $_FILES['filename']['size'];      // размер файла
    // обработаем файл, если он загружен
    if($f_size <=  0) die(" ?-Error-файл не загружен");
    // вычислим хэш и поищем похожий файл в БД
    $hash = $this->hashFile($f_tmpn);   // вычислим хэш нового файла
    $y_ifile = getVal("SELECT ifile FROM $this->filetable WHERE file_hash='$hash'"); // ищем
    if(!empty($y_ifile)) {
      // если нашли похожий по хэшу файл - возвращаем его индекс
      return $y_ifile;
    }
    // подготовим метку если она есть
    if(!empty($metka)) {
      $metka = '_' . $metka;
    } else {
      $metka = '';
    }
    // новое (цифровое) имя файла с таким же расширением
    $ext  = strtolower(pathinfo($f_name, PATHINFO_EXTENSION));  // расширение
    $newn = date('U') . rand(100, 999) . $metka . '.' . $ext;   // новое имя файла
    $newfn = $this->diskName($newn);                  // полное имя нового файла
    // переместим временный загруженный файл в нужное место
    // разрешим писать в каталог CentOs
    // chmod 777 files
    // chcon -R -h -t httpd_sys_script_rw_t /var/www/html/opdoc/files
    $b = move_uploaded_file($f_tmpn, $newfn);
    if(!$b) die(" ?-Error-файл не загружен");
    // вставим запись о файле
    $sql = "INSERT INTO $this->filetable (file_name,file_type,file_size,file_hash) VALUES (?,?,?,?)";
    $stmt = prepareSql($sql);
    $stmt->bind_param('ssis', $newn, $f_type, $f_size, $hash);
    if(! $stmt->execute()) {
      // удалим файл, если запись в БД неудачная
      unlink($newfn);
      die("Ошибка записи");
    }
    $ifile = $stmt->insert_id;  // индекс вставленной записи
    $stmt->close();             // close statement
    return $ifile;
  }

  /**
   * Удалить файл на диске и в таблице с указанным кодом
   * Если запись из-за связей не удалилась, то файл с диска не удаляем
   * @param int $ifile код записи файла
   */
  function deleteIfile($ifile)
  {
    $ifile = intval($ifile);
    $fnam = $this->getFullFileName($ifile);                     // полное имя файла
    $r = execSQL("DELETE FROM $this->filetable WHERE ifile=$ifile"); // удалим запись о файле
    if ($r) {
      // если в БД удалили запись, удаляем файл
      if (!empty($fnam))
        unlink($fnam);  // удалить файл документа
    }
  }

  /**
   * Выдает полное имя файла на диске по коду файла
   * @param int $ifile   код записи файла
   * @return string|null полное имя файла на диске
   */
  private function getFullFileName($ifile)
  {
    $fnam = getVal("SELECT file_name FROM $this->filetable WHERE ifile=$ifile");
    if(!empty($fnam)) {
      // полное имя файла для хранения на диске
      $fnam = $this->diskName($fnam);
    }
    return $fnam;
  }

  /**
   * Формирует полное имя файла для хранения на диске
   * @param string $filename  имя файла
   * @return string полное имя файла
   */
  private function  diskName($filename)
  {
    $str =  __DIR__ . DIRECTORY_SEPARATOR .
        $this->files_dir .
        DIRECTORY_SEPARATOR . $filename;
    return $str;
  }

  /**
   * Вычислить хэш файла
   * @param string $filename  имя файла
   * @return string хэш файла
   */
  private function hashFile($filename)
  {
    $str = hash_file('md5', $filename);
    return  $str;
  }

}
