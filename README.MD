# Contact Forms by Pavel Silinskii

![WordPress](https://img.shields.io/badge/WordPress-5.9%2B-21759B?logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)
![License](https://img.shields.io/badge/License-GPL%20v2-green)
![Version](https://img.shields.io/badge/Version-1.0.0-blue)

A powerful WordPress plugin for creating and managing contact forms with database storage, email notifications, REST API integration, drag-and-drop form builder, and CSV export.

---

## Table of Contents

- [Features](#features)
- [Screenshots](#screenshots)
- [Installation](#installation)
- [Usage](#usage)
- [REST API](#rest-api)
- [Shortcode](#shortcode)
- [Field Types](#field-types)
- [CSV Export](#csv-export)
- [Spam Protection](#spam-protection)
- [Project Structure](#project-structure)
- [Requirements](#requirements)

---

## Features

- **Visual Form Builder** — drag-and-drop interface to create forms without coding
- **7 Field Types** — Text, Email, Phone, Textarea, Select, Checkbox, Radio
- **Database Storage** — all submissions saved to WordPress database
- **Email Notifications** — automatic email sent on every form submission
- **REST API** — full API for headless WordPress and external integrations
- **CSV Export** — export all submissions to CSV with one click
- **Shortcode** — embed forms anywhere with `[contact_form id="1"]`
- **Spam Protection** — built-in honeypot anti-spam system
- **Admin Panel** — clean, intuitive interface for managing forms and submissions
- **Multiple Forms** — create unlimited forms for different purposes
- **Active/Inactive** — enable or disable forms without deleting them

---

## Screenshots

### Admin — Forms List
Clean overview of all forms with submission count, shortcode, and quick actions.

### Admin — Form Editor
Drag-and-drop field builder with live preview of field order. Add any field type with one click.

### Frontend — Contact Form
Clean, responsive form design that works with any WordPress theme.

### Submissions
View all form submissions directly in the admin, or export to CSV.

---

## Installation

### Manual Installation

1. Download or clone this repository
2. Upload the `contact-forms-by-pavel-silinskii` folder to `/wp-content/plugins/`
3. Activate the plugin in **WordPress Admin → Plugins**
4. Go to **Contact Forms** in the admin menu to create your first form

### Via Git

```bash
cd wp-content/plugins
git clone https://github.com/pavelsilinskiiwork/advanced-contact-forms.git
```

Then activate in WordPress Admin → Plugins.

---

## Usage

### Creating a Form

1. Go to **Contact Forms → Add New**
2. Enter a form name and configure settings:
   - **Send Notifications To** — email address for submission notifications
   - **Email Subject** — subject line for notification emails
   - **Success Message** — message shown after successful submission
3. Add fields using the sidebar buttons or **+ Add Field**
4. Drag fields to reorder them
5. Click **Save Form**
6. Copy the shortcode and paste it into any page or post

### Embedding a Form

Use the shortcode on any page, post, or widget:

```
[contact_form id="1"]
```

Replace `1` with your form's ID.

### Viewing Submissions

1. Go to **Contact Forms → All Forms**
2. The **Submissions** column shows the count for each form
3. Click **CSV** to export all submissions

---

## REST API

The plugin registers a REST API namespace at `pavelsilinskii-cf/v1`.

### Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/wp-json/pavelsilinskii-cf/v1/forms` | Admin | List all forms |
| GET | `/wp-json/pavelsilinskii-cf/v1/forms/{id}` | Public | Get form fields |
| POST | `/wp-json/pavelsilinskii-cf/v1/forms/{id}/submit` | Public | Submit a form |
| GET | `/wp-json/pavelsilinskii-cf/v1/forms/{id}/submissions` | Admin | Get submissions |

### Submit a Form via API

```bash
curl -X POST https://yoursite.com/wp-json/pavelsilinskii-cf/v1/forms/1/submit \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: your_nonce" \
  -d '{
    "full_name": "Pavel Silinskii",
    "email_address": "pavel@example.com",
    "message": "Hello from the API!"
  }'
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Thank you! We will get back to you shortly.",
    "submission_id": 1
}
```

**Validation Error (422):**
```json
{
    "code": "validation_failed",
    "message": "Validation failed",
    "data": {
        "status": 422,
        "errors": {
            "email_address": "Invalid email address",
            "message": "Message is required"
        }
    }
}
```

### Get Form Fields

```bash
curl https://yoursite.com/wp-json/pavelsilinskii-cf/v1/forms/1
```

**Response:**
```json
{
    "id": 1,
    "name": "Contact Us",
    "fields": [
        {
            "label": "Full Name",
            "name": "full_name",
            "type": "text",
            "placeholder": "Full name",
            "required": true
        },
        {
            "label": "Email Address",
            "name": "email_address",
            "type": "email",
            "required": true
        }
    ],
    "success_message": "Thank you! We will get back to you shortly."
}
```

### Get Submissions (Admin Only)

```bash
curl https://yoursite.com/wp-json/pavelsilinskii-cf/v1/forms/1/submissions \
  -H "X-WP-Nonce: your_admin_nonce"
```

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "form_id": 1,
            "data": {
                "full_name": "Pavel Silinskii",
                "email_address": "pavel@example.com",
                "message": "Hello!"
            },
            "ip_address": "127.0.0.1",
            "status": "new",
            "created_at": "2026-08-19 12:00:00"
        }
    ],
    "total": 1,
    "pages": 1,
    "current_page": 1
}
```

---

## Shortcode

Basic usage:
```
[contact_form id="1"]
```

The shortcode renders a fully functional form with:
- All configured fields
- Client-side and server-side validation
- AJAX submission (no page reload)
- Success/error messages
- Spam protection

---

## Field Types

| Type | Description | Options |
|------|-------------|---------|
| `text` | Single line text input | Placeholder, Required |
| `email` | Email input with validation | Placeholder, Required |
| `phone` | Phone number input | Placeholder, Required |
| `textarea` | Multi-line text area | Placeholder, Required |
| `select` | Dropdown with options | Options list, Required |
| `checkbox` | Single checkbox | Label text, Required |
| `radio` | Radio button group | Options list, Required |

---

## CSV Export

Export all submissions for any form:

1. Go to **Contact Forms → All Forms**
2. Click the **CSV** button next to any form with submissions
3. The file downloads automatically with:
   - Submission ID
   - Date and time
   - IP address
   - Status
   - All field values as JSON

**CSV filename format:** `submissions-{form_id}-{date}.csv`

---

## Spam Protection

The plugin uses a **honeypot** technique:
- A hidden field named `website` is added to every form
- Real users never see or fill this field
- Bots that auto-fill forms will trigger the honeypot
- Honeypot submissions are silently accepted but not saved

Enable/disable in **Contact Forms → Settings → Spam Protection**.

---

## Project Structure

```
contact-forms-by-pavel-silinskii/
├── contact-forms-by-pavel-silinskii.php    # Main plugin file
├── includes/
│   ├── Core/
│   │   ├── Plugin.php            # Plugin bootstrap
│   │   ├── Installer.php         # Activation/deactivation, DB tables
│   │   └── Database.php          # Database queries
│   ├── Admin/
│   │   └── AdminMenu.php         # Admin panel, AJAX handlers
│   ├── Api/
│   │   └── RestApi.php           # REST API endpoints
│   └── Frontend/
│       └── Shortcode.php         # [contact_form] shortcode
├── templates/
│   ├── admin/
│   │   ├── forms-list.php        # Forms overview page
│   │   ├── form-editor.php       # Form builder page
│   │   └── settings.php          # Plugin settings page
│   └── frontend/
│       └── form.php              # Frontend form template
├── assets/
│   ├── css/
│   │   ├── admin.css             # Admin styles
│   │   └── frontend.css          # Frontend form styles
│   └── js/
│       ├── admin.js              # Admin JS (form builder, AJAX)
│       └── frontend.js           # Frontend JS (form submission)
└── README.md
```

---

## Database Tables

The plugin creates two tables on activation:

### `{prefix}pscf_forms`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | Form name |
| description | text | Form description |
| fields | longtext | JSON array of field configs |
| email_to | varchar | Notification recipient |
| email_subject | varchar | Email subject |
| success_message | text | Message shown after submission |
| is_active | tinyint | Form active status |
| created_at | datetime | Creation timestamp |
| updated_at | datetime | Last update timestamp |

### `{prefix}pscf_submissions`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| form_id | bigint | Reference to form |
| data | longtext | JSON of submitted values |
| ip_address | varchar | Submitter IP |
| user_agent | text | Browser user agent |
| status | varchar | Submission status |
| created_at | datetime | Submission timestamp |

---

## Requirements

- WordPress 5.9+
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+

---

## Hooks & Filters

```php
// Runs after successful form submission
do_action('pscf_form_submitted', $submission_id, $form_id, $data);

// Filter form fields before rendering
apply_filters('pscf_form_fields', $fields, $form_id);

// Filter submission data before saving
apply_filters('pscf_submission_data', $data, $form_id);
```

---

## Changelog

### 1.0.0 — 2026-08-19
- Initial release
- Visual drag-and-drop form builder
- 7 field types
- Database storage for submissions
- Email notifications
- REST API with 4 endpoints
- CSV export
- Shortcode `[contact_form id="X"]`
- Honeypot spam protection
- Admin panel with forms list and editor

---

## About the Developer

Built by **Pavel Silinskii** — Full-Stack PHP Developer with 15+ years of experience building WordPress plugins, e-commerce platforms, and web applications.

- GitHub: [github.com/pavelsilinskiiwork](https://github.com/pavelsilinskiiwork)
- LinkedIn: [linkedin.com/in/pavel-silinskii](https://linkedin.com/in/pavel-silinskii)
- Contra: [contra.com/pavel-silinskii](https://contra.com/pavel-silinskii)

---

## License

Licensed under the [GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html).
