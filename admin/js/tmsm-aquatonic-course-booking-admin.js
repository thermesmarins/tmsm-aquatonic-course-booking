(function( $ ) {
  'use strict';

  $('.settings_page_tmsm-aquatonic-course-booking-settings .button.cancelled').on( 'click', function( e ) {
    e.preventDefault();
    if( window.confirm( TmsmAquatonicCourseAdmin.i18n.surecancel )){
      var url = $(this).attr('href');
      console.log(url);
      window.location.href = url;
    }
  });

  /**
   * Dashboard: Highlight same values in the dashboard table
   */
  $('.settings_page_tmsm-aquatonic-course-booking-settings .table-dashboard td span:not(.onlyadmin)').each(function( index ) {
    var lastClass = $(this).attr('class').split(' ').pop();
    $( this ).mouseover(function() {
      if(lastClass !== ''){
        $('.'+lastClass).each(function( index ) {
          $(this).addClass('highlight');
        });

      }
    }).mouseout(function() {
      $('.'+lastClass).each(function( index ) {
        $(this).removeClass('highlight');
      });
    });
  });

  /*
   * Dashboard: Refresh counter
   */
  var refreshCounter = $('#refresh-counter');
  var remainingTimeMillisecond = refreshCounter.attr('data-time') * 1000; //multiply by 1000 because javascript timestamps are in ms
  var currentTime = new Date();
  var endDate = new Date(remainingTimeMillisecond);
  var executeAfterElapsed = false;
  refreshCounter.countdown(endDate, {elapse: true}).on('update.countdown', function(event) {
    var $this = $(this).html(event.strftime(''
      + '<span>%M</span> min '
      + '<span>%S</span> sec'));
    if (event.elapsed && executeAfterElapsed === false) {
      executeAfterElapsed = true;
      document.location.reload();
    }
  });

  /*
   * History: Save History XLS
   */
  $("#tmsm-aquatonic-course-booking-history-savexls").click(function(){
    console.log('button save-xls');
    var wb = XLSX.utils.table_to_book(document.getElementById('tmsm-aquatonic-course-booking-history'));
    XLSX.writeFile(wb, "export.xlsx");
  });


})( jQuery );
