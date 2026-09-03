<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap acf-wrap">
    <h1><?php esc_html_e('Settings', 'pavel-silinskii-contact-forms'); ?></h1>

    <form method="post">
        <?php wp_nonce_field('pavel_silinskii_contact_forms_settings_nonce'); ?>
        <input type="hidden" name="pavel_silinskii_contact_forms_save_settings" value="1">

        <table class="form-table">
            <tr>
                <th><label for="email_from"><?php esc_html_e('From Email', 'pavel-silinskii-contact-forms'); ?></label></th>
                <td>
                    <input type="email" id="email_from" name="email_from" class="regular-text"
                           value="<?php echo esc_attr(get_option('pavel_silinskii_contact_forms_email_from', get_option('admin_email'))); ?>">
                    <p class="description"><?php esc_html_e('Email address used as sender for notifications.', 'pavel-silinskii-contact-forms'); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Spam Protection', 'pavel-silinskii-contact-forms'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="spam_protection"
                               <?php checked(get_option('pavel_silinskii_contact_forms_spam_protection', '1'), '1'); ?>>
                        <?php esc_html_e('Enable honeypot spam protection', 'pavel-silinskii-contact-forms'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Save Settings', 'pavel-silinskii-contact-forms')); ?>
    </form>

    <div class="acf-card" style="margin-top:20px">
        <h2><?php esc_html_e('REST API', 'pavel-silinskii-contact-forms'); ?></h2>
        <p><?php esc_html_e('Available endpoints:', 'pavel-silinskii-contact-forms'); ?></p>
        <ul>
            <li><code>GET <?php echo esc_url(rest_url('pavelsilinskii-cf/v1/forms')); ?></code> — <?php esc_html_e('List all forms (admin)', 'pavel-silinskii-contact-forms'); ?></li>
            <li><code>GET <?php echo esc_url(rest_url('pavelsilinskii-cf/v1/forms/{id}')); ?></code> — <?php esc_html_e('Get form fields', 'pavel-silinskii-contact-forms'); ?></li>
            <li><code>POST <?php echo esc_url(rest_url('pavelsilinskii-cf/v1/forms/{id}/submit')); ?></code> — <?php esc_html_e('Submit form', 'pavel-silinskii-contact-forms'); ?></li>
            <li><code>GET <?php echo esc_url(rest_url('pavelsilinskii-cf/v1/forms/{id}/submissions')); ?></code> — <?php esc_html_e('Get submissions (admin)', 'pavel-silinskii-contact-forms'); ?></li>
        </ul>
    </div>
</div>