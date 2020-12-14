(function( $ ) {
	'use strict';

  $('.tmsm-aquatonic-course-birthdate input').mask("99/99/9999", {placeholder: tmsm_aquatonic_course_booking_params.i18n.birthdateformat});

	if($('.tmsm-aquatonic-course-participants').length > 0 ){
	  var participants = $('.tmsm-aquatonic-course-participants input').val();
    $('#tmsm-aquatonic-course-slots-container').html('participants: '+participants);
  }

  var bbdemo = bbdemo || {};
  var wp = window.wp || {};

  (function($) {
    bbdemo.Post = Backbone.Model.extend({
      initialize: function(data) {
        try {
          this.set('title', data.title.rendered);
        } catch (e) {
          console.log(e);
        }
      }
    });

    bbdemo.PostCollection = Backbone.Collection.extend({
      model: bbdemo.Post,
      url: bbdata.rest_url + 'wp/v2/posts',

    });

    bbdemo.PostsView = wp.Backbone.View.extend({
      template: wp.template('bb-post-listing'),
      page: 1,

      templateHelpers: {
        moment: moment // <-- this is the reference to the moment in your view
      },
      events: {
        'click .refresh': 'refreshPosts',
        'click .previous': 'previous',
        'click .next': 'next',
      },

      previous: function(){

        this.page = this.page - 1;

        this.refreshPosts();
      },

      next: function(){
        this.page = this.page + 1;

        this.refreshPosts();
      },

      refreshPosts: function() {
        this.collection.reset();
        this.views.remove();
        this.render();
        this.collection.fetch({
          // Override the url for the fetch to be able to get draft posts and the publish "status" value in the result
          url: bbdata.rest_url + 'wp/v2/posts?filter[post_status]=draft,publish&page=' + this.page,
          headers: { 'X-WP-Nonce': bbdata.nonce }
        });
      },

      initialize: function() {
        this.listenTo(this.collection, 'add', this.addPostView);
        console.log("moment", moment().format());
      },

      addPostView: function(post) {
        this.views.add('.bb-posts', new bbdemo.PostView({ model: post }));
      }
    });

    bbdemo.PostView = wp.Backbone.View.extend({
      template: wp.template('bb-post'),
      tagName: 'tr',

      events: {
        'click .save': 'save'
      },

      save: function() {
        var self = this;
        this.model.set('title', this.$('.title').val());
        this.model.set('status', this.$('.status').val());
        this.model.save({}, {
          headers: { 'X-WP-Nonce': bbdata.nonce },
          success: function() {
            //self.$el.effect('highlight', {}, 3000);
          }
        });
      },

      prepare: function() {
        return this.model.toJSON();
      }
    });

    bbdemo.initialize = function() {
      var postCollection = new bbdemo.PostCollection();
      var postsView = new bbdemo.PostsView({ collection: postCollection });
      postCollection.add(bbdata.posts);
      $('#postlisting').html(postsView.render().el);
    }

    $(document).ready(function(){
      bbdemo.initialize();
    });
  })(jQuery);




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
    url: TmsmAquatonicCourseApp.ajaxurl,
    action: 'tmsm-aquatonic-course-booking-weekday',

    sync: function( method, object, options ) {

      if ( typeof options.data === 'undefined' ) {
        options.data = {};
      }

      options.data.nonce = TmsmAquatonicCourseApp.nonce; // From localized script.
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
   * WeekDay
   */
  TmsmAquatonicCourseApp.WeekDayModel = TmsmAquatonicCourseBaseModel.extend( {
    action: 'tmsm-aquos-spa-booking-weekday',
    defaults: {
      date_label: null,
      date_computed: null,
    }
  } );

  TmsmAquatonicCourseApp.WeekDayCollection = TmsmAquatonicCourseBaseCollection.extend( {
    action: 'tmsm-aquos-spa-booking-weekday',
    model: TmsmAquatonicCourseApp.WeekDayModel,

  } );

  TmsmAquatonicCourseApp.WeekDayListView = Backbone.View.extend( {
    el: '#tmsm-aquos-spa-booking-date-container',
    listElement: '#tmsm-aquos-spa-booking-weekdays-list',
    selectButtons: '.tmsm-aquos-spa-booking-time-button',
    addAppointmentButton: '#tmsm-aquos-spa-booking-confirm',
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
      'click .tmsm-aquos-spa-booking-time-button' : 'selectTime',
      'click #tmsm-aquos-spa-booking-weekdays-previous': 'previous',
      'click #tmsm-aquos-spa-booking-weekdays-next': 'next',
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
        $('#tmsm-aquos-spa-booking-weekdays-previous').attr('disabled', true);
        console.log('premiere page je cache previous');
      }
      else{
        $('#tmsm-aquos-spa-booking-weekdays-previous').attr('disabled', false);
        console.log('autre page jaffiche previous');
      }

      if((TmsmAquatonicCourseApp.calendar.daysrangeto / 7) < this.daysPage){
        $('#tmsm-aquos-spa-booking-weekdays-next').attr('disabled', true);
      }
      else{
        $('#tmsm-aquos-spa-booking-weekdays-next').attr('disabled', false);
      }

      var i = 0;

      if(TmsmAquatonicCourseApp.productsList.selectedValue){
        for (i = (parseInt(TmsmAquatonicCourseApp.calendar.daysrangefrom)+(this.daysPage-1) * 7); i < (parseInt(TmsmAquatonicCourseApp.calendar.daysrangefrom)+7+(this.daysPage-1) * 7); i++) {

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
              productcategory: TmsmAquatonicCourseApp.productCategoriesList.selectedValue,
              product: TmsmAquatonicCourseApp.productsList.selectedValue,
              productvariation: TmsmAquatonicCourseApp.productVariationsList.selectedValue,
              choice: TmsmAquatonicCourseApp.choicesList.selectedValue,
              date: model.attributes.date_computed
            }
          });

        }, this );

      }

      return this;
    },
    selectTime: function(event){
      event.preventDefault();
      console.log('WeekDayListView selectTime');
      this.selectedValue = $(event.target).data('hourminutes');
      var date = $(event.target).data('date');
      console.log('WeekDayListView selectedValue: '+ this.selectedValue);
      $( this.selectButtons ).removeClass('btn-primary').removeClass('disabled').removeClass('selected').addClass('not-selected');
      $(event.target).addClass('btn-primary').addClass('disabled').removeClass('not-selected');

      console.warn($(this.addAppointmentButton));
      TmsmAquatonicCourseApp.animateTransition($(this.addAppointmentButton));

      TmsmAquatonicCourseApp.selectedData.set('hourminutes', this.selectedValue);
      TmsmAquatonicCourseApp.selectedData.set('date', date);
    },

  } );

  TmsmAquatonicCourseApp.WeekDayListItemView = Backbone.View.extend( {
    tagName: 'li',
    className: 'tmsm-aquos-spa-booking-weekday-item',
    template: wp.template( 'tmsm-aquos-spa-booking-weekday' ),

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
      productcategory: null,
      product: null,
      productvariation: null,
      date: null,
      hourminutes: null,
      is_voucher: null
    },

  } );


  TmsmAquatonicCourseApp.SelectedDataView = Backbone.View.extend( {
    el: '#tmsm-aquos-spa-booking-confirm-container',
    cancelButton: '#tmsm-aquos-spa-booking-cancel',
    confirmButton: '#tmsm-aquos-spa-booking-confirm',
    errorElement: '#tmsm-aquos-spa-booking-confirm-error',

    initialize: function() {
      console.log('SelectedDataView initialize');
      this.hideError();
      this.hideConfirm();
      this.hideCancel();
      this.listenTo(this.model, 'change', this.change);
    },

    events: {
      'click #tmsm-aquos-spa-booking-cancel': 'cancel',
      'click #tmsm-aquos-spa-booking-confirm': 'confirm'
    },

    cancel: function(event){
      event.preventDefault();

      this.hideError();

      console.log('SelectedDataView cancel');

      TmsmAquatonicCourseApp.selectedData.clear().set({});

      TmsmAquatonicCourseApp.animateTransition(TmsmAquatonicCourseApp.havevoucherList.element());

      TmsmAquatonicCourseApp.havevoucherList.reset();
      TmsmAquatonicCourseApp.productCategoriesList.reset();
      TmsmAquatonicCourseApp.productsList.reset();
      TmsmAquatonicCourseApp.productVariationsList.reset();
      TmsmAquatonicCourseApp.dateList.reset();
      TmsmAquatonicCourseApp.timesList.reset();
    },

    confirm: function(event) {
      event.preventDefault();

      console.log('SelectedDataView confirm');
      $(this.errorElement).empty();
      this.showLoading();
      var container = this;

      wp.ajax.send('tmsm-aquos-spa-booking-addtocart', {
        success: function(data){
          console.log('wp.ajax.send success');
          console.log(data);
          if(data.redirect){
            console.log('redirect!');
            window.location = data.redirect;
          }
          else{
            console.log('no redirect...');
            console.log(data.redirect);
          }
        },
        error: function(data){
          console.log('wp.ajax.send error');
          console.log(data);
          container.hideLoading();
          console.log('wp.ajax.send error');
          console.log(data);
          if(data.errors){
            container.showError();
            $(container.errorElement).html( data.errors );
          }

        },
        data: {
          nonce: TmsmAquatonicCourseApp.nonce,
          selecteddata: TmsmAquatonicCourseApp.selectedData.attributes,
        }
      });



    },

    change: function (){
      console.log('SelectedDataView change');
      console.log(this.model);

      if(this.canConfirm(this.model.attributes)){
        this.showConfirm();
      }
      else{
        this.hideConfirm();
      }

      if(this.canCancel(this.model.attributes)){
        this.showCancel();
      }
      else{
        this.hideCancel();
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
      return (attributes.productcategory != null && attributes.product != null && attributes.date != null && attributes.hourminutes != null );
    },

    canCancel: function(attributes) {
      return (attributes.is_voucher != null && attributes.productcategory != null );
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
