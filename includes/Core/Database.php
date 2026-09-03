<?php

namespace PavelSilinskii\ContactForms\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Database
{
    private static string $forms_table;
    private static string $submissions_table;

    private const CACHE_GROUP = 'pavel-silinskii-contact-forms';

    public static function init(): void
    {
        global $wpdb;
        self::$forms_table = $wpdb->prefix . 'pavel_silinskii_contact_forms_forms';
        self::$submissions_table = $wpdb->prefix . 'pavel_silinskii_contact_forms_submissions';
    }

    public static function getFormsTable(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'pavel_silinskii_contact_forms_forms';
    }

    public static function getSubmissionsTable(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'pavel_silinskii_contact_forms_submissions';
    }

    /**
     * Per-form "generation" token used to namespace cache keys for parameterized
     * submission queries, so bumping it (on insert/delete) instantly invalidates
     * every previously cached page/limit/offset combination without having to
     * enumerate and delete each one individually.
     */
    private static function getSubmissionsGen(int $form_id): string
    {
        $key = "submissions_gen_{$form_id}";
        $found = false;
        $gen = wp_cache_get($key, self::CACHE_GROUP, false, $found);

        if (!$found) {
            $gen = '0';
            wp_cache_set($key, $gen, self::CACHE_GROUP, 0);
        }

        return (string) $gen;
    }

    private static function bumpSubmissionsGen(int $form_id): void
    {
        wp_cache_set("submissions_gen_{$form_id}", (string) microtime(true), self::CACHE_GROUP, 0);
    }

    public static function getForms(): array
    {
        global $wpdb;

        $found = false;
        $cached = wp_cache_get('forms_all', self::CACHE_GROUP, false, $found);
        if ($found) {
            return $cached;
        }

        $table = self::getFormsTable();
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no core API for it; $table is our own fixed name, never user input; result is cached above.
        $results = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC", ARRAY_A) ?? [];

        wp_cache_set('forms_all', $results, self::CACHE_GROUP, MINUTE_IN_SECONDS * 5);

        return $results;
    }

    public static function getForm(int $id): ?array
    {
        global $wpdb;

        $cache_key = "form_{$id}";
        $found = false;
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP, false, $found);
        if ($found) {
            return $cached ?: null;
        }

        $table = self::getFormsTable();
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no core API for it; $table is our own fixed name, never user input; $id goes through %d; result is cached above.
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);

        wp_cache_set($cache_key, $result ?: false, self::CACHE_GROUP, MINUTE_IN_SECONDS * 5);

        return $result ?: null;
    }

    public static function createForm(array $data): int|false
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no core API for it; values are sanitized above; cache invalidated below.
        $wpdb->insert(self::getFormsTable(), [
            'name' => sanitize_text_field($data['name']),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'fields' => wp_json_encode($data['fields'] ?? []),
            'email_to' => sanitize_email($data['email_to'] ?? get_option('admin_email')),
            'email_subject' => sanitize_text_field($data['email_subject'] ?? 'New Form Submission'),
            'success_message' => sanitize_textarea_field($data['success_message'] ?? 'Thank you for your message!'),
            'is_active' => 1,
        ]);

        wp_cache_delete('forms_all', self::CACHE_GROUP);

        return $wpdb->insert_id;
    }

    public static function updateForm(int $id, array $data): bool
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no core API for it; values are sanitized above; cache invalidated below.
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

        wp_cache_delete('forms_all', self::CACHE_GROUP);
        wp_cache_delete("form_{$id}", self::CACHE_GROUP);

        return $result !== false;
    }

    public static function deleteForm(int $id): bool
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no core API for it; cache invalidated below.
        $wpdb->delete(self::getSubmissionsTable(), ['form_id' => $id]);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no core API for it; cache invalidated below.
        $result = $wpdb->delete(self::getFormsTable(), ['id' => $id]) !== false;

        wp_cache_delete('forms_all', self::CACHE_GROUP);
        wp_cache_delete("form_{$id}", self::CACHE_GROUP);
        wp_cache_delete("submissions_count_{$id}", self::CACHE_GROUP);
        self::bumpSubmissionsGen($id);

        return $result;
    }

    public static function getSubmissions(int $form_id, int $limit = 50, int $offset = 0): array
    {
        global $wpdb;

        $gen = self::getSubmissionsGen($form_id);
        $cache_key = "submissions_{$form_id}_{$gen}_{$limit}_{$offset}";
        $found = false;
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP, false, $found);
        if ($found) {
            return $cached;
        }

        $table = self::getSubmissionsTable();
        // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no core API for it; $table is our own fixed name, never user input; result is cached above.
        $results = $wpdb->get_results(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table is our own fixed table name, never user input; user values go through %d.
                "SELECT * FROM $table WHERE form_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $form_id, $limit, $offset
            ),
            ARRAY_A
        ) ?? [];

        wp_cache_set($cache_key, $results, self::CACHE_GROUP, MINUTE_IN_SECONDS * 5);

        return $results;
    }

    public static function countSubmissions(int $form_id): int
    {
        global $wpdb;

        $cache_key = "submissions_count_{$form_id}";
        $found = false;
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP, false, $found);
        if ($found) {
            return $cached;
        }

        $table = self::getSubmissionsTable();
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no core API for it; $table is our own fixed name, never user input; $form_id goes through %d; result is cached above.
        $count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE form_id = %d", $form_id));

        wp_cache_set($cache_key, $count, self::CACHE_GROUP, MINUTE_IN_SECONDS * 5);

        return $count;
    }

    public static function createSubmission(array $data): int|false
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no core API for it; values are sanitized above; cache invalidated below.
        $wpdb->insert(self::getSubmissionsTable(), [
            'form_id' => intval($data['form_id']),
            'data' => wp_json_encode($data['data']),
            'ip_address' => sanitize_text_field($data['ip_address'] ?? ''),
            'user_agent' => sanitize_text_field($data['user_agent'] ?? ''),
            'status' => 'new',
        ]);

        $form_id = intval($data['form_id']);
        wp_cache_delete("submissions_count_{$form_id}", self::CACHE_GROUP);
        self::bumpSubmissionsGen($form_id);

        return $wpdb->insert_id;
    }

    public static function getAllSubmissionsForExport(int $form_id): array
    {
        global $wpdb;

        $gen = self::getSubmissionsGen($form_id);
        $cache_key = "submissions_export_{$form_id}_{$gen}";
        $found = false;
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP, false, $found);
        if ($found) {
            return $cached;
        }

        $table = self::getSubmissionsTable();
        // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, no core API for it; $table is our own fixed name, never user input; result is cached above.
        $results = $wpdb->get_results(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table is our own fixed table name, never user input; $form_id goes through %d.
                "SELECT * FROM $table WHERE form_id = %d ORDER BY created_at DESC",
                $form_id
            ),
            ARRAY_A
        ) ?? [];

        wp_cache_set($cache_key, $results, self::CACHE_GROUP, MINUTE_IN_SECONDS * 5);

        return $results;
    }
}
