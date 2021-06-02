TMSM Aquatonic Course Booking
======================

Allow Aquatonic Booking 

The plugin requires:
* Gravity Forms plugin 2.4.0+ for a create booking form and a cancel booking form
* TMSM Gravity Forms Dialog Insight 1.0.6+ with a feed for contact merge and a feed for offers consent

* For testing: change all bookings to current date and make them active (use carefully)
UPDATE {$wpdb->prefix}aquatonic_course_booking  SET status= 'active', course_start = CONCAT(CURDATE(), ' ', TIME(course_start)), course_end = CONCAT(CURDATE(),' ', TIME(course_end))