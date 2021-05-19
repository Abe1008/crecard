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

printHeadPageEdt("Данные о платежах 1");

$sTit = $PayOff == 0? "Покупки": "Оплата";
$sGo  = $PayOff == 0? "оплаты": "покупки";
$ipay = $PayOff == 0? 1: 0;

// сумма долга
if(dirtyDolg()) {
  require_once "calculation.php";
}
$dolg  = Dolg(); // долг
$spodp = ($dolg > 0)? '<span class="txtred">долг ': '<span class="txtgrn">переплата ';
$sdolg = $spodp . sprintf("%.2f", abs($dolg)) . '</span> ' .
         '&nbsp; остаток ' . sprintf("%.2f", Ostatok());
// дата новой записи
$datt = date("Y-m-d");

// вход-выход
$form_login = makeFormLogin($_SERVER['PHP_SELF']);  // форма авторизации

echo <<<_EOF

<table width="100%" border="0">
<tr>
<td width="30%" class="showdocnote"><b>$sTit</b></td>
<td class="showdocnote" align="right"><b>$sdolg</b></td>
<td width="9%" align="right">$form_login</td>
<td width="20%" align="right"><a href="index.php?payoff=$ipay" class="gotodocnote">$sGo</a></td>
</tr>
</table>

  <div class="inputnewexcuse">
  <hr>
  <table><tr>
  <form  action="paysave.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="newrecord" value="$Uid">
  <td><input type="date" name="f_dat"  value="$datt"></td>
  <td><input type="text" name="f_sm"   placeholder="сумма" size="12" class="newtxt"></td>
  <td><input type="text" name="f_prim" placeholder="примечание" size="64"></td> 
    <input type="hidden" size="20" name="f_payoff" value="$PayOff"> 
    <!-- <td><input type="file" name="filename"></td>  -->
  <td><input type="submit" value="новый платеж" class="info"></td>
  </form>
  </tr></table>
  </div>

<table width="100%" class="spis" border="1">
<thead class="thdr"><tr>
 <th width="6%">дата</th>
 <th width="15%">сумма</th>
 <th>примечание</th>
 <th width="4%">док.</th>
 <th width="18px">уд.</th>
</tr></thead>
<tbody class="hightlight">

_EOF;

$sql = "SELECT id,dat,sm,prim,ost, f.file_name 
        FROM pays LEFT JOIN p_files as f ON (pays.ifile=f.ifile)
        WHERE uid=$Uid AND payoff=$PayOff 
        ORDER BY dat,id;";
$res = queryDb($sql); //
while (list($id,$dat,$sm,$prim,$ost, $fnam) = fetchRow($res)) {
  $dats = dat2str($dat);
  $sms  = sprintf('%.2f',$sm);
  $ff = '';
  $fr = '';
  // есть имя документа
  // запись "можно редактировать" или нельзя?
  $cledt = '';
  $cledtsel = '';
  // если "можно редактировать" запись.
  $cledt = 'class="edt"';
  $cledtsel = 'class="edtsel"';
  if($fnam) {
    // есть имя документа, его можно открыть
    $ff = "<a href='files/$fnam' target='_blank' class='nounderline' title='открыть документ'>".
        "<img src='img/doc_open.png' alt='открыть документ'></a>";
    // есть документ  - можно удалить документ
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
  echo "<td $cledt id='D$id'>$dats</td>";  // D дата
  echo "<td $cledt id='S$id' align='right'>$sms</td>";                // S сумма
  echo "<td $cledt id='P$id'>$prim</td>";                   // P примечание
  echo "<td align='center'>$ff</td>";
  echo "<td align='center'>$fr</td>";
  echo "</tr>\n";
}
// $res->close();
echo "</tbody></table>\n";

// кнопка скролирования вверх
// http://webmastermix.ru/raznoe/300-knopka-vverkh-dlya-saita.html
echo "<div id='toTop'>наверх</div>\n";

// подключим javascript для таблицы
echo '<script type="text/javascript" language="javascript" src="zindex.js"></script>';

// TODO сейчас не надо
//$jsonTip = makeTipSelectJson();

// подключим редактирование полей
echo <<<_EOF
<!-- программа  -->
<script type="text/javascript" language="javascript">
$(document).ready(function(){
  // подключим редактирование "в таблице на месте"
  $('td.edt').editable('paysave.php', {
    placeholder: '...'
  });
  // подключим добавление документов
  $('.fileupload').fileupload()
      .bind('fileuploaddone', function(e,data) {window.location.reload(true);});
});
</script>

_EOF;
printEndPage();
