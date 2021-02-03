(function( $ ) {
  'use strict';


  console.log('start');
  $('.settings_page_tmsm-aquatonic-course-booking-settings .table-dashboard td span').each(function( index ) {
    var lastClass = $(this).attr('class').split(' ').pop();
    console.log('lastClass init:'+lastClass);
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

})( jQuery );
