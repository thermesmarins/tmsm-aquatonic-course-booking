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

Aquos API Integration
---

The plugin synchronizes booking data and status updates with the Aquos external API.

### Key Methods
*   `aquos_send_contact( $booking )`: Sends the initial contact and booking data to Aquos. Triggered every 5 minutes via cron for bookings with `self = 1`.
*   `aquos_update_status( $booking, $status )`: Updates the reservation status in Aquos. Triggered via admin/Ajax when a booking is marked as 'arrived' or 'cancelled'.
*   `aquos_api_request( $endpoint, $data )`: Private helper centralizing JSON encoding, signature generation (HMAC), and HTTP transmission.
*   `aquos_map_status( $status )`: Centralizes the translation of WordPress statuses to Aquos-specific terminology.

### Configuration & Debugging
*   **Debug Mode**: Enabled via the `TMSM_AQUATONIC_COURSE_BOOKING_DEBUG` constant. When `true`, API transactions and lifecycle events are logged.
*   **Security**: Logs are sanitized to prevent PII (Personally Identifiable Information) leakage. Full payload dumps are strictly avoided in production-ready code.
*   **Site ID**: Supports numeric IDs including `0` (used for specific multisite branches like Rennes).

### Log Monitoring
Transactions can be monitored in `wp-content/tmsm-error.log` (or Docker stdout). Look for markers like `Aquos API Request` and `Aquos API Response Code`.

