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
