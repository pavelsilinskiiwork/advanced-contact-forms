<?php

namespace ACF\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Installer
{
    public static function activate(): void
    {
        self::createTables();
        self::createDefaultOptions();
        flush_rewrite_rules();
    }

    public static function deactivate(): void
    {
        flush_rewrite_rules();
    }

    private static function createTables(): void
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Forms table
        $forms_table = $wpdb->prefix . 'pscf_forms';
        $sql_forms = "CREATE TABLE IF NOT EXISTS $forms_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            fields longtext NOT NULL,
            email_to varchar(255),
            email_subject varchar(255),
            success_message text,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Submissions table
        $submissions_table = $wpdb->prefix . 'pscf_submissions';
        $sql_submissions = "CREATE TABLE IF NOT EXISTS $submissions_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            form_id bigint(20) NOT NULL,
            data longtext NOT NULL,
            ip_address varchar(45),
            user_agent text,
            status varchar(50) DEFAULT 'new',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY form_id (form_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql_forms);
        dbDelta($sql_submissions);
    }

    private static function createDefaultOptions(): void
    {
        add_option('pscf_version', PSCF_VERSION);
        add_option('pscf_email_from', get_option('admin_email'));
        add_option('pscf_spam_protection', '1');
    }
}