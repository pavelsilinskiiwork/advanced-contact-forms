<?php

namespace PavelSilinskii\ContactForms\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Plugin
{
    public function init(): void
    {
        $this->initComponents();
    }

    private function initComponents(): void
    {
        // Admin
        if (is_admin()) {
            $admin = new \PavelSilinskii\ContactForms\Admin\AdminMenu();
            $admin->init();
        }

        // REST API
        $api = new \PavelSilinskii\ContactForms\Api\RestApi();
        $api->init();

        // Frontend
        $frontend = new \PavelSilinskii\ContactForms\Frontend\Shortcode();
        $frontend->init();
    }
}