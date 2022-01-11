TMSM Aquatonic Course Booking
======================

Allow Aquatonic Course Booking 

The plugin requires:
---
* Gravity Forms plugin 2.4.0+ for a create booking form and a cancel booking form

Shortcodes:
---
* `[tmsm_aquatonic_course_booking_remainingdays_left]` displays remaining days to book or the date when booking will be available at a later date.

Tests
---
Change all bookings to current date and make them active (use carefully)
`UPDATE {$wpdb->prefix}aquatonic_course_booking  SET status= 'active', course_start = CONCAT(CURDATE(), ' ', TIME(course_start)), course_end = CONCAT(CURDATE(),' ', TIME(course_end))`
