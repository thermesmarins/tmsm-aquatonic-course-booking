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

      console.log('sync action: '+options.data.action);
      console.log('sync options: ');
      console.log(options);


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
      hourminutes: null,
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
          $( '.tmsm-aquatonic-course-booking-weekday-times[data-date="'+model.attributes.date+'"]').empty();
        }
        var item = new TmsmAquatonicCourseApp.TimesListItemView( { model: model } );
        if ($('.tmsm-aquatonic-course-booking-weekday-times[data-date="' + model.attributes.date + '"]').length > 0) {
          $( '.tmsm-aquatonic-course-booking-weekday-times[data-date="' + model.attributes.date + '"]').append(item.render().$el.context.outerHTML);
        }
        else{
          console.log('tmsm-aquatonic-course-booking-weekday-times not added for '+model.attributes.date);
        }
        //
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
    tagName: 'li',
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
      'click #tmsm-aquatonic-course-booking-weekdays-previous': 'previous',
      'click #tmsm-aquatonic-course-booking-weekdays-next': 'next',
    },

    previous: function(event){
      console.log('WeekDayListView previous');

      event.preventDefault();
      this.daysPage = this.daysPage - 1;
      this.render();
    },

    next: function(event){
      console.log('WeekDayListView next');

      event.preventDefault();
      this.daysPage = this.daysPage + 1;

      this.render();
    },

    render: function() {
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

        console.log('WeekDayListView fetch:');
        TmsmAquatonicCourseApp.times.fetch({
          data: {
            date: model.attributes.date_computed,
            participants: TmsmAquatonicCourseApp.participants,
          }
        });

      }, this );


      return this;
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
    confirmButton: '#tmsm-aquatonic-course-booking-confirm',
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

        if($(TmsmAquatonicCourseApp.form_fields.date_field).length > 0){
          $(TmsmAquatonicCourseApp.form_fields.date_field).val(this.model.attributes.date);
        }
        if($(TmsmAquatonicCourseApp.form_fields.hour_field).length > 0){
          $(TmsmAquatonicCourseApp.form_fields.hour_field).val(this.model.attributes.hour);
        }
        if($(TmsmAquatonicCourseApp.form_fields.minutes_field).length > 0){
          $(TmsmAquatonicCourseApp.form_fields.minutes_field).val(this.model.attributes.minutes);
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
      console.log('SelectedDataView showConfirm');
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

  /**
   * Set initial data into view and start recurring display updates.
   */
  TmsmAquatonicCourseApp.init = function() {
    console.log('TmsmAquatonicCourseApp.init 01');

    $('.tmsm-aquatonic-course-birthdate input').mask("99/99/9999", {placeholder: TmsmAquatonicCourseApp.i18n.birthdateformat});

    if($('.tmsm-aquatonic-course-participants').length > 0 ){
      TmsmAquatonicCourseApp.participants = $('.tmsm-aquatonic-course-participants input').val();
    }

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


  };

  $( document ).ready( function() {

    if($('.tmsm-aquatonic-course-form_wrapper').length > 0){
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
