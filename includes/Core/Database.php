<?php

namespace ACF\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Database
{
    private static string $forms_table;
    private static string $submissions_table;

    public static function init(): void
    {
        global $wpdb;
        self::$forms_table = $wpdb->prefix . 'pscf_forms';
        self::$submissions_table = $wpdb->prefix . 'pscf_submissions';
    }

    public static function getFormsTable(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'pscf_forms';
    }

    public static function getSubmissionsTable(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'pscf_submissions';
    }

    public static function getForms(): array
    {
        global $wpdb;
        $table = self::getFormsTable();
        return $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC", ARRAY_A) ?? [];
    }

    public static function getForm(int $id): ?array
    {
        global $wpdb;
        $table = self::getFormsTable();
        $result = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id),
            ARRAY_A
        );
        return $result ?: null;
    }

    public static function createForm(array $data): int|false
    {
        global $wpdb;
        $wpdb->insert(self::getFormsTable(), [
            'name' => sanitize_text_field($data['name']),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'fields' => wp_json_encode($data['fields'] ?? []),
            'email_to' => sanitize_email($data['email_to'] ?? get_option('admin_email')),
            'email_subject' => sanitize_text_field($data['email_subject'] ?? 'New Form Submission'),
            'success_message' => sanitize_textarea_field($data['success_message'] ?? 'Thank you for your message!'),
            'is_active' => 1,
        ]);
        return $wpdb->insert_id;
    }

    public static function updateForm(int $id, array $data): bool
    {
        global $wpdb;
        $result = $wpdb->update(
            self::getFormsTable(),
            [
                'name' => sanitize_text_field($data['name']),
                'description' => sanitize_textarea_field($data['description'] ?? ''),
                'fields' => wp_json_encode($data['fields'] ?? []),
                'email_to' => sanitize_email($data['email_to'] ?? ''),
                'email_subject' => sanitize_text_field($data['email_subject'] ?? ''),
                'success_message' => sanitize_textarea_field($data['success_message'] ?? ''),
                'is_active' => intval($data['is_active'] ?? 1),
            ],
            ['id' => $id]
        );
        return $result !== false;
    }

    public static function deleteForm(int $id): bool
    {
        global $wpdb;
        $wpdb->delete(self::getSubmissionsTable(), ['form_id' => $id]);
        return $wpdb->delete(self::getFormsTable(), ['id' => $id]) !== false;
    }

    public static function getSubmissions(int $form_id, int $limit = 50, int $offset = 0): array
    {
        global $wpdb;
        $table = self::getSubmissionsTable();
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE form_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $form_id, $limit, $offset
            ),
            ARRAY_A
        ) ?? [];
    }

    public static function countSubmissions(int $form_id): int
    {
        global $wpdb;
        $table = self::getSubmissionsTable();
        return (int) $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE form_id = %d", $form_id)
        );
    }

    public static function createSubmission(array $data): int|false
    {
        global $wpdb;
        $wpdb->insert(self::getSubmissionsTable(), [
            'form_id' => intval($data['form_id']),
            'data' => wp_json_encode($data['data']),
            'ip_address' => sanitize_text_field($data['ip_address'] ?? ''),
            'user_agent' => sanitize_text_field($data['user_agent'] ?? ''),
            'status' => 'new',
        ]);
        return $wpdb->insert_id;
    }

    public static function getAllSubmissionsForExport(int $form_id): array
    {
        global $wpdb;
        $table = self::getSubmissionsTable();
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE form_id = %d ORDER BY created_at DESC",
                $form_id
            ),
            ARRAY_A
        ) ?? [];
    }
}