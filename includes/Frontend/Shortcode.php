<?php

namespace PavelSilinskii\ContactForms\Frontend;

if (!defined('ABSPATH')) {
    exit;
}

class Shortcode
{
    public function init(): void
    {
        add_shortcode('contact_form', [$this, 'renderForm']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function enqueueAssets(): void
    {
        wp_enqueue_style(
            'pavel-silinskii-contact-forms-frontend',
            PAVEL_SILINSKII_CONTACT_FORMS_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            PAVEL_SILINSKII_CONTACT_FORMS_VERSION
        );

        wp_enqueue_script(
            'pavel-silinskii-contact-forms-frontend',
            PAVEL_SILINSKII_CONTACT_FORMS_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            PAVEL_SILINSKII_CONTACT_FORMS_VERSION,
            true
        );

        wp_localize_script('pavel-silinskii-contact-forms-frontend', 'pavelSilinskiiContactFormsFrontend', [
            'restUrl' => rest_url('pavelsilinskii-cf/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }

    public function renderForm(array $atts): string
    {
        $atts = shortcode_atts(['id' => 0], $atts);
        $form_id = intval($atts['id']);

        if (!$form_id) {
            return '<p class="acf-error">Please specify a form ID.</p>';
        }

        $form = \PavelSilinskii\ContactForms\Core\Database::getForm($form_id);

        if (!$form || !$form['is_active']) {
            return '<p class="acf-error">Form not found or inactive.</p>';
        }

        $fields = json_decode($form['fields'], true) ?? [];

        ob_start();
        include PAVEL_SILINSKII_CONTACT_FORMS_PLUGIN_DIR . 'templates/frontend/form.php';
        return ob_get_clean();
    }
}