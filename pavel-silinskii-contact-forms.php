<?php
/**
 * Plugin Name: Pavel Silinskii Contact Forms
 * Plugin URI: https://github.com/pavelsilinskiiwork/advanced-contact-forms
 * Description: Advanced contact forms with database storage, email notifications, REST API and CSV export.
 * Version: 1.0.0
 * Requires at least: 5.9
 * Author: Pavel Silinskii
 * Author URI: https://linkedin.com/in/pavel-silinskii
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pavel-silinskii-contact-forms
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PAVEL_SILINSKII_CONTACT_FORMS_VERSION', '1.0.0');
define('PAVEL_SILINSKII_CONTACT_FORMS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PAVEL_SILINSKII_CONTACT_FORMS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PAVEL_SILINSKII_CONTACT_FORMS_PLUGIN_FILE', __FILE__);

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'PavelSilinskii\ContactForms\\';
    $base_dir = PAVEL_SILINSKII_CONTACT_FORMS_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize plugin
function pavel_silinskii_contact_forms_init(): void
{
    $plugin = new PavelSilinskii\ContactForms\Core\Plugin();
    $plugin->init();
}

add_action('plugins_loaded', 'pavel_silinskii_contact_forms_init');

// Activation hook
register_activation_hook(__FILE__, function () {
    PavelSilinskii\ContactForms\Core\Installer::activate();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function () {
    PavelSilinskii\ContactForms\Core\Installer::deactivate();
});