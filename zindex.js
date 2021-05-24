/*
 * Copyright (c) 2018. Aleksey Eremin
 * 17.05.2021
 */

/*
 * для index.php
 *
 */
// подключение dataTables
//
// http://qaru.site/questions/tagged/datatables .
$(document).ready(function(){
    // $('#myTable').DataTable( {
    //   //  scrollY:        "85vh",
    //   //  scrollCollapse: true,
    //   info:        true,
    //   paging:      false,
    //   // размещение элемнтов https://datatables.net/reference/option/dom
    //   dom: 'ift',   // https://stackoverflow.com/questions/8355638/datatables-place-search-and-entries-filter-under-the-table
    //   // https://datatables.net/reference/option/language
    //   language: {
    //     search:       "поиск:",
    //     zeroRecords:  "нет совпадающих записей",
    //     info:         "записей _TOTAL_",
    //     infoEmpty:    "совпадений 0",
    //     infoFiltered: "(всего _MAX_)"
    //   },
    //   // не сортировать и не искать где столбцы с классом 'nosort'
    //   // https://datatables.net/reference/option/columnDefs.targets .
    //   columnDefs: [
    //     { targets: "nosort", orderable:  false },
    //     { targets: "nosort", searchable: false }
    //   ]
    // } );

    // автоматизация кнопки"наверх"
    // отображение кнопки
    $(window).scroll(function() {
      if($(this).scrollTop() > 120) {
        $('#toTop').fadeIn();
      } else {
        $('#toTop').fadeOut();
      }
    });
    // анимация - движение вверх
    $('#toTop').click(function() {
      $('body,html').animate({scrollTop:0},900);
    });

});

/**
 * открыть окно документа
 * @param id ид. оператора
 * @returns {boolean}
 */
function opD(id)
{
  var url="opdocs.php?op_id=" + id;
  window.open(url,"_self");
  // window.location.href = url;
  //
  return false;
}

