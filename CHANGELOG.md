* Optimized forms CSS
* Fixed status labels when WooCommerce is not enabled
* Fix cancel form without explaination 
* Filter bookings by arrived status
* Better UX
* Display "Closed" when allotment is null
* New loading icon
* "If you can't see the barcode" URL now redirects to cancel page

### 1.6.2: June 22th, 2021
* Optimized email CSS
* Booking confirmation email now has a link to access barcode
* New cancel booking page, with barcode

### 1.6.1: June 22th, 2021
* Remove error logs
* Fix bookings query with prepare, that prevented case insensitive searches
* Prevent URL too long in bookings list search
* Display success message after changing booking status

### 1.6.0: June 17th, 2021
* Remove special chars from tokens
* Aquos contact feedback with name and booking date
* Due to problem with Aquos webservice, the contacts are now sent with a cron every 5 minutes, and not through the gravity form submission

### 1.5.0: June 15th, 2021
* Fix filter bookings query
* Adding debug information
* Better bookings search allowing to search only by name
* Removed jquery mask for birthdate
* Better handling of birthdate (need to create another GF field, and update StormBringer theme)
* Confirm prompt for cancellation button

### 1.4.9: June 14th, 2021
* Removed link to dashboard from minidashboard

### 1.4.8: June 1st, 2021
* Remove debug log
* Updated translations
* Removed dangerous queries
* Bookings list in backend: Cancel button 

### 1.4.7: June 1st, 2021
* Treatments allotment

### 1.4.6: June 1st, 2021
* Remove debug
* Updated translations
* Fix realtime value when negative
* Better view of bookings, with filters: name, course date, creation date

### 1.4.5: May 31th, 2021
* Automatic refresh of data now executed one minute after cron event has executed, to let time to do the actions
* Mini dashboard: Remove javascript, fix margins, add link to main dashboard
* Fix missing aquos_generate_signature function
* Prevent non-admins from seeing dashboard calculations
* New setting: Blocked before date
* Allow 0 for text settings

### 1.4.4: May 27th, 2021
* Dialog Insight: Make birthdate value optional when sending to web service
* Markup "modifyUrl" now goes to cancel page, not contact page
* Require wp-i18n for using sprintf in javascript
* "Pick a timeslot" before the timeslots in the select menu
* Format phone for Dialog Insight
* Fix title info was not submitted to Dialog Insight web service
* Fix capability "aquatonic_course" for shop_manager
* Fix dropdown menu buttons margins
* Fix dashboard now values now only use capacity and realtime, not bookings
* Settings page now restricted to administrators 
* Fix timeslots, remove the first option "Pick a timeslot" to let appear first "No timeslot available"
* Send contact info to Aquos when submitting booking (not when status=arrived anymore)
* Mini dashboard

### 1.4.3: May 24th, 2021
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