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

      // console.log('TimesListView collection:');
      // console.log(this.collection);
      // console.log('TimesListView collection length: ' + this.collection.length);

      var i = 0;
      this.collection.each( function( model ) {
        i++;
        if(i===1){
          var $selectForDate = $( '.tmsm-aquatonic-course-booking-weekday-times[data-date="'+model.attributes.date+'"]' );
          $selectForDate.empty();
          // console.log('model.attributes.hourminutes: '+model.attributes.hourminutes);
          if ( model.attributes.hourminutes == null ) {
            // If API returns only an informational row, show it as the select placeholder.
            $selectForDate.append('<option selected>' + ( model.attributes.message || TmsmAquatonicCourseApp.i18n.notimeslot ) + '</option>');
          } else {
            $selectForDate.append('<option>' + TmsmAquatonicCourseApp.i18n.pickatimeslot + '</option>');
          }
        }

        if ( model.attributes.hourminutes == null ) {
          return;
        }

        var item = new TmsmAquatonicCourseApp.TimesListItemView( { model: model } );
        // console.log('item render:');
        // console.log(item.render());
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
      // console.log('TimeListView selectTime');
      this.selectedValue = $(event.target).data('hourminutes');
      // console.log('TimeListView selectedValue: '+ this.selectedValue);
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

// Test ultime pour savoir si le script est lu
// console.log('--- DEBUG START ---');
// console.log('Fichier datepicker_integration.js chargé (V20 - Fix Final Validation & Events)');

TmsmAquatonicCourseApp.WeekDayListView = Backbone.View.extend({
  el: '#tmsm-aquatonic-course-slots-container',
  
  listElement: '#tmsm-aquatonic-course-booking-weekdays-list',
  datepickerId: '#tmsm-inline-calendar', 
  
  headerTemplate: null,
  startDateOffset: 0,

  initialize: function() {
    // console.log('WeekDayListView initialize (Arnaud V20)');
    
    if (typeof moment === 'undefined') {
        console.error('Moment.js manquant');
        return;
    }

    moment.locale(TmsmAquatonicCourseApp.data.locale);
    
    // Initialisation : on se cale sur le lundi de la semaine actuelle
    var startDay = moment().add(parseInt(TmsmAquatonicCourseApp.data.daysrangefrom) || 0, 'days').startOf('isoWeek');
    var today = moment().startOf('day');
    this.startDateOffset = startDay.diff(today, 'days');

    // Cache des anciens boutons
    $('#tmsm-aquatonic-course-booking-weekdays-previous, #tmsm-aquatonic-course-booking-weekdays-next').hide();

    this.listenTo(this.collection, 'sync', this.render);
    _.bindAll(this, 'render', 'initDatepicker', 'onDateChange', 'getHeaderHtml', 'injectStyles', 'selectTime', 'selectTimeWithSelect');
    
    this.injectStyles();
  },

  injectStyles: function() {
    if ($('#tmsm-datepicker-styles').length > 0) return;

    var css = `
      .tmsm-inline-calendar-wrapper {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin: 0 auto 20px auto;
        overflow: hidden;
        max-width: 1200px;
        max-height: 800px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      }

      .tmsm-calendar-title-area {
        padding: 10px 0 10px 0;
        text-align: center;
      }

      .tmsm-calendar-title-area h2 {
        font-size: 2rem;
        font-weight: 300;
        color: #666;
        margin: 0;
      }

      .datepicker-inline {
        width: 100% !important;
        border: none !important;
        padding: 0 40px 10px 40px !important;
      }

      .datepicker table {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0 10px !important;
      }

      .datepicker .datepicker-switch {
        font-weight: 800 !important;
        color: #111 !important;
        font-size: 1.6rem !important;
        text-transform: uppercase;
        letter-spacing: 2px;
      }
      
      .datepicker .prev, .datepicker .next {
        color: #1a56db !important;
        font-size: 2.5rem !important;
        font-weight: bold !important;
      }

      .datepicker .dow {
        color: #111 !important;
        font-weight: 800 !important;
        font-size: 1.2rem !important;
        text-transform: uppercase;
        padding: 10px 0 !important;
        border-bottom: 2px solid #f1f5f9 !important;
      }

      .datepicker table tr td.day {
        height: 30px !important;
        font-size: 1.6rem !important;
        font-weight: 500 !important;
        color: #111 !important;
        border-radius: 0 !important;
        transition: all 0.2s ease;
      }

      .datepicker table tr td.old, 
      .datepicker table tr td.day.disabled,
      .datepicker table tr td.past {
        color: #eee !important;
        opacity: 0.5;
      }

      .datepicker table tr td.active, 
      .datepicker table tr td.active:hover,
      .datepicker table tr td.active.active {
        background-color: #1a56db !important;
        background-image: none !important;
        color: #ffffff !important;
        font-weight: 800 !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 15px -3px rgba(26, 86, 219, 0.4) !important;
        z-index: 10;
        position: relative;
      }

      .datepicker table tr:has(.active) td:not(.old):not(.new) {
        background-color: #eff6ff;
      }

      .datepicker table tr:has(.active) td:not(.old):not(.new):first-child {
        border-top-left-radius: 12px !important;
        border-bottom-left-radius: 12px !important;
      }
      .datepicker table tr:has(.active) td:not(.old):not(.new):last-child {
        border-top-right-radius: 12px !important;
        border-bottom-right-radius: 12px !important;
      }

      .datepicker table tr td.today {
        background: transparent !important;
        color: #1a56db !important;
        font-weight: 900 !important;
      }

      .datepicker .datepicker-footer {
        display: block !important;
        text-align: center !important;
        padding: 25px 0 !important;
        border-top: 1px solid #f1f5f9 !important;
        font-size: 1.5rem !important;
        font-weight: 800 !important;
        text-transform: uppercase;
        color: #1a56db !important;
        cursor: pointer;
      }

      @media (max-width: 768px) {
        .datepicker-inline { padding: 10px !important; }
        .datepicker table tr td.day { height: 55px !important; font-size: 1.3rem !important; }
        .tmsm-calendar-title-area h2 { font-size: 1.5rem; }
      }
    `;

    $('<style>')
      .attr('id', 'tmsm-datepicker-styles')
      .prop('type', 'text/css')
      .html(css)
      .appendTo('head');
  },

  events: {
    'click .tmsm-aquatonic-course-booking-time-button': 'selectTime',
    'change .tmsm-aquatonic-course-booking-weekday-times': 'selectTimeWithSelect', // ESSENTIEL : Écoute le changement du select
    'click #tmsm-aquatonic-course-booking-weekdays-previous': 'previous',
    'click #tmsm-aquatonic-course-booking-weekdays-next': 'next',
  },

  getHeaderHtml: function() {
      var templateId = 'tmsm-aquatonic-calendar-header';
      if ($('#tmpl-' + templateId).length > 0) {
          try {
              return wp.template(templateId)();
          } catch (e) {}
      }

      return '<div class="tmsm-inline-calendar-wrapper">' +
             '  <div id="tmsm-inline-calendar"></div>' +
             '</div>' +
             '<div id="tmsm-aquatonic-course-booking-weekdays-list"></div>';
  },

  onDateChange: function(date) {
    if (!date) return;
    
    var selectedDate = moment(date);
    var selectedMonday = selectedDate.clone().startOf('isoWeek'); 
    var today = moment().startOf('day');

    if (selectedMonday.isValid()) {
      this.startDateOffset = selectedMonday.diff(today, 'days');
      
      // On met à jour la date dans le modèle pour la validation immédiate si besoin
      if (TmsmAquatonicCourseApp.selectedData) {
          TmsmAquatonicCourseApp.selectedData.set("date", selectedDate.format("YYYY-MM-DD"));
      }

      this.render();
    }
  },

  initDatepicker: function() {
    var self = this;
    var $calendar = $(this.datepickerId);

    if (typeof $.fn.datepicker !== 'undefined') {
      if ($calendar.length > 0) {
        $calendar.datepicker({
          format: 'dd/mm/yyyy',
          language: 'fr',
          weekStart: 1, 
          todayHighlight: true,
          startDate: new Date(),
          todayBtn: "linked",
          clearBtn: false
        }).on('changeDate', function(e) {
            self.onDateChange(e.date);
        });

        var currentActiveDate = moment().add(this.startDateOffset, 'days').toDate();
        $calendar.datepicker('setDate', currentActiveDate);
      }
    } else {
      setTimeout(this.initDatepicker, 1000);
    }
  },

  render: function() {
    var self = this;
    
    if (this.$('#tmsm-inline-calendar').length === 0) {
        this.$el.html(this.getHeaderHtml());
        setTimeout(this.initDatepicker, 100);
    }

    var $list = this.$(this.listElement);
    if ($list.length === 0) {
        this.$el.append('<div id="tmsm-aquatonic-course-booking-weekdays-list"></div>');
        $list = this.$(this.listElement);
    }
    $list.empty();
    
    this.collection.reset();
    TmsmAquatonicCourseApp.times_indexmax = 1;

    for (var i = this.startDateOffset; i < (this.startDateOffset + 7); i++) {
      this.collection.push({
        date_label: moment().add(i, 'days').format('dddd Do MMMM'),
        date_label_secondline: moment().add(i, 'days').format('MMMM'),
        date_label_firstline: moment().add(i, 'days').format('dddd Do'),
        date_computed: moment().add(i, 'days').format('YYYY-MM-DD')
      });
    }

    var loaded_days = 0;
    this.collection.each(function(model) {
      var item = new TmsmAquatonicCourseApp.WeekDayListItemView({ model: model });
      $list.append(item.render().$el);

      var $select = $('select.tmsm-aquatonic-course-booking-weekday-times[data-date='+model.attributes.date_computed+']');
      $select.hide().parent().next().show();

      TmsmAquatonicCourseApp.times.fetch({
        data: {
          date: model.attributes.date_computed,
          participants: TmsmAquatonicCourseApp.participants,
        },
        complete: function() {
          $select.selectpicker('refresh').show().parent().next().hide();
          loaded_days++;
        }
      });
    }, this);

    return this;
  },

  selectTimeWithSelect: function(event) {
    var $opt = $(event.target.options[event.target.selectedIndex]);
    console.log('Selection via Select detectée:', $opt.data('hourminutes'));

    if (!$opt.data('hourminutes')) {
      TmsmAquatonicCourseApp.selectedData.set({
        'hourminutes': null,
        'hour': null,
        'minutes': null,
        'date': null
      });
      return;
    }
    
    // MISE A JOUR DU MODELE (Critique pour validation)
    TmsmAquatonicCourseApp.selectedData.set({
      'hourminutes': $opt.data('hourminutes'),
      'hour': $opt.data('hour'),
      'minutes': $opt.data('minutes'),
      'date': $opt.data('date')
    });
  },

  selectTime: function(event) {
    // Utilisation de currentTarget pour être sûr de choper le bouton
    var $el = $(event.currentTarget);
    $('.tmsm-aquatonic-course-booking-time-button').removeClass('btn-primary disabled selected').addClass('not-selected');
    $el.addClass('btn-primary disabled').removeClass('not-selected');

    console.log('Selection via Button detectée:', $el.data('hourminutes'));

    // MISE A JOUR DU MODELE (Critique pour validation)
    TmsmAquatonicCourseApp.selectedData.set({
      'hourminutes': $el.data('hourminutes'),
      'hour': $el.data('hour'),
      'minutes': $el.data('minutes'),
      'date': $el.data('date')
    });
    
    // Animation automatique vers le bouton Continuer
    if (TmsmAquatonicCourseApp.selectedDataList && TmsmAquatonicCourseApp.selectedDataList.confirmButton) {
         TmsmAquatonicCourseApp.animateTransition($(TmsmAquatonicCourseApp.selectedDataList.confirmButton));
    }
  }
});
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
    confirmButton: '.gform_next_button',
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
          var formatSummary = window.sprintf || function(format) {
            var args = Array.prototype.slice.call(arguments, 1);
            var autoIndex = 0;
            return String(format || '').replace(/%(\d+\$)?s/g, function(match, numbered) {
              if (numbered) {
                var numberedIndex = parseInt(numbered, 10) - 1;
                return typeof args[numberedIndex] !== 'undefined' ? args[numberedIndex] : '';
              }
              var current = autoIndex++;
              return typeof args[current] !== 'undefined' ? args[current] : '';
            });
          };
          $(TmsmAquatonicCourseApp.form_fields.summary_field).html(
            formatSummary(
              TmsmAquatonicCourseApp.i18n.summary,
              TmsmAquatonicCourseApp.participants,
              day.format('dddd DD MMMM YYYY'),
              this.model.attributes.hour,
              this.model.attributes.minutes
            )
          );
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
      console.log('canConfirm: ' + attributes.date + ' ' + attributes.hour + ' ' + attributes.minutes);
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

    moment.locale('fr');
    moment.locale('fr_FR');
    moment.updateLocale('fr', null);

    TmsmAquatonicCourseApp.participants = $('.tmsm-aquatonic-course-participants input').val();
    if(TmsmAquatonicCourseApp.participants != ''){
      $('.tmsm-aquatonic-course-times').show();
      $('.tmsm-aquatonic-course-summary').show();
    }
    else{
      $('.tmsm-aquatonic-course-times').hide();
      $('.tmsm-aquatonic-course-summary').hide();
    }

    //if(TmsmAquatonicCourseApp.form_fields.step == 2 ){
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

  };

  $(document).on('gform_post_render', function(event, form_id, current_page){
    console.log('optimizations gform_post_render');
  });

  if ($('.tmsm-aquatonic-course-participants').length > 0) {
    console.log('exist participants');

    if($('.tmsm-aquatonic-course-participants input').val() != ''){
      console.log('defined participants');
      TmsmAquatonicCourseApp.init();
    }

    $('.tmsm-aquatonic-course-participants input').on('keyup input', function (e) {
      console.log('change participants');
      TmsmAquatonicCourseApp.init();
    });
  }

  // Disable PDF link after click
  $('#tmsm-aquatonic-course-booking-download-pdf').on('click', function (e) {
    $(this).addClass('disabled');
    $(this).text(TmsmAquatonicCourseApp.i18n.downloading);
  });


  //$('.tmsm-aquatonic-course-birthdate input').mask("99/99/9999", {placeholder: TmsmAquatonicCourseApp.i18n.birthdateformat});
  //$('.tmsm-aquatonic-course-phone input').mask('+99-9999999999', {placeholder: ''});
  /*$('.tmsm-aquatonic-course-phone input').mask('YZ0000000000', {placeholder: '__________', translation:  {
    'Y': {pattern: /\+/, optional: true},
    'Z': {pattern: /[0-9]/, optional: true}
  }});*/





})(jQuery, TmsmAquatonicCourseApp);

