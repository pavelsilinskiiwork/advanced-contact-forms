<?php

namespace ACF\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Plugin
{
    public function init(): void
    {
        $this->loadTextDomain();
        $this->initComponents();
    }

    private function loadTextDomain(): void
    {
        load_plugin_textdomain(
            'contact-forms-by-pavel-silinskii',
            false,
            dirname(plugin_basename(PSCF_PLUGIN_FILE)) . '/languages'
        );
    }

    private function initComponents(): void
    {
        // Admin
        if (is_admin()) {
            $admin = new \ACF\Admin\AdminMenu();
            $admin->init();
        }

        // REST API
        $api = new \ACF\Api\RestApi();
        $api->init();

        // Frontend
        $frontend = new \ACF\Frontend\Shortcode();
        $frontend->init();
    }
}