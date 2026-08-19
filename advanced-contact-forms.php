<?php
/**
 * Plugin Name: Advanced Contact Forms
 * Plugin URI: https://github.com/pavelsilinskiiwork/advanced-contact-forms
 * Description: Advanced contact forms with database storage, email notifications, REST API and CSV export.
 * Version: 1.0.0
 * Author: Pavel Silinskii
 * Author URI: https://linkedin.com/in/pavel-silinskii
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: advanced-contact-forms
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ACF_VERSION', '1.0.0');
define('ACF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ACF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ACF_PLUGIN_FILE', __FILE__);

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'ACF\\';
    $base_dir = ACF_PLUGIN_DIR . 'includes/';

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
function acf_init(): void
{
    $plugin = new ACF\Core\Plugin();
    $plugin->init();
}

add_action('plugins_loaded', 'acf_init');

// Activation hook
register_activation_hook(__FILE__, function () {
    ACF\Core\Installer::activate();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function () {
    ACF\Core\Installer::deactivate();
});