<?php

namespace ACF\Admin;

class AdminMenu
{
    public function init(): void
    {
        add_action('admin_menu', [$this, 'registerMenus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('wp_ajax_acf_save_form', [$this, 'ajaxSaveForm']);
        add_action('wp_ajax_acf_delete_form', [$this, 'ajaxDeleteForm']);
        add_action('wp_ajax_acf_export_csv', [$this, 'exportCsv']);
    }

    public function registerMenus(): void
    {
        add_menu_page(
            __('Contact Forms', 'advanced-contact-forms'),
            __('Contact Forms', 'advanced-contact-forms'),
            'manage_options',
            'advanced-contact-forms',
            [$this, 'renderFormsPage'],
            'dashicons-email-alt',
            30
        );

        add_submenu_page(
            'advanced-contact-forms',
            __('All Forms', 'advanced-contact-forms'),
            __('All Forms', 'advanced-contact-forms'),
            'manage_options',
            'advanced-contact-forms',
            [$this, 'renderFormsPage']
        );

        add_submenu_page(
            'advanced-contact-forms',
            __('Add New Form', 'advanced-contact-forms'),
            __('Add New', 'advanced-contact-forms'),
            'manage_options',
            'acf-new-form',
            [$this, 'renderFormEditor']
        );

        add_submenu_page(
            'advanced-contact-forms',
            __('Settings', 'advanced-contact-forms'),
            __('Settings', 'advanced-contact-forms'),
            'manage_options',
            'acf-settings',
            [$this, 'renderSettings']
        );
    }

    public function enqueueAssets(string $hook): void
    {
        if (!str_contains($hook, 'advanced-contact-forms') &&
            !str_contains($hook, 'acf-')) {
            return;
        }

        wp_enqueue_style(
            'acf-admin',
            ACF_PLUGIN_URL . 'assets/css/admin.css',
            [],
            ACF_VERSION
        );

       wp_enqueue_script(
    'acf-admin',
    ACF_PLUGIN_URL . 'assets/js/admin.js',
    ['jquery', 'jquery-ui-sortable'], 
    ACF_VERSION,
    true
);

        wp_localize_script('acf-admin', 'acfAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('acf_admin_nonce'),
            'strings' => [
                'confirmDelete' => __('Are you sure you want to delete this form?', 'advanced-contact-forms'),
                'saved' => __('Form saved successfully!', 'advanced-contact-forms'),
                'error' => __('An error occurred. Please try again.', 'advanced-contact-forms'),
            ],
        ]);
    }

    public function renderFormsPage(): void
    {
        $forms = \ACF\Core\Database::getForms();
        include ACF_PLUGIN_DIR . 'templates/admin/forms-list.php';
    }

    public function renderFormEditor(): void
    {
        $form_id = intval($_GET['form_id'] ?? 0);
        $form = $form_id ? \ACF\Core\Database::getForm($form_id) : null;
        if ($form) {
            $form['fields'] = json_decode($form['fields'], true) ?? [];
        }
        include ACF_PLUGIN_DIR . 'templates/admin/form-editor.php';
    }

    public function renderSettings(): void
    {
        if (isset($_POST['acf_save_settings']) &&
            check_admin_referer('acf_settings_nonce')) {
            update_option('acf_email_from', sanitize_email($_POST['email_from']));
            update_option('acf_spam_protection', isset($_POST['spam_protection']) ? '1' : '0');
            echo '<div class="notice notice-success"><p>' .
                esc_html__('Settings saved.', 'advanced-contact-forms') .
                '</p></div>';
        }
        include ACF_PLUGIN_DIR . 'templates/admin/settings.php';
    }

    public function ajaxSaveForm(): void
    {
        check_ajax_referer('acf_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }

        $data = [
            'name' => sanitize_text_field($_POST['name'] ?? ''),
            'description' => sanitize_textarea_field($_POST['description'] ?? ''),
            'fields' => json_decode(stripslashes($_POST['fields'] ?? '[]'), true),
            'email_to' => sanitize_email($_POST['email_to'] ?? ''),
            'email_subject' => sanitize_text_field($_POST['email_subject'] ?? ''),
            'success_message' => sanitize_textarea_field($_POST['success_message'] ?? ''),
            'is_active' => intval($_POST['is_active'] ?? 1),
        ];

        if (empty($data['name'])) {
            wp_send_json_error(['message' => 'Form name is required']);
        }

        $form_id = intval($_POST['form_id'] ?? 0);

        if ($form_id) {
            \ACF\Core\Database::updateForm($form_id, $data);
            wp_send_json_success(['message' => 'Form updated', 'form_id' => $form_id]);
        } else {
            $new_id = \ACF\Core\Database::createForm($data);
            wp_send_json_success(['message' => 'Form created', 'form_id' => $new_id]);
        }
    }

    public function ajaxDeleteForm(): void
    {
        check_ajax_referer('acf_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }

        $form_id = intval($_POST['form_id'] ?? 0);

        if (!$form_id) {
            wp_send_json_error(['message' => 'Invalid form ID']);
        }

        \ACF\Core\Database::deleteForm($form_id);
        wp_send_json_success(['message' => 'Form deleted']);
    }

    public function exportCsv(): void
    {
        check_ajax_referer('acf_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $form_id = intval($_GET['form_id'] ?? 0);
        $form = \ACF\Core\Database::getForm($form_id);

        if (!$form) {
            wp_die('Form not found');
        }

        $submissions = \ACF\Core\Database::getAllSubmissionsForExport($form_id);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="submissions-' . $form_id . '-' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // Headers
        fputcsv($output, ['ID', 'Date', 'IP Address', 'Status', 'Data']);

        foreach ($submissions as $submission) {
            $data = json_decode($submission['data'], true);
            fputcsv($output, [
                $submission['id'],
                $submission['created_at'],
                $submission['ip_address'],
                $submission['status'],
                wp_json_encode($data),
            ]);
        }

        fclose($output);
        exit;
    }
}