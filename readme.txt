=== Advanced Contact Forms ===
Contributors: pavelsilinskii
Tags: contact form, form builder, rest api, csv export, email notifications
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A powerful contact form plugin with a drag-and-drop builder, database storage, email notifications, REST API, and CSV export of submissions.

== Description ==

Advanced Contact Forms lets you build and manage contact forms without writing any code. Every submission is stored in the WordPress database, so nothing is lost if an email notification fails to arrive.

= Features =

* **Visual Form Builder** — drag-and-drop interface to create forms without coding
* **7 Field Types** — Text, Email, Phone, Textarea, Select, Checkbox, Radio
* **Database Storage** — all submissions saved to the WordPress database
* **Email Notifications** — automatic email sent on every form submission
* **REST API** — full API for headless WordPress and external integrations
* **CSV Export** — export all submissions to CSV with one click
* **Shortcode** — embed forms anywhere with `[contact_form id="1"]`
* **Spam Protection** — built-in honeypot anti-spam system
* **Admin Panel** — clean, intuitive interface for managing forms and submissions
* **Multiple Forms** — create unlimited forms for different purposes
* **Active/Inactive** — enable or disable forms without deleting them

= REST API =

The plugin registers a REST API namespace at `pavelsilinskii-cf/v1` with endpoints to list forms, fetch a single form's fields, submit a form, and (for admins) read submissions.

= Hooks & Filters =

`do_action('acf_form_submitted', $submission_id, $form_id, $data);`

`apply_filters('acf_form_fields', $fields, $form_id);`

`apply_filters('acf_submission_data', $data, $form_id);`

== Installation ==

1. Upload the `advanced-contact-forms` folder to `/wp-content/plugins/`, or install the plugin directly through the WordPress Plugins screen.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Contact Forms** in the admin menu to create your first form.
4. Add fields, configure the notification email, and click **Save Form**.
5. Copy the shortcode (e.g. `[contact_form id="1"]`) and paste it into any page or post.

== Frequently Asked Questions ==

= How do I embed a form? =

Use the shortcode `[contact_form id="1"]` on any page, post, or widget, replacing `1` with your form's ID.

= Where are submissions stored? =

Submissions are stored in dedicated database tables (`{prefix}_acf_forms` and `{prefix}_acf_submissions`), separate from WordPress core tables.

= Can I export submissions? =

Yes. Go to **Contact Forms → All Forms** and click the **CSV** button next to any form with submissions.

= Does it protect against spam? =

Yes, a honeypot field is added to every form. It's invisible to real visitors but catches bots that auto-fill every field. Honeypot submissions are silently discarded. This can be toggled in **Contact Forms → Settings**.

= Is there a REST API? =

Yes. See the Description tab for the endpoint list. The `pavelsilinskii-cf/v1` namespace exposes endpoints to list forms, read a form's fields, submit a form, and (for admins) read submissions.

== Screenshots ==

1. Admin — Forms list with submission counts, shortcodes, and quick actions.
2. Admin — Drag-and-drop form editor.
3. Frontend — Contact form rendered by the shortcode.
4. Admin — Submissions view with CSV export.

== Changelog ==

= 1.0.0 =
* Initial release.
* Visual drag-and-drop form builder.
* 7 field types.
* Database storage for submissions.
* Email notifications.
* REST API with 4 endpoints.
* CSV export.
* Shortcode `[contact_form id="X"]`.
* Honeypot spam protection.
* Admin panel with forms list and editor.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
