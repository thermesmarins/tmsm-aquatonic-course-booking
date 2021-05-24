* Times dropdown must be above header in fixed position 
* Admin: Better label on change booking status button
* Front: Only load times when on step 2
* Fix no distraction mode after Elementor footer update
* Enqueue min JS file for production
* New form field in add form: summary to display selected summary of the booking before confirming
* Smooth transition to summary field after selecting time

### 1.4.2: May 19th, 2021
* No Distraction for StormBringer theme
* Fix $el.content for WordPress 5.7
* Allow shop_manager role to access the admin pages
* Remove update bookings query for testing (too dangerous for production)

### 1.4.1: May 04th, 2021
* New fields for testing: Real Time Attendance, Lessons Date
* Calculate dashboard with lessons members
* Rename capacity for allotment
* New dashboard colors for allotment

### 1.4.0: April 14th, 2021
* Gravity Forms: Add WooCommerce styling to message content
* Gravity Forms: Customize email notification (headers, message, to, subject) to use WooCommerce CSS styling
* New GF merge tag for barcode block
* Add custom CSS to emails for dark mode
* New setting: Dialog Insight Source Code
* New settings: Aquos endpoints and site ID
* Aquos: send contact information with web service request (with signature)
* New booking field: title (Miss or Mr)
* New database version number: 3

### 1.3.0: April 13th, 2021
* Gravity Forms: Use WooCommerce email templates for notifications
* Barcode logo round and white
* Email markup for EventReservation (Barcode needs testing)
* New settings for add booking and cancel booking pages
* Barcode logo for Aquatonic
* New hook for adding GF entry into custom database: gf_entry_saved instead of gform_after_submission
* Barcode white border (with background-image)

### 1.2.0: April 7th, 2021
* Add vendor php-barcode-generator
* Action to generate a barcode image from a barcode number
* Gravity Forms merge tag for barcode image action URL
* Gravity Forms merge tag for barcode number

### 1.1.2: April 6th, 2021
* Fix fill booking_id when changing status
* Fix sending JSON data with default function, not error or success  
* Remove Gravity Forms overflow hidden
* better Dialog Insight settings control

### 1.1.1: April 5th, 2021
* Add barcode field to course sql table
* Barcode scan JSOn returns error messages

### 1.1.0: March 9th, 2021
* Dialog Insight API refactoring
* Dialog Insight: mark booking as arrived
* Attendance counter: include jQuery Countdown library
* Attendance counter: minutes until next refresh of the cron event
* Attendance counter: refresh button to force run of cron event
* Check Dialog Insight API connection on saving settings
* Check Gravity Forms settings (classes, input names, feeds) on saving

### 1.0.1: March 1st, 2021
* Encode token in URL
* Update forms CSS styling
* Update FR translation

### 1.0.0: February 26th, 2021
* First version