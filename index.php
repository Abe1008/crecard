<?php
/**
 * (C) 2018. Aleksey Eremin
 * 04.09.18
 * 17.05.2021
 *
 * Created by PhpStorm.
 * User: ae
 * Date: 20.02.2018
 * Time: 16:09
 */
/*
 * Отображение и загрузка данных о платежах
 *
 */

require_once "common.php";
// открытая БД $My_Db
// переменные $creUid

printHeadPage("Данные о платежах");

$sTit = $PayOff == 0? "Расходы" : "Оплата";
$sGo  = $PayOff == 0? "оплата"  : "расходы";
$ipay = $PayOff == 0? 1: 0;

// сумма долга
if(dirtyDolg()) {
  require_once "calculation.php";
}
$dolg  = Dolg(); // долг
$sdolg = '';
if($dolg > 0) {
  $sdolg = "долг $dolg" ;
}
$ost = Ostatok();
$sost = "<tr><td class='txtdolg'>$sdolg</td><td class='txtostatok'>&nbsp;остаток $ost</td></tr>";

$dp = datePay();
$mp = minimalPay(); // минимальный платеж для безпроцентности
$sdapla = '';
if($mp > 0.005) {
  $sdapla = "<tr class='txtminpay'><td >платеж $dp</td><td>&nbsp;сумма $mp</td></tr>";
}

// дата новой записи
$datt = date("Y-m-d");

// вход-выход
$form_login = makeFormLogin($_SERVER['PHP_SELF']);  // форма авторизации

echo <<<_EOF

<table width="100%" border="0">
<tr>
<td width="20%" class="showdocnote"><b>$sTit</b></td>
<td class="showdocnote"><table border="0">$sost $sdapla</table></td>
<td width="15%" align="right"><a href="index.php?payoff=$ipay" class="gotodocnote">$sGo</a></td>
</tr>
</table>

  <div class="inputnew">
  <hr>
  <table><tr>
  <form action="paysave.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="newrecord" value="$Uid">
  <td><input type="date" name="f_dat"  value="$datt"></td>
  <td><input type="text" name="f_sm"   size=8 placeholder="сумма"></td>
  <td><input type="text" name="f_prim" size=8 placeholder="примечание"></td> 
  <input type="hidden" name="f_payoff" value="$PayOff"> 
  <td><input type="submit" value="добавить" class="info"></td>
  </form>
  </tr></table>
  </div>

<table width="100%" class="spis" border="1">
<thead class="thdr"><tr>
 <th width="18%">дата</th>
 <th width="16%">сумма</th>
 <th>примечание</th>
 <th width="4%">док.</th>
 <th width="4%">уд.</th>
</tr></thead>
<tbody class="hightlight">

_EOF;

$sql = "SELECT id,dat,sm,prim, f.file_name 
        FROM pays LEFT JOIN p_files as f ON (pays.ifile=f.ifile)
        WHERE uid=$Uid AND payoff=$PayOff 
        ORDER BY dat DESC, id DESC;";
$res = queryDb($sql); //
while (list($id,$dat,$sm,$prim,$fnam) = fetchRow($res)) {
  $dats = dat2str($dat);
  $sms  = sprintf('%.2f',$sm);
  $ff = '';
  $fr = '';
  // есть имя документа
  // запись "можно редактировать" или нельзя?
  //$cledt = '';
  // если "можно редактировать" запись.
  $cledt = 'class="edt"';
  if($fnam) {
    // есть имя документа, его можно открыть
    $ff = "<a href='files/$fnam' target='_blank' class='nounder' title='открыть документ'>" .
        "<img src='img/doc_open.png' alt='открыть документ'></a>";
    // есть документ - можно удалить документ
    $fr = "<a href='paysave.php?delDoc=$id' onclick='return confirm(\"Удалить документ?\")' title='удалить документ'><img src='img/doc_del.png' alt='удалить'></a>";
  } else {
    // нет имени документа - можно добавить документ или удалить запись
    // сделаем класс fileupload ! https://github.com/blueimp/jQuery-File-Upload/wiki/Multiple-File-Upload-Widgets-on-the-same-page
    $ff = "<div class='file-upload'><label title='добавить документ'>" .
          "<input class='fileupload' type='file' name='filename' data-url='paysave.php?addDoc=$id'>" .
          "<span>+</span>" .
          "</label></div>";
    // редактирование - удалить строку
    $fr = "<a href='paysave.php?delRec=$id' onclick='return confirm(\"Удалить запись?\")' title='удалить запись'><img src='img/rec_del.png' alt='удалить запись'></a>";
  }

  echo "<tr>";
  echo "<td $cledt id='D$id'>$dats</td>";                   // D дата
  echo "<td $cledt id='S$id' align='right'>$sms</td>";      // S сумма
  echo "<td $cledt id='P$id'>$prim</td>";                   // P примечание
  echo "<td align='center'>$ff</td>";
  echo "<td align='center'>$fr</td>";
  echo "</tr>\n";
}
$res->close();
echo "</tbody></table>\n";

// кнопка вход/выход
echo "<p> $form_login";

// кнопка скролирования вверх
// http://webmastermix.ru/raznoe/300-knopka-vverkh-dlya-saita.html
echo "<div id='toTop'>наверх</div>\n";

// подключим javascript для таблицы
//echo '<script type="text/javascript" language="javascript" src="zindex.js"></script>';

// подключим редактирование полей
echo <<<_EOF
<!-- программа  -->
<script type="text/javascript" language="javascript">
$(document).ready(function(){
  // подключим редактирование "в таблице на месте"
  $('.edt').editable('paysave.php', {
    placeholder: '',
    cssclass: 'myedt'    
  });
  // подключим добавление документов
  $('.fileupload').fileupload()
      .bind('fileuploaddone', function(e,data) {window.location.reload(true);});
});
</script>

_EOF;

printEndPage();
