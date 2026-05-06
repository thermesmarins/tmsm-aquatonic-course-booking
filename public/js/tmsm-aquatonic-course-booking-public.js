/**
 * Script public pour le plugin tmsm-aquatonic-course-booking
 * Version: 21.0 (Fix Browser Compatibility & Robustness)
 */

(function($) {
    'use strict';

    // Global application object
    window.TmsmAquatonicCourseApp = window.TmsmAquatonicCourseApp || {};
    var App = window.TmsmAquatonicCourseApp;

    /**
     * Mixin pour la synchronisation Backbone via admin-ajax.php
     */
    var AdminAjaxSyncableMixin = {
        url: function() { return App.data.ajaxurl; },
        action: 'tmsm-aquatonic-course-booking-weekday',

        sync: function(method, object, options) {
            options = options || {};
            options.data = options.data || {};
            
            options.data.nonce = App.data.nonce;
            options.data.action_type = method;

            if (undefined === options.data.action && undefined !== this.action) {
                options.data.action = this.action;
            }

            return Backbone.sync(method, object, options);
        }
    };

    /**
     * Modèles de base
     */
    var BaseModel = Backbone.Model.extend(_.extend({}, AdminAjaxSyncableMixin));
    var BaseCollection = Backbone.Collection.extend(_.extend({}, AdminAjaxSyncableMixin));

    /**
     * Modèle et Collection pour les créneaux horaires
     */
    App.TimeModel = BaseModel.extend({
        action: 'tmsm-aquatonic-course-booking-times',
        defaults: {
            date: null, hour: null, minute: null, priority: null,
            capacity: null, hourminutes: null, index: null
        }
    });

    App.TimesCollection = BaseCollection.extend({
        action: 'tmsm-aquatonic-course-booking-times',
        model: App.TimeModel
    });

    /**
     * Vue pour la liste des horaires (Select ou Liste)
     */
    App.TimesListView = Backbone.View.extend({
        el: '#tmsm-aquatonic-course-booking-times-container',
        listElement: '#tmsm-aquatonic-course-booking-times-list',
        loadingElement: '#tmsm-aquatonic-course-booking-times-loading',
        errorElement: '#tmsm-aquatonic-course-booking-times-error',

        initialize: function() {
            _.bindAll(this, 'render', 'selectTime', 'cancelTime');
            this.hide();
            this.listenTo(this.collection, 'sync', this.render);
        },

        events: {
            'click .tmsm-aquatonic-course-booking-time-button': 'selectTime',
            'click .tmsm-aquatonic-course-booking-time-change-label': 'cancelTime',
            'click #tmsm-aquatonic-course-booking-times-anotherdate': 'changeDate'
        },

        loading: function() {
            $(this.errorElement).hide();
            $(this.loadingElement).show();
            $(this.listElement).hide();
        },

        loaded: function() {
            $(this.loadingElement).hide();
            $(this.listElement).show();
        },

        render: function() {
            var $list = this.$(this.listElement).empty();
            $list.hide();

            // Track initialized dates to empty selects and add headers only once per date
            var initializedDates = {};

            this.collection.each(function(model) {
                var date = model.get('date');
                var $selectForDate = $('.tmsm-aquatonic-course-booking-weekday-times[data-date="' + date + '"]');

                if (date && !initializedDates[date]) {
                    $selectForDate.empty();
                    if (model.get('hourminutes') == null) {
                        $selectForDate.append('<option selected>' + (model.get('message') || App.i18n.notimeslot) + '</option>');
                    } else {
                        $selectForDate.append('<option>' + App.i18n.pickatimeslot + '</option>');
                    }
                    initializedDates[date] = true;
                }

                if (model.get('hourminutes') == null) return;

                var item = new App.TimesListItemView({ model: model });
                var renderedItem = item.render().el;

                if ($selectForDate.length > 0) {
                    $selectForDate.append(renderedItem.outerHTML);
                }
                $list.append(item.render().$el);
            }, this);

            this.loaded();
            this.collection.length === 0 ? $(this.errorElement).show() : $(this.errorElement).hide();
            return this;
        },

        selectTime: function(event) {
            event.preventDefault();
            var $target = $(event.target);
            var selectedValue = $target.data('hourminutes');
            $('.tmsm-aquatonic-course-booking-time-button').hide().removeClass('selected').addClass('not-selected');
            $target.show().addClass('selected').removeClass('not-selected');
            App.selectedData.set('hourminutes', selectedValue);
        },

        cancelTime: function(event) {
            event.preventDefault();
            $('.tmsm-aquatonic-course-booking-time-button').show().removeClass('selected').addClass('not-selected');
            App.selectedData.set('hourminutes', null);
        },

        changeDate: function(event) {
            event.preventDefault();
            App.weekdaysList.render(); // Retour au calendrier
        },

        hide: function() { this.$el.hide(); },
        show: function() { this.$el.show(); }
    });

    App.TimesListItemView = Backbone.View.extend({
        tagName: 'option',
        attributes: function() {
            return {
                'data-index': this.model.get('index'),
                'data-hourminutes': this.model.get('hourminutes'),
                'data-date': this.model.get('date'),
                'data-hour': this.model.get('hour'),
                'data-minutes': this.model.get('minutes')
            };
        },
        className: 'tmsm-aquatonic-course-booking-time-item',
        template: wp.template('tmsm-aquatonic-course-booking-time'),

        render: function() {
            this.$el.html(this.template(this.model.toJSON()));
            return this;
        }
    });

    /**
     * Modèle et Collection pour les jours de la semaine
     */
    App.WeekDayModel = BaseModel.extend({
        action: 'tmsm-aquatonic-course-booking-weekday',
        defaults: { date_label: null, date_computed: null }
    });

    App.WeekDayCollection = BaseCollection.extend({
        action: 'tmsm-aquatonic-course-booking-weekday',
        model: App.WeekDayModel
    });

    /**
     * Vue principale du calendrier (WeekDayListView)
     */
    App.WeekDayListView = Backbone.View.extend({
        el: '#tmsm-aquatonic-course-slots-container',
        listElement: '#tmsm-aquatonic-course-booking-weekdays-list',
        datepickerId: '#tmsm-inline-calendar',
        startDateOffset: 0,

        initialize: function() {
            _.bindAll(this, 'render', 'initDatepicker', 'onDateChange', 'injectStyles', 'selectTime', 'selectTimeWithSelect');
            
            if (typeof moment === 'undefined') {
                console.error('Moment.js is required but not loaded.');
                return;
            }

            moment.locale(App.data.locale || 'fr');
            
            var startDay = moment().add(parseInt(App.data.daysrangefrom) || 0, 'days').startOf('isoWeek');
            var today = moment().startOf('day');
            this.startDateOffset = startDay.diff(today, 'days');

            this.injectStyles();
            this.listenTo(this.collection, 'sync', this.render);
        },

        injectStyles: function() {
            var styleId = 'tmsm-datepicker-styles-v21';
            if ($('#' + styleId).length > 0) return;

            // FIX: Suppression du sélecteur :has() pour compatibilité navigateur.
            // On utilise une classe .active-week gérée en JS.
            var css = '.tmsm-inline-calendar-wrapper { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; margin: 0 auto 20px; overflow: hidden; max-width: 1200px; font-family: sans-serif; }' +
                '.datepicker-inline { width: 100% !important; border: none !important; padding: 10px 40px !important; }' +
                '.datepicker table { width: 100% !important; border-collapse: separate !important; border-spacing: 0 5px !important; }' +
                '.datepicker .datepicker-switch { font-weight: 800; color: #111; font-size: 1.2rem; text-transform: uppercase; }' +
                '.datepicker .prev, .datepicker .next { color: #1a56db !important; font-size: 1.5rem; font-weight: bold; cursor: pointer; }' +
                '.datepicker .dow { color: #444; font-weight: 700; font-size: 0.9rem; text-transform: uppercase; padding: 10px 0; border-bottom: 2px solid #f1f5f9; }' +
                '.datepicker table tr td.day { height: 40px; font-size: 1.1rem; font-weight: 500; color: #111; transition: all 0.2s; cursor: pointer; }' +
                '.datepicker table tr td.old, .datepicker table tr td.day.disabled { color: #ccc !important; opacity: 0.5; cursor: default; }' +
                '.datepicker table tr td.active { background-color: #1a56db !important; color: #ffffff !important; border-radius: 8px !important; font-weight: 800; }' +
                '.datepicker table tr.active-week td:not(.old):not(.new) { background-color: #eff6ff; }' +
                '.datepicker table tr td.today { color: #1a56db !important; font-weight: 900; }' +
                '@media (max-width: 768px) { .datepicker-inline { padding: 10px !important; } .datepicker table tr td.day { height: 35px; } }';

            $('<style>').attr('id', styleId).prop('type', 'text/css').html(css).appendTo('head');
        },

        events: {
            'click .tmsm-aquatonic-course-booking-time-button': 'selectTime',
            'change .tmsm-aquatonic-course-booking-weekday-times': 'selectTimeWithSelect'
        },

        getHeaderHtml: function() {
            var templateId = 'tmsm-aquatonic-calendar-header';
            if ($('#tmpl-' + templateId).length > 0) {
                try { return wp.template(templateId)(); } catch (e) {}
            }
            return '<div class="tmsm-inline-calendar-wrapper"><div id="tmsm-inline-calendar"></div></div><div id="tmsm-aquatonic-course-booking-weekdays-list"></div>';
        },

        onDateChange: function(date) {
            if (!date) return;
            var selectedDate = moment(date);
            var selectedMonday = selectedDate.clone().startOf('isoWeek');
            if (selectedMonday.isValid()) {
                this.startDateOffset = selectedMonday.diff(moment().startOf('day'), 'days');
                if (App.selectedData) {
                    App.selectedData.set("date", selectedDate.format("YYYY-MM-DD"));
                }
                this.render();
            }
        },

        initDatepicker: function() {
            var self = this;
            var $calendar = $(this.datepickerId);

            if ($.fn.datepicker) {
                if ($calendar.length > 0) {
                    $calendar.datepicker({
                        format: 'dd/mm/yyyy',
                        language: App.data.locale || 'fr',
                        weekStart: 1,
                        todayHighlight: true,
                        startDate: new Date(),
                        todayBtn: "linked"
                    }).on('changeDate', function(e) {
                        self.onDateChange(e.date);
                    });

                    var currentActiveDate = moment().add(this.startDateOffset, 'days').toDate();
                    $calendar.datepicker('setDate', currentActiveDate);
                    
                    // Appliquer la classe active-week après init
                    this.updateWeekHighlight();
                }
            } else {
                setTimeout(function() { self.initDatepicker(); }, 500);
            }
        },

        updateWeekHighlight: function() {
            var $calendar = $(this.datepickerId);
            $calendar.find('tr').removeClass('active-week');
            $calendar.find('td.active').parent().addClass('active-week');
        },

        render: function() {
            if (this.$('#tmsm-inline-calendar').length === 0) {
                this.$el.html(this.getHeaderHtml());
                this.initDatepicker();
            }

            var $list = this.$(this.listElement);
            if ($list.length === 0) {
                this.$el.append('<div id="tmsm-aquatonic-course-booking-weekdays-list"></div>');
                $list = this.$(this.listElement);
            }
            $list.empty();
            
            this.collection.reset();
            if (App.times) {
                App.times.reset();
            }

            for (var i = this.startDateOffset; i < (this.startDateOffset + 7); i++) {
                var day = moment().add(i, 'days');
                this.collection.push({
                    date_label: day.format('dddd Do MMMM'),
                    date_label_firstline: day.format('dddd Do'),
                    date_label_secondline: day.format('MMMM'),
                    date_computed: day.format('YYYY-MM-DD')
                });
            }

            this.collection.each(function(model) {
                var item = new App.WeekDayListItemView({ model: model });
                $list.append(item.render().$el);

                var dateStr = model.get('date_computed');
                var $select = $('select.tmsm-aquatonic-course-booking-weekday-times[data-date="' + dateStr + '"]');
                $select.hide().parent().next().show(); // Loader

                App.times.fetch({
                    data: {
                        date: dateStr,
                        participants: App.participants,
                    },
                    remove: false, // On garde les données des autres jours
                    complete: function() {
                        if ($.fn.selectpicker) {
                            $select.selectpicker('refresh');
                        }
                        $select.show().parent().next().hide();
                    }
                });
            }, this);

            this.updateWeekHighlight();
            return this;
        },

        selectTimeWithSelect: function(event) {
            var $opt = $(event.target.options[event.target.selectedIndex]);
            if (!$opt.data('hourminutes')) {
                App.selectedData.set({ 'hourminutes': null, 'hour': null, 'minutes': null, 'date': null });
                return;
            }
            // On récupère explicitement la date liée à l'option pour synchroniser avec l'horaire
            var selectedDate = $opt.data('date');
            
            App.selectedData.set({
                'hourminutes': $opt.data('hourminutes'),
                'hour': $opt.data('hour'),
                'minutes': $opt.data('minutes'),
                'date': selectedDate
            });
        },

        selectTime: function(event) {
            var $el = $(event.currentTarget);
            $('.tmsm-aquatonic-course-booking-time-button').removeClass('btn-primary disabled selected').addClass('not-selected');
            $el.addClass('btn-primary disabled selected').removeClass('not-selected');

            App.selectedData.set({
                'hourminutes': $el.data('hourminutes'),
                'hour': $el.data('hour'),
                'minutes': $el.data('minutes'),
                'date': $el.data('date')
            });
            
            if (App.selectedDataList && App.selectedDataList.confirmButton) {
                 App.animateTransition($(App.selectedDataList.confirmButton));
            }
        }
    });

    App.WeekDayListItemView = Backbone.View.extend({
        tagName: 'div',
        className: 'tmsm-aquatonic-course-booking-weekday-item',
        template: wp.template('tmsm-aquatonic-course-booking-weekday'),
        render: function() {
            this.$el.html(this.template(this.model.toJSON()));
            return this;
        }
    });

    /**
     * Gestion des données sélectionnées et validation
     */
    App.SelectedDataModel = Backbone.Model.extend({
        defaults: { date: null, hourminutes: null, hour: null, minutes: null }
    });

    App.SelectedDataView = Backbone.View.extend({
        el: '#tmsm-aquatonic-course-booking-confirm-container',
        confirmButton: '.gform_next_button',
        summaryField: '.tmsm-aquatonic-course-summary',

        initialize: function() {
            _.bindAll(this, 'change');
            this.listenTo(this.model, 'change', this.change);
            $(this.confirmButton).hide();
        },

        change: function() {
            var attrs = this.model.attributes;
            if (attrs.hourminutes) {
                this.updateFormFields(attrs);
                this.showConfirm();
            } else {
                this.hideConfirm();
            }
        },

        updateFormFields: function(attrs) {
            var fields = App.form_fields;
            if (fields.date_field) $(fields.date_field).val(attrs.date);
            if (fields.hour_field) $(fields.hour_field).val(attrs.hour);
            if (fields.minutes_field) $(fields.minutes_field).val(attrs.minutes);
            
            if (fields.summary_field) {
                var dayLabel = moment(attrs.date).format('dddd DD MMMM YYYY');
                var summary = (App.i18n.summary || 'Réservation pour %s pers. le %s à %sh%s')
                    .replace('%s', App.participants)
                    .replace('%s', dayLabel)
                    .replace('%s', attrs.hour)
                    .replace('%s', attrs.minutes);
                $(fields.summary_field).html(summary);
            }
        },

        showConfirm: function() {
            $(this.confirmButton).show();
            App.animateTransition($(App.form_fields.summary_field || this.summaryField));
        },

        hideConfirm: function() {
            $(this.confirmButton).hide();
        }
    });

    /**
     * Utilitaires et Initialisation
     */
    App.animateTransition = function($el) {
        if (!$el.length) return;
        $el.show();
        $('html, body').animate({ scrollTop: $el.offset().top - 100 }, 400);
    };

    App.init = function() {
        var $partInput = $('.tmsm-aquatonic-course-participants input');
        App.participants = $partInput.val();
        
        if (App.participants !== '') {
            $('.tmsm-aquatonic-course-times, .tmsm-aquatonic-course-summary').show();
        } else {
            $('.tmsm-aquatonic-course-times, .tmsm-aquatonic-course-summary').hide();
            return;
        }

        App.times = new App.TimesCollection();
        App.timesList = new App.TimesListView({ collection: App.times });

        App.weekdays = new App.WeekDayCollection();
        App.weekdaysList = new App.WeekDayListView({ collection: App.weekdays });
        App.weekdaysList.render();

        App.selectedData = new App.SelectedDataModel();
        App.selectedDataList = new App.SelectedDataView({ model: App.selectedData });
    };

    // DOM Ready logic
    $(function() {
        if ($('.tmsm-aquatonic-course-participants').length > 0) {
            if ($('.tmsm-aquatonic-course-participants input').val() !== '') {
                App.init();
            }

            $('.tmsm-aquatonic-course-participants input').on('keyup input', _.debounce(function() {
                App.init();
            }, 300));
        }

        $('#tmsm-aquatonic-course-booking-download-pdf').on('click', function() {
            $(this).addClass('disabled').text(App.i18n.downloading || 'Chargement...');
        });
    });

})(jQuery);
