(function( $ ) {
  'use strict';


  /**
   * Dashboard: Highlight same values in the dashboard table
   */
  $('.settings_page_tmsm-aquatonic-course-booking-settings .table-dashboard td span').each(function( index ) {
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
   * Refresh counter
   */
  var refreshCounter = $('#refresh-counter');
  var remainingTimeMillisecond = refreshCounter.attr('data-time') * 1000; //multiply by 1000 because javascript timestamps are in ms
  var currentTime = new Date();
  var endDate = new Date(remainingTimeMillisecond);
  refreshCounter.countdown(endDate, {elapse: true}).on('update.countdown', function(event) {
    var $this = $(this).html(event.strftime(''
      + '<span>%M</span> min '
      + '<span>%S</span> sec'));
    if (event.elapsed) {
      console.log('elapsed');
      document.location.reload();
    }
  });

  /**
   * Dashboard: Highlight same values in the dashboard table
   */
  /*$('.settings_page_tmsm-aquatonic-course-booking-settings .tooltip-trigger').each(function( index ) {
    console.log('.tooltip-trigger');
    $( this ).mouseover(function() {
          $(this).next('.tooltip-content').addClass('tooltip-active');
    }).mouseout(function() {
        $(this).next('.tooltip-content').removeClass('tooltip-active');
    });
  });*/

})( jQuery );
