<?php

namespace PSCF\Core;

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
            $admin = new \PSCF\Admin\AdminMenu();
            $admin->init();
        }

        // REST API
        $api = new \PSCF\Api\RestApi();
        $api->init();

        // Frontend
        $frontend = new \PSCF\Frontend\Shortcode();
        $frontend->init();
    }
}