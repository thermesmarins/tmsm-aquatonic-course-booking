(function( $ ) {
	'use strict';



  var wp = window.wp || {};



})( jQuery );


var TmsmAquatonicCourseApp = TmsmAquatonicCourseApp || {};

(function ($, TmsmAquatonicCourse) {
  'use strict';

  /**
   * A mixin for collections/models.
   * @see http://taylorlovett.com/2014/09/28/syncing-backbone-models-and-collections-to-admin-ajax-php/
   * @see https://deliciousbrains.com/building-reactive-wordpress-plugins-part-1-backbone-js/
   * @see https://www.synbioz.com/blog/tech/debuter-avec-backbonejs
   */
  var TmsmAquatonicCourseAdminAjaxSyncableMixin = {
    url: TmsmAquatonicCourseApp.data.ajaxurl,
    action: 'tmsm-aquatonic-course-booking-weekday',

    sync: function( method, object, options ) {

      if ( typeof options.data === 'undefined' ) {
        options.data = {};
      }

      options.data.nonce = TmsmAquatonicCourseApp.data.nonce; // From localized script.
      options.data.action_type = method;



      // If no action defined, set default.
      if ( undefined === options.data.action && undefined !== this.action ) {
        options.data.action = this.action;
      }

      //console.log('sync action: '+options.data.action);
      //console.log('sync options: ');
      //console.log(options);


      return Backbone.sync( method, object, options );

      // Reads work just fine.
      /*if ( 'read' === method ) {
        return Backbone.sync( method, object, options );
      }

      var json = this.toJSON();
      var formattedJSON = {};

      if ( json instanceof Array ) {
        formattedJSON.models = json;
      } else {
        formattedJSON.model = json;
      }

      _.extend( options.data, formattedJSON );

      // Need to use "application/x-www-form-urlencoded" MIME type.
      options.emulateJSON = true;

      // Force a POST with "create" method if not a read, otherwise admin-ajax.php does nothing.
      return Backbone.sync.call( this, 'create', object, options );*/
    }
  };

  /**
   * A model for all your syncable models to extend.
   * Based on http://taylorlovett.com/2014/09/28/syncing-backbone-models-and-collections-to-admin-ajax-php/
   */
  var TmsmAquatonicCourseBaseModel = Backbone.Model.extend( _.defaults( {
    // parse: function( response ) {
    // Implement me depending on your response from admin-ajax.php!
    // return response;
    // }
  }, TmsmAquatonicCourseAdminAjaxSyncableMixin ) );

  /**
   * A collection for all your syncable collections to extend.
   * Based on http://taylorlovett.com/2014/09/28/syncing-backbone-models-and-collections-to-admin-ajax-php/
   */
  var TmsmAquatonicCourseBaseCollection = Backbone.Collection.extend( _.defaults( {
    // parse: function( response ) {
    // 	Implement me depending on your response from admin-ajax.php!
    // return response;
    // }
  }, TmsmAquatonicCourseAdminAjaxSyncableMixin ) );


  /**
   * Time
   */
  TmsmAquatonicCourseApp.TimeModel = TmsmAquatonicCourseBaseModel.extend( {
    action: 'tmsm-aquatonic-course-booking-times',
    defaults: {
      date: null,
      hour: null,
      minute: null,
      priority: null,
      capacity: null,
      hourminutes: null,
      index: null,
    }
  } );


  TmsmAquatonicCourseApp.TimesCollection = TmsmAquatonicCourseBaseCollection.extend( {
    action: 'tmsm-aquatonic-course-booking-times',
    model: TmsmAquatonicCourseApp.TimeModel,

  } );

  TmsmAquatonicCourseApp.TimesListView = Backbone.View.extend( {
    el: '#tmsm-aquatonic-course-booking-times-container',
    selectedValue: null,
    listElement: '#tmsm-aquatonic-course-booking-times-list',
    loadingElement: '#tmsm-aquatonic-course-booking-times-loading',
    errorElement: '#tmsm-aquatonic-course-booking-times-error',
    anotherDateElement: '#tmsm-aquatonic-course-booking-times-anotherdate',
    selectButtons: '.tmsm-aquatonic-course-booking-time-button',
    cancelButtons: '.tmsm-aquatonic-course-booking-time-change-label',

    initialize: function() {

      console.log('TimesListView initialize');
      this.hide();
      this.listenTo( this.collection, 'sync', this.render );
    },

    events : {
      'click .tmsm-aquatonic-course-booking-time-button' : 'selectTime',
      'click .tmsm-aquatonic-course-booking-time-change-label' : 'cancelTime',
      'click #tmsm-aquatonic-course-booking-times-anotherdate' : 'changeDate',
      'click .previous': 'previous',
      'click .next': 'next',
    },

    loading: function(){
      console.log('TimesListView loading');
      $( this.errorElement ).hide();
      $( this.loadingElement ).show();
      $( this.listElement ).hide();
    },
    loaded: function(){
      console.log('TimesListView loaded');
      $( this.loadingElement ).hide();
      $( this.listElement ).show();
    },

    render: function() {
      console.log('TimesListView render');
      var $list = this.$( this.listElement ).empty().val('');

      $list.hide();

      console.log('TimesListView collection:');
      console.log(this.collection);
      console.log('TimesListView collection length: ' + this.collection.length);

      var i = 0;
      this.collection.each( function( model ) {
        i++;
        if(i===1){
          $( '.tmsm-aquatonic-course-booking-weekday-times[data-date="'+model.attributes.date+'"]').empty().append('<option>'+TmsmAquatonicCourseApp.i18n.pickatimeslot+'</option>');
        }
        var item = new TmsmAquatonicCourseApp.TimesListItemView( { model: model } );
        console.log('item render:');
        console.log(item.render());
        if ($('.tmsm-aquatonic-course-booking-weekday-times[data-date="' + model.attributes.date + '"]').length > 0) {
          //$( '.tmsm-aquatonic-course-booking-weekday-times[data-date="' + model.attributes.date + '"]').append(item.render().$el.context.outerHTML);
          $( '.tmsm-aquatonic-course-booking-weekday-times[data-date="' + model.attributes.date + '"]').append(item.render().el.outerHTML);
        }
        else{
          console.warn('tmsm-aquatonic-course-booking-weekday-times not added for '+model.attributes.date);
        }

        //$( '.tmsm-aquatonic-course-booking-weekday-times[data-date=\''+model.attributes.date+'\']').append(item.render().$el);

        $list.append( item.render().$el );

      }, this );

      this.loaded();

      if(this.collection.length === 0){
        $( this.errorElement ).show();
      }
      else{
        $( this.errorElement ).hide();
      }

      return this;
    },

    selectTime: function(event){
      event.preventDefault();
      console.log('TimeListView selectTime');
      this.selectedValue = $(event.target).data('hourminutes');
      console.log('TimeListView selectedValue: '+ this.selectedValue);
      $( this.selectButtons ).hide().removeClass('disabled').removeClass('selected').addClass('not-selected');
      $(event.target).show().addClass('selected').removeClass('not-selected').find('.tmsm-aquatonic-course-booking-time').addClass('disabled');

      TmsmAquatonicCourseApp.selectedData.set('hourminutes', this.selectedValue);
    },

    selectTimeWithSelect: function(event){
      console.log('selectTimeWithSelect');
    },
    cancelTime: function(event){
      event.preventDefault();
      $( this.selectButtons ).show().removeClass('disabled').removeClass('selected').addClass('not-selected');
      this.selectedValue = null;

      TmsmAquatonicCourseApp.selectedData.set('hourminutes', null);
    },

    changeDate: function(event){
      event.preventDefault();
      TmsmAquatonicCourseApp.dateList.reset();
      TmsmAquatonicCourseApp.timesList.reset();
      //TmsmAquatonicCourseApp.animateTransition(TmsmAquatonicCourseApp.dateList.element());
    },

    reset: function (){
      this.selectedValue = null;
      TmsmAquatonicCourseApp.selectedData.set('hourminutes', null);
      this.hide();
    },

    element: function (){
      return this.$el;
    },
    hide: function (){
      this.$el.hide();
    },
    show: function (){
      this.$el.show();
    }
  } );


  TmsmAquatonicCourseApp.TimesListItemView = Backbone.View.extend( {
    tagName: 'option',
    attributes: function() {
      TmsmAquatonicCourseApp.times_indexmax = Math.max(TmsmAquatonicCourseApp.times_indexmax, this.model.get('index'));
      return {
        'data-index': this.model.get('index'),
        'data-hourminutes': this.model.attributes.hourminutes,
        'data-date': this.model.attributes.date,
        'data-hour': this.model.attributes.hour,
        'data-minutes': this.model.attributes.minutes,
      }},
    className: 'tmsm-aquatonic-course-booking-time-item',
    template: wp.template( 'tmsm-aquatonic-course-booking-time' ),

    initialize: function() {
      this.listenTo( this.model, 'change', this.render );
      this.listenTo( this.model, 'destroy', this.remove );
    },

    render: function() {
      var html = this.template( this.model.toJSON() );
      this.$el.html( html );
      return this;
    },

  } );
  
  /**
   * WeekDay
   */
  TmsmAquatonicCourseApp.WeekDayModel = TmsmAquatonicCourseBaseModel.extend( {
    action: 'tmsm-aquatonic-course-booking-weekday',
    defaults: {
      date_label: null,
      date_computed: null,
    }
  } );

  TmsmAquatonicCourseApp.WeekDayCollection = TmsmAquatonicCourseBaseCollection.extend( {
    action: 'tmsm-aquatonic-course-booking-weekday',
    model: TmsmAquatonicCourseApp.WeekDayModel,

  } );

  TmsmAquatonicCourseApp.WeekDayListView = Backbone.View.extend( {
    el: '#tmsm-aquatonic-course-slots-container',
    listElement: '#tmsm-aquatonic-course-booking-weekdays-list',
    selectButtons: '.tmsm-aquatonic-course-booking-time-button',
    addAppointmentButton: '#tmsm-aquatonic-course-booking-confirm',
    daysPage: 1,
    timesPage: 1,
    timesPerPage: 10,

    templateHelpers: {
      moment: moment // <-- this is the reference to the moment in your view
    },

    initialize: function() {

      console.log('WeekDayListView initialize');

      moment.locale(TmsmAquatonicCourseApp.locale);
      console.log("moment", moment().format());
      console.log("moment locale: "+ moment.locale());

      console.log("moment fromnow: "+ moment().fromNow());
      this.listenTo( this.collection, 'sync', this.render );
    },

    events : {
      'click .tmsm-aquatonic-course-booking-time-button' : 'selectTime',
      'change .tmsm-aquatonic-course-booking-weekday-times' : 'selectTimeWithSelect',
      'click #tmsm-aquatonic-course-booking-weekdays-previous': 'previous_week',
      'click #tmsm-aquatonic-course-booking-weekdays-next': 'next_week',
      'click #tmsm-aquatonic-course-booking-next-times': 'next_times',
    },
    refreshNextTimesButton: function(event){

      return; // Disable the function


      var object = this;
      console.log('refreshNextTimesButton');
      console.log('TmsmAquatonicCourseApp.times_indexmax: ' + TmsmAquatonicCourseApp.times_indexmax);
      console.log('this.timesPerPage: ' + this.timesPerPage);
      console.log('this.timesPage: ' +  this.timesPage);

      // Hide or show next times button
      if( TmsmAquatonicCourseApp.times_indexmax >= (object.timesPerPage * object.timesPage)){
        console.log('show next_times button');
        $('#tmsm-aquatonic-course-booking-next-times').show();
      }
      else{
        console.log('hide next_times button');
        $('#tmsm-aquatonic-course-booking-next-times').hide();
      }

      console.log('hide all times where index is above: ' + (object.timesPerPage * object.timesPage));
      $('.tmsm-aquatonic-course-booking-weekday-times .tmsm-aquatonic-course-booking-time-item').filter(function() {
        console.log('index (' + $(this).attr("data-index") + ')');
        console.log('(this.timesPerPage * this.timesPage): '+ (object.timesPerPage * object.timesPage));
        return $(this).attr("data-index") <=  (object.timesPerPage * object.timesPage);
      }).show();

      console.log('show all times where index is below: ' + (object.timesPerPage * object.timesPage));
      $('.tmsm-aquatonic-course-booking-weekday-times .tmsm-aquatonic-course-booking-time-item').filter(function() {
        console.log('index (' + $(this).attr("data-index") + ')');
        console.log('(this.timesPerPage * this.timesPage): '+ (object.timesPerPage * object.timesPage));
        return $(this).attr("data-index") >  (object.timesPerPage * object.timesPage);
      }).hide();


    },
    previous_week: function(event){
      console.log('WeekDayListView previous_week');

      event.preventDefault();
      this.daysPage = this.daysPage - 1;
      this.timesPage = 1;
      $('#tmsm-aquatonic-course-booking-next-times').hide();

      this.render();
    },

    next_week: function(event){
      console.log('WeekDayListView next_week');

      event.preventDefault();
      this.daysPage = this.daysPage + 1;
      this.timesPage = 1;
      $('#tmsm-aquatonic-course-booking-next-times').hide();

      this.render();
    },

    next_times: function(event){
      console.log('WeekDayListView next_times');

      event.preventDefault();
      this.timesPage = this.timesPage + 1;

      this.refreshNextTimesButton();


    },

    render: function() {


      console.warn(' TmsmAquatonicCourseApp.form_fields.step:'+ TmsmAquatonicCourseApp.form_fields.step);
      if(TmsmAquatonicCourseApp.form_fields.step != 2){
        return;
      }
      var object = this;

      console.log('WeekDayListView render');
      var $list = this.$( this.listElement ).empty().val('');

      this.collection.reset();

      if(this.daysPage === 1){
        $('#tmsm-aquatonic-course-booking-weekdays-previous').attr('disabled', true);
        console.log('premiere page je cache previous');
      }
      else{
        $('#tmsm-aquatonic-course-booking-weekdays-previous').attr('disabled', false);
        console.log('autre page jaffiche previous');
      }

      if((TmsmAquatonicCourseApp.data.daysrangeto / 7) < this.daysPage){
        $('#tmsm-aquatonic-course-booking-weekdays-next').attr('disabled', true);
      }
      else{
        $('#tmsm-aquatonic-course-booking-weekdays-next').attr('disabled', false);
      }

      var i = 0;


      TmsmAquatonicCourseApp.times_indexmax = 1;
      $('#tmsm-aquatonic-course-booking-next-times').hide();

      for (i = (parseInt(TmsmAquatonicCourseApp.data.daysrangefrom)+(this.daysPage-1) * 7); i < (parseInt(TmsmAquatonicCourseApp.data.daysrangefrom)+7+(this.daysPage-1) * 7); i++) {

        this.collection.push( {
          date_label: moment().add(i, 'days').format('dddd Do MMMM'),
          date_label_secondline: moment().add(i, 'days').format('MMMM'),
          date_label_firstline: moment().add(i, 'days').format('dddd Do'),
          date_computed: moment().add(i, 'days').format('YYYY-MM-DD')
        });
      }

      console.log('WeekDayListView collection:');
      console.log(this.collection);

      console.log('WeekDayListView collection length: ' + this.collection.length);

      this.collection.each( function( model ) {


        //console.log('WeekDayListView each');
        //console.log(model);
        var item = new TmsmAquatonicCourseApp.WeekDayListItemView( { model: model } );
        $list.append( item.render().$el );

        $('select.tmsm-aquatonic-course-booking-weekday-times[data-date='+model.attributes.date_computed+']').selectpicker('refresh');

        console.log('WeekDayListView fetch:');
        TmsmAquatonicCourseApp.times.fetch({
          data: {
            date: model.attributes.date_computed,
            participants: TmsmAquatonicCourseApp.participants,
          },
          complete: function(xhr){
            console.log('complete fetch for date '+model.attributes.date_computed);
            console.log('count options:' + $('select.tmsm-aquatonic-course-booking-weekday-times[data-date='+model.attributes.date_computed+'] option').length);

            // No results: remove the first option "Pick a timeslot" to let appear first "No timeslot available"
            if($('select.tmsm-aquatonic-course-booking-weekday-times[data-date='+model.attributes.date_computed+'] option:eq(1)').length > 0){
              if($.trim($('select.tmsm-aquatonic-course-booking-weekday-times[data-date='+model.attributes.date_computed+'] option:eq(1)').text()) == TmsmAquatonicCourseApp.i18n.notimeslot){
                $('select.tmsm-aquatonic-course-booking-weekday-times[data-date='+model.attributes.date_computed+'] option:eq(0)').remove();
              }
            }

            console.log('TmsmAquatonicCourseApp.times_indexmax after fetching '+ model.attributes.date_computed + ': ' + TmsmAquatonicCourseApp.times_indexmax);

            $('select.tmsm-aquatonic-course-booking-weekday-times[data-date='+model.attributes.date_computed+']').selectpicker('refresh');

            object.refreshNextTimesButton();


          }
        });

      }, this );




      return this;
    },

    selectTimeWithSelect: function(event){
      console.log('selectTimeWithSelect');
      console.log('event.target.selectedIndex:'+event.target.selectedIndex);
      event.preventDefault();
      var date = $(event.target.options[event.target.selectedIndex]).data('date');
      var hour = $(event.target.options[event.target.selectedIndex]).data('hour');
      var minutes = $(event.target.options[event.target.selectedIndex]).data('minutes');
      this.selectedValue =  $(event.target.options[event.target.selectedIndex]).data('hourminutes');
      console.log('WeekDayListView selectedValue: '+ this.selectedValue);
      TmsmAquatonicCourseApp.selectedData.set('hourminutes', this.selectedValue);
      TmsmAquatonicCourseApp.selectedData.set('hour', hour);
      TmsmAquatonicCourseApp.selectedData.set('minutes', minutes);
      TmsmAquatonicCourseApp.selectedData.set('date', date);
    },

    selectTime: function(event){
      event.preventDefault();
      console.log('WeekDayListView selectTime');
      this.selectedValue = $(event.target).data('hourminutes');
      var date = $(event.target).data('date');
      var hour = $(event.target).data('hour');
      var minutes = $(event.target).data('minutes');
      console.log('WeekDayListView selectedValue: '+ this.selectedValue);
      $( this.selectButtons ).removeClass('btn-primary').removeClass('disabled').removeClass('selected').addClass('not-selected');
      $(event.target).addClass('btn-primary').addClass('disabled').removeClass('not-selected');

      console.warn($(this.addAppointmentButton));
      //TmsmAquatonicCourseApp.animateTransition($(this.addAppointmentButton));

      TmsmAquatonicCourseApp.selectedData.set('hourminutes', this.selectedValue);
      TmsmAquatonicCourseApp.selectedData.set('hour', hour);
      TmsmAquatonicCourseApp.selectedData.set('minutes', minutes);
      TmsmAquatonicCourseApp.selectedData.set('date', date);
    },

  } );

  TmsmAquatonicCourseApp.WeekDayListItemView = Backbone.View.extend( {
    tagName: 'div',
    className: 'tmsm-aquatonic-course-booking-weekday-item',
    template: wp.template( 'tmsm-aquatonic-course-booking-weekday' ),

    initialize: function() {
      this.listenTo( this.model, 'change', this.render );
      this.listenTo( this.model, 'destroy', this.remove );
    },

    render: function() {
      var html = this.template( this.model.toJSON() );
      this.$el.html( html );
      return this;
    },

  } );


  /**
   * Selected Data
   */
  TmsmAquatonicCourseApp.SelectedDataModel = Backbone.Model.extend( {
    defaults: {
      date: null,
      hourminutes: null,
      hour: null,
      minutes: null,
    },

  } );


  TmsmAquatonicCourseApp.SelectedDataView = Backbone.View.extend( {
    el: '#tmsm-aquatonic-course-booking-confirm-container',
    cancelButton: '#tmsm-aquatonic-course-booking-cancel',
    confirmButton: '.gform_button[type=submit]',
    errorElement: '#tmsm-aquatonic-course-booking-confirm-error',

    initialize: function() {
      console.log('SelectedDataView initialize');
      this.hideError();
      this.hideConfirm();
      this.hideCancel();
      this.listenTo(this.model, 'change', this.change);
    },

    events: {
      'click #tmsm-aquatonic-course-booking-cancel': 'cancel',
      'click #tmsm-aquatonic-course-booking-confirm': 'confirm'
    },

    cancel: function(event){
      event.preventDefault();

      this.hideError();

      console.log('SelectedDataView cancel');

      TmsmAquatonicCourseApp.selectedData.clear().set({});

      TmsmAquatonicCourseApp.dateList.reset();
      TmsmAquatonicCourseApp.timesList.reset();
    },

    confirm: function(event) {
      event.preventDefault();

      console.log('SelectedDataView confirm');
      $(this.errorElement).empty();
      this.showLoading();
      var container = this;





    },

    change: function (){
      console.log('SelectedDataView change');
      console.log(this.model);

      if(this.canConfirm(this.model.attributes)){

        console.log('date:'+this.model.attributes.date);
        console.log('hour:'+this.model.attributes.hour);
        console.log('minutes:'+this.model.attributes.minutes);

        var day = moment(this.model.attributes.date);
        console.log(day);



        if($(TmsmAquatonicCourseApp.form_fields.date_field).length > 0){
          $(TmsmAquatonicCourseApp.form_fields.date_field).val(this.model.attributes.date);
        }
        if($(TmsmAquatonicCourseApp.form_fields.hour_field).length > 0){
          $(TmsmAquatonicCourseApp.form_fields.hour_field).val(this.model.attributes.hour);
        }
        if($(TmsmAquatonicCourseApp.form_fields.minutes_field).length > 0){
          $(TmsmAquatonicCourseApp.form_fields.minutes_field).val(this.model.attributes.minutes);
        }
        if($(TmsmAquatonicCourseApp.form_fields.summary_field).length > 0){
          $(TmsmAquatonicCourseApp.form_fields.summary_field).html(sprintf(TmsmAquatonicCourseApp.i18n.summary, TmsmAquatonicCourseApp.participants, day.format('dddd DD MMMM YYYY'), this.model.attributes.hour, this.model.attributes.minutes ));
        }



        this.showConfirm();
      }
      else{
        this.hideConfirm();
      }


    },

    showLoading: function(){
      console.log('SelectedDataView showLoading');
      $( this.confirmButton ).prop('disabled', true).addClass('btn-disabled');
    },
    hideLoading: function(){
      console.log('SelectedDataView hideLoading');
      $( this.confirmButton ).prop('disabled', false).removeClass('btn-disabled');
    },
    showError: function(){
      $( this.errorElement ).show();
    },
    hideError: function(){
      $( this.errorElement ).hide();
    },
    showConfirm: function(){
      console.log('SelectedDataView showConfirm 02');
      TmsmAquatonicCourseApp.animateTransition( $(TmsmAquatonicCourseApp.form_fields.summary_field));
      $( this.confirmButton ).show();
    },
    hideConfirm: function(){
      $( this.confirmButton ).hide();
    },

    showCancel: function(){
      console.log('SelectedDataView showCancel');
      $( this.cancelButton ).show();
    },
    hideCancel: function(){
      $( this.cancelButton ).hide();
    },

    canConfirm: function(attributes) {
      return ( attributes.date != null && attributes.hour != null && attributes.minutes != null );
    },

    render: function() {

      return this;
    },

    element: function (){
      return this.$el;
    },
    hide: function (){
      this.$el.hide();
    },
    show: function (){
      this.$el.show();
    }

  } );

  // Animate Display
  TmsmAquatonicCourseApp.animateTransition = function(element){
    console.log('animateTransition ' + element.attr('id'));
    element.show();
    $('html, body').animate({
      scrollTop: element.offset().top
    }, 400);
  };

  /**
   * Set initial data into view and start recurring display updates.
   */
  TmsmAquatonicCourseApp.init = function() {
    console.log('TmsmAquatonicCourseApp.init');



    /*$('.tmsm-aquatonic-course-birthdate input').datepicker({
      'language': TmsmAquatonicCourseApp.data.locale,
      'format': TmsmAquatonicCourseApp.i18n.birthdateformatdatepicker
    });*/
    $('.tmsm-aquatonic-course-birthdate input').mask("99/99/9999", {placeholder: TmsmAquatonicCourseApp.i18n.birthdateformat});
    //$('.tmsm-aquatonic-course-birthdate input').mask("99/99/9999", {placeholder: TmsmAquatonicCourseApp.i18n.birthdateformat});

    if($('.tmsm-aquatonic-course-participants').length > 0 ){
      TmsmAquatonicCourseApp.participants = $('.tmsm-aquatonic-course-participants input').val();
    }

    //if(TmsmAquatonicCourseApp.form_fields.step == 2 ){
      console.log('loading rest of app');
      TmsmAquatonicCourseApp.times_per_page = 10;
      TmsmAquatonicCourseApp.times_page = 1;
      TmsmAquatonicCourseApp.times_indexmax = 1;

      TmsmAquatonicCourseApp.times = new TmsmAquatonicCourseApp.TimesCollection();
      TmsmAquatonicCourseApp.times.reset( TmsmAquatonicCourseApp.data.times );
      TmsmAquatonicCourseApp.timesList = new TmsmAquatonicCourseApp.TimesListView( { collection: TmsmAquatonicCourseApp.times } );
      TmsmAquatonicCourseApp.timesList.render();

      TmsmAquatonicCourseApp.weekdays = new TmsmAquatonicCourseApp.WeekDayCollection();
      TmsmAquatonicCourseApp.weekdays.reset( TmsmAquatonicCourseApp.data.times );
      TmsmAquatonicCourseApp.weekdaysList = new TmsmAquatonicCourseApp.WeekDayListView( { collection: TmsmAquatonicCourseApp.weekdays } );
      TmsmAquatonicCourseApp.weekdaysList.render();

      TmsmAquatonicCourseApp.selectedData = new TmsmAquatonicCourseApp.SelectedDataModel();
      TmsmAquatonicCourseApp.selectedDataList = new TmsmAquatonicCourseApp.SelectedDataView( { model: TmsmAquatonicCourseApp.selectedData } );
    //}

    // Check Gravity Forms step
    $(document).bind('gform_post_render', function(event, formId, currentPage){
      console.warn('currentPage:'+currentPage);

      // On step 2, load weekday times
      TmsmAquatonicCourseApp.form_fields.step = currentPage;
      if(TmsmAquatonicCourseApp.form_fields.step == 2){
        TmsmAquatonicCourseApp.weekdaysList.render();
      }

      // Reset summary field
      if($(TmsmAquatonicCourseApp.form_fields.summary_field).length > 0){
        $(TmsmAquatonicCourseApp.form_fields.summary_field).html('');
      }
    });



  };

  $( document ).ready( function() {

    if($('.tmsm-aquatonic-course-form-add_wrapper').length > 0){
      TmsmAquatonicCourseApp.init();
    }

  } );




})(jQuery, TmsmAquatonicCourseApp);

// OptinMonster compatibility
document.addEventListener('om.Scripts.init', function(evt) {
  window._omapp.scripts.moment.status = 'loaded';
  window._omapp.scripts.moment.object = window.moment ? window.moment : null;
  window._omapp.scripts.momentTz.status = 'loaded';
  window._omapp.scripts.momentTz.object = window.moment ? window.moment.tz : null;
});
