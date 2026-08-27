<?php

namespace PSCF\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class AdminMenu
{
    public function init(): void
    {
        add_action('admin_menu', [$this, 'registerMenus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('wp_ajax_pscf_save_form', [$this, 'ajaxSaveForm']);
        add_action('wp_ajax_pscf_delete_form', [$this, 'ajaxDeleteForm']);
        add_action('wp_ajax_pscf_export_csv', [$this, 'exportCsv']);
    }

    public function registerMenus(): void
    {
        add_menu_page(
            __('Contact Forms', 'contact-forms-by-pavel-silinskii'),
            __('Contact Forms', 'contact-forms-by-pavel-silinskii'),
            'manage_options',
            'contact-forms-by-pavel-silinskii',
            [$this, 'renderFormsPage'],
            'dashicons-email-alt',
            30
        );

        add_submenu_page(
            'contact-forms-by-pavel-silinskii',
            __('All Forms', 'contact-forms-by-pavel-silinskii'),
            __('All Forms', 'contact-forms-by-pavel-silinskii'),
            'manage_options',
            'contact-forms-by-pavel-silinskii',
            [$this, 'renderFormsPage']
        );

        add_submenu_page(
            'contact-forms-by-pavel-silinskii',
            __('Add New Form', 'contact-forms-by-pavel-silinskii'),
            __('Add New', 'contact-forms-by-pavel-silinskii'),
            'manage_options',
            'pscf-new-form',
            [$this, 'renderFormEditor']
        );

        add_submenu_page(
            'contact-forms-by-pavel-silinskii',
            __('Settings', 'contact-forms-by-pavel-silinskii'),
            __('Settings', 'contact-forms-by-pavel-silinskii'),
            'manage_options',
            'pscf-settings',
            [$this, 'renderSettings']
        );
    }

    public function enqueueAssets(string $hook): void
    {
        if (!str_contains($hook, 'contact-forms-by-pavel-silinskii') &&
            !str_contains($hook, 'pscf-')) {
            return;
        }

        wp_enqueue_style(
            'pscf-admin',
            PSCF_PLUGIN_URL . 'assets/css/admin.css',
            [],
            PSCF_VERSION
        );

       wp_enqueue_script(
    'pscf-admin',
    PSCF_PLUGIN_URL . 'assets/js/admin.js',
    ['jquery', 'jquery-ui-sortable'],
    PSCF_VERSION,
    true
);

        wp_localize_script('pscf-admin', 'pscfAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pscf_admin_nonce'),
            'strings' => [
                'confirmDelete' => __('Are you sure you want to delete this form?', 'contact-forms-by-pavel-silinskii'),
                'saved' => __('Form saved successfully!', 'contact-forms-by-pavel-silinskii'),
                'error' => __('An error occurred. Please try again.', 'contact-forms-by-pavel-silinskii'),
            ],
        ]);
    }

    public function renderFormsPage(): void
    {
        $forms = \PSCF\Core\Database::getForms();
        include PSCF_PLUGIN_DIR . 'templates/admin/forms-list.php';
    }

    public function renderFormEditor(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only: just selects which form to display on this manage_options-gated page, no state change.
        $form_id = intval($_GET['form_id'] ?? 0);
        $form = $form_id ? \PSCF\Core\Database::getForm($form_id) : null;
        if ($form) {
            $form['fields'] = json_decode($form['fields'], true) ?? [];
        }
        include PSCF_PLUGIN_DIR . 'templates/admin/form-editor.php';
    }

    public function renderSettings(): void
    {
        if (isset($_POST['pscf_save_settings']) &&
            check_admin_referer('pscf_settings_nonce')) {
            update_option('pscf_email_from', sanitize_email(wp_unslash($_POST['email_from'] ?? '')));
            update_option('pscf_spam_protection', isset($_POST['spam_protection']) ? '1' : '0');
            echo '<div class="notice notice-success"><p>' .
                esc_html__('Settings saved.', 'contact-forms-by-pavel-silinskii') .
                '</p></div>';
        }
        include PSCF_PLUGIN_DIR . 'templates/admin/settings.php';
    }

    public function ajaxSaveForm(): void
    {
        check_ajax_referer('pscf_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }

        $data = [
            'name' => sanitize_text_field(wp_unslash($_POST['name'] ?? '')),
            'description' => sanitize_textarea_field(wp_unslash($_POST['description'] ?? '')),
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- decoded JSON is whitelisted/sanitized field-by-field in sanitizeFields() right below.
            'fields' => $this->sanitizeFields(json_decode(wp_unslash($_POST['fields'] ?? '[]'), true) ?? []),
            'email_to' => sanitize_email(wp_unslash($_POST['email_to'] ?? '')),
            'email_subject' => sanitize_text_field(wp_unslash($_POST['email_subject'] ?? '')),
            'success_message' => sanitize_textarea_field(wp_unslash($_POST['success_message'] ?? '')),
            'is_active' => intval($_POST['is_active'] ?? 1),
        ];

        if (empty($data['name'])) {
            wp_send_json_error(['message' => 'Form name is required']);
        }

        $form_id = intval($_POST['form_id'] ?? 0);

        if ($form_id) {
            \PSCF\Core\Database::updateForm($form_id, $data);
            wp_send_json_success(['message' => 'Form updated', 'form_id' => $form_id]);
        } else {
            $new_id = \PSCF\Core\Database::createForm($data);
            wp_send_json_success(['message' => 'Form created', 'form_id' => $new_id]);
        }
    }

    /**
     * Whitelist and sanitize the decoded field-definition array submitted from the form builder.
     */
    private function sanitizeFields(array $fields): array
    {
        $allowed_types = ['text', 'email', 'phone', 'textarea', 'select', 'checkbox', 'radio'];
        $sanitized = [];

        foreach ($fields as $field) {
            if (!is_array($field)) {
                continue;
            }

            $type = in_array($field['type'] ?? '', $allowed_types, true) ? $field['type'] : 'text';

            $clean = [
                'label' => sanitize_text_field($field['label'] ?? ''),
                'name' => sanitize_key($field['name'] ?? ''),
                'type' => $type,
                'placeholder' => sanitize_text_field($field['placeholder'] ?? ''),
                'required' => !empty($field['required']),
            ];

            if (!empty($field['options']) && is_array($field['options'])) {
                $clean['options'] = array_map('sanitize_text_field', $field['options']);
            }

            $sanitized[] = $clean;
        }

        return $sanitized;
    }

    public function ajaxDeleteForm(): void
    {
        check_ajax_referer('pscf_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }

        $form_id = intval($_POST['form_id'] ?? 0);

        if (!$form_id) {
            wp_send_json_error(['message' => 'Invalid form ID']);
        }

        \PSCF\Core\Database::deleteForm($form_id);
        wp_send_json_success(['message' => 'Form deleted']);
    }

    public function exportCsv(): void
    {
        check_ajax_referer('pscf_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $form_id = intval($_GET['form_id'] ?? 0);
        $form = \PSCF\Core\Database::getForm($form_id);

        if (!$form) {
            wp_die('Form not found');
        }

        $submissions = \PSCF\Core\Database::getAllSubmissionsForExport($form_id);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="submissions-' . $form_id . '-' . gmdate('Y-m-d') . '.csv"');

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

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- streaming a CSV to the browser via php://output, not a filesystem write; WP_Filesystem has no equivalent for this.
        fclose($output);
        exit;
    }
}