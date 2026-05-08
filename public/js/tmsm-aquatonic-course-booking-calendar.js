/**
 * tmsm-aquatonic-course-booking-calendar.js
 * Version: 1.0.0
 *
 * Remplacement vanilla JS du calendrier de réservation (sans Backbone.js / Underscore.js).
 * Dépendances conservées : jQuery (WP core), moment.js, Bootstrap Datepicker.
 */

(function ($) {
    'use strict';

    var App = window.TmsmAquatonicCourseApp || {};

    // ─── État global ──────────────────────────────────────────────────────────
    var state = {
        participants:        0,
        weekOffset:          0,   // nb de jours entre aujourd'hui et le lundi affiché
        selectedDate:        null,
        selectedHourminutes: null,
        selectedHour:        null,
        selectedMinutes:     null,
        calendarReady:       false
    };

    // ─── Sélecteurs DOM ───────────────────────────────────────────────────────
    var SEL = {
        container:    '#tmsm-aquatonic-course-slots-container',
        calendar:     '#tmsm-inline-calendar',
        weekList:     '#tmsm-aquatonic-course-booking-weekdays-list',
        participants: '.tmsm-aquatonic-course-participants input',
        times:        '.tmsm-aquatonic-course-times',
        summary:      '.tmsm-aquatonic-course-summary',
        nextBtn:      '.gform_next_button'
    };

    // ─── Debounce (sans Underscore.js) ────────────────────────────────────────
    function debounce(fn, delay) {
        var timer;
        return function () {
            clearTimeout(timer);
            timer = setTimeout(fn, delay);
        };
    }

    // ─── Scroll vers un élément ───────────────────────────────────────────────
    // requestAnimationFrame garantit que le navigateur a recalculé le layout
    // (reflow) après un show() avant qu'on lise offset().top.
    function scrollToEl($el) {
        if (!$el || !$el.length) return;
        requestAnimationFrame(function () {
            var target = Math.max(0, $el.offset().top - 100);
            if ('scrollBehavior' in document.documentElement.style) {
                window.scrollTo({ top: target, behavior: 'smooth' });
            } else {
                document.documentElement.scrollTop = target;
                document.body.scrollTop = target;
            }
        });
    }

    // ─── Styles injectés ──────────────────────────────────────────────────────
    function injectStyles() {
        var id = 'tmsm-calendar-v2-styles';
        if (document.getElementById(id)) return;

        var rules = [
            '.tmsm-inline-calendar-wrapper{background:#fff;border:1px solid #e2e8f0;border-radius:12px;margin:0 auto 20px;overflow:clip;max-width:1200px;font-family:sans-serif}',
            '.datepicker-inline{width:100%!important;border:none!important;padding:10px 40px!important}',
            '.datepicker table{width:100%!important;border-collapse:separate!important;border-spacing:0 5px!important}',
            '.datepicker .datepicker-switch{font-weight:800;color:#111;font-size:1.2rem;text-transform:uppercase}',
            '.datepicker .prev,.datepicker .next{color:#1a56db!important;font-size:1.5rem;font-weight:bold;cursor:pointer}',
            '.datepicker .dow{color:#444;font-weight:700;font-size:.9rem;text-transform:uppercase;padding:10px 0;border-bottom:2px solid #f1f5f9}',
            '.datepicker table tr td.day{height:40px;font-size:1.1rem;font-weight:500;color:#111;transition:all .2s;cursor:pointer}',
            '.datepicker table tr td.old,.datepicker table tr td.day.disabled{color:#ccc!important;opacity:.5;cursor:default}',
            '.datepicker table tr td.active{background-color:#1a56db!important;color:#fff!important;border-radius:8px!important;font-weight:800}',
            '.datepicker table tr.active-week td:not(.old):not(.new){background-color:#eff6ff}',
            '.datepicker table tr td.today{color:#1a56db!important;font-weight:900}',
            '.tmsm-week-grid{display:flex;gap:8px;flex-wrap:wrap;margin-top:16px}',
            '.tmsm-day-col{flex:1;min-width:120px;border:1px solid #e2e8f0;border-radius:8px;padding:10px;background:#fafafa}',
            '.tmsm-day-col .day-label-top{font-weight:700;font-size:.85rem;text-transform:uppercase;color:#333;margin-bottom:2px}',
            '.tmsm-day-col .day-label-month{font-size:.8rem;color:#666;margin-bottom:8px}',
            '.tmsm-day-col select{width:100%;padding:6px;border:1px solid #ced4da;border-radius:4px;color:#000;background:#fff;font-weight:normal}',
            '.tmsm-day-col select:disabled{background:#f1f5f9;color:#999}',
            '.tmsm-day-spinner{display:none;font-size:.8rem;color:#1a56db;margin-top:4px}',
            '@media(max-width:768px){.tmsm-week-grid{flex-direction:column}.datepicker-inline{padding:10px!important}.datepicker table tr td.day{height:35px}}'
        ];

        var style = document.createElement('style');
        style.id = id;
        style.textContent = rules.join('');
        document.head.appendChild(style);
    }

    // ─── Calendrier (datepicker) ──────────────────────────────────────────────
    function renderCalendar() {
        console.log('Rendering calendar with week offset:', state.weekOffset);
        var $container = $(SEL.container);
        if (!$container.length) return;

        if (!$container.find('.tmsm-inline-calendar-wrapper').length) {
            $container.html(
                '<div class="tmsm-inline-calendar-wrapper">' +
                    '<div id="tmsm-inline-calendar"></div>' +
                '</div>' +
                '<div id="tmsm-aquatonic-course-booking-weekdays-list"></div>'
            );
            initDatepicker();
        }

        renderWeek();
    }

    function initDatepicker() {
        var $cal = $(SEL.calendar);

        if (!$.fn.datepicker) {
            setTimeout(initDatepicker, 500);
            return;
        }

        $cal.datepicker({
            format:         'dd/mm/yyyy',
            language:       (App.data && App.data.locale) || 'fr',
            weekStart:      1,
            todayHighlight: true,
            startDate:      new Date(),
            todayBtn:       'linked'
        }).on('changeDate', function (e) {
            if (!e.date) return;
            var selected = moment(e.date);
            state.weekOffset   = selected.clone().startOf('isoWeek').diff(moment().startOf('day'), 'days');
            state.selectedDate = selected.format('YYYY-MM-DD');
            renderWeek();
        });

        var initialDate = moment().add(state.weekOffset, 'days').toDate();
        $cal.datepicker('setDate', initialDate);
        highlightActiveWeek();
    }

    function highlightActiveWeek() {
        var $cal = $(SEL.calendar);
        $cal.find('tr').removeClass('active-week');
        $cal.find('td.active').closest('tr').addClass('active-week');
    }

    // ─── Grille de la semaine ─────────────────────────────────────────────────
    function renderWeek() {
        clearSelection();

        var $list = $(SEL.weekList);
        $list.empty();

        var grid = document.createElement('div');
        grid.className = 'tmsm-week-grid';

        for (var i = 0; i < 7; i++) {
            var day    = moment().add(state.weekOffset + i, 'days');
            var dateStr = day.format('YYYY-MM-DD');
            grid.appendChild(buildDayColumn(day, dateStr));
        }

        $list.append(grid);
        highlightActiveWeek();

        // 7 appels AJAX en parallèle (contrainte API : pas de paramètre "semaine")
        for (var j = 0; j < 7; j++) {
            fetchSlots(moment().add(state.weekOffset + j, 'days').format('YYYY-MM-DD'));
        }
    }

    function buildDayColumn(day, dateStr) {
        var col = document.createElement('div');
        col.className   = 'tmsm-day-col';
        col.dataset.date = dateStr;

        // Étiquette du jour : ligne 1 = "Lun 07", ligne 2 = "Mai"
        var labelTop = document.createElement('div');
        labelTop.className   = 'day-label-top';
        labelTop.textContent = day.format('ddd DD');

        var labelMonth = document.createElement('div');
        labelMonth.className   = 'day-label-month';
        labelMonth.textContent = day.format('MMMM');

        // Select des créneaux
        var select = document.createElement('select');
        select.className = 'tmsm-aquatonic-course-booking-weekday-times';
        select.dataset.date = dateStr;
        select.disabled  = true;
        select.appendChild(makeOption('', (App.i18n && App.i18n.loading) || 'Chargement…'));
        select.addEventListener('change', onSelectChange);

        // Spinner texte
        var spinner = document.createElement('div');
        spinner.className   = 'tmsm-day-spinner';
        spinner.textContent = (App.i18n && App.i18n.loading) || 'Chargement…';
        spinner.style.display = 'block';

        col.appendChild(labelTop);
        col.appendChild(labelMonth);
        col.appendChild(select);
        col.appendChild(spinner);

        return col;
    }

    function makeOption(value, text) {
        var opt = document.createElement('option');
        opt.value       = value;
        opt.textContent = text;
        return opt;
    }

    // ─── Appel AJAX ───────────────────────────────────────────────────────────
    function fetchSlots(dateStr) {
        $.ajax({
            url:      App.data.ajaxurl,
            method:   'POST',
            dataType: 'json',
            data: {
                action:       'tmsm-aquatonic-course-booking-times',
                nonce:        App.data.nonce,
                date:         dateStr,
                participants: state.participants
            }
        })
        .done(function (slots) { fillDaySelect(dateStr, slots); })
        .fail(function ()       { fillDaySelect(dateStr, null);  });
    }

    function fillDaySelect(dateStr, slots) {
        var $col     = $('.tmsm-day-col[data-date="' + dateStr + '"]');
        var $select  = $col.find('select');
        var $spinner = $col.find('.tmsm-day-spinner');

        $spinner.hide();
        $select.empty();

        var noSlots = !slots || !slots.length || slots[0].hourminutes === null;

        if (noSlots) {
            var msg = (slots && slots[0] && slots[0].message)
                ? slots[0].message
                : ((App.i18n && App.i18n.notimeslot) || 'Aucun créneau');
            $select.append(makeOption('', msg)).prop('disabled', true);
            return;
        }

        $select.append(makeOption('', (App.i18n && App.i18n.pickatimeslot) || 'Choisir un créneau'));

        slots.forEach(function (slot) {
            if (!slot.hourminutes) return;
            var opt = makeOption(slot.hourminutes, slot.hourminutes);
            opt.dataset.date        = slot.date;
            opt.dataset.hour        = slot.hour;
            opt.dataset.minutes     = slot.minutes;
            opt.dataset.hourminutes = slot.hourminutes;
            $select.append(opt);
        });

        $select.prop('disabled', false);

        if ($.fn.selectpicker) {
            $select.selectpicker('refresh');
        }
    }

    // ─── Sélection d'un créneau ───────────────────────────────────────────────
    function onSelectChange(e) {
        var $select = $(e.target);
        var $opt    = $select.find('option:selected');

        // Réinitialiser les autres selects
        $('.tmsm-aquatonic-course-booking-weekday-times').not($select).val('');
        if ($.fn.selectpicker) {
            $('.tmsm-aquatonic-course-booking-weekday-times').not($select).selectpicker('refresh');
        }
        console.log('Selected option:', $opt.val(), $opt.data());

        if (!$opt.val()) {
            clearSelection();
            return;
        }

        applySelection(
            $opt.data('date'),
            $opt.data('hourminutes'),
            $opt.data('hour'),
            $opt.data('minutes')
        );
    }

    function applySelection(date, hourminutes, hour, minutes) {
        state.selectedDate        = date;
        state.selectedHourminutes = hourminutes;
        state.selectedHour        = hour;
        state.selectedMinutes     = minutes;

        var f = App.form_fields || {};

        // Les inputs GF ont l'attribut HTML disabled (classe "disabled" dans GF).
        // Un input disabled n'est pas soumis par le navigateur → on retire disabled avant de remplir.
        if (f.date_field)    $(f.date_field).prop('disabled', false).val(date);
        if (f.hour_field)    $(f.hour_field).prop('disabled', false).val(hour);
        if (f.minutes_field) $(f.minutes_field).prop('disabled', false).val(minutes);

        if (f.summary_field) {
            var dayLabel = moment(date).format('dddd DD MMMM YYYY');
            var tpl = (App.i18n && App.i18n.summary) || 'Réservation pour %s pers. le %s à %sh%s';
            var text = tpl
                .replace('%s', state.participants)
                .replace('%s', dayLabel)
                .replace('%s', hour)
                .replace('%s', minutes);
            $(f.summary_field).html(text).show();
        }
        var $next = $(SEL.nextBtn);
        $next.show();
        scrollToEl($next.length ? $next : $(f.summary_field));
    }

    function clearSelection() {
        state.selectedDate        = null;
        state.selectedHourminutes = null;
        state.selectedHour        = null;
        state.selectedMinutes     = null;

        var f = App.form_fields || {};
        if (f.date_field)    $(f.date_field).val('');
        if (f.hour_field)    $(f.hour_field).val('');
        if (f.minutes_field) $(f.minutes_field).val('');

        $(SEL.nextBtn).hide();
    }

    // ─── Initialisation ───────────────────────────────────────────────────────
    function init() {
        var participants = $(SEL.participants).val();

        if (!participants || participants === '') {
            $(SEL.times + ', ' + SEL.summary).hide();
            $(SEL.nextBtn).hide();
            return;
        }

        state.participants = participants;

        if (!state.calendarReady) {
            injectStyles();

            // Calcul de l'offset de départ (même logique que l'ancien fichier)
            var startDay = moment().add(parseInt((App.data && App.data.daysrangefrom) || 0), 'days').startOf('isoWeek');
            state.weekOffset   = startDay.diff(moment().startOf('day'), 'days');
            state.calendarReady = true;
        }

        $(SEL.times + ', ' + SEL.summary).show();

        renderCalendar();
    }

    // ─── Démarrage ────────────────────────────────────────────────────────────
    $(function () {
        if (!$(SEL.participants).length) return;

        // En GF multipage, le container du calendrier est dans le DOM sur toutes les pages
        // mais caché (display:none) sur les pages suivantes. Si le container n'est pas
        // visible, on est sur la page 2+ → ne pas s'initialiser, sinon clearSelection()
        // viderait les champs date/heure que GF a persistés depuis la page 1.
        if (!$(SEL.container).is(':visible')) return;

        if ($(SEL.participants).val() !== '') {
            init();
        }

        $(SEL.participants).on('keyup input', debounce(init, 300));

        $('#tmsm-aquatonic-course-booking-download-pdf').on('click', function () {
            $(this).addClass('disabled').text((App.i18n && App.i18n.downloading) || 'Chargement…');
        });
    });

})(jQuery);
