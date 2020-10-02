(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */


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
