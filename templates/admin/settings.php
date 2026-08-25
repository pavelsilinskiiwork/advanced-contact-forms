<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap acf-wrap">
    <h1><?php esc_html_e('Settings', 'contact-forms-by-pavel-silinskii'); ?></h1>

    <form method="post">
        <?php wp_nonce_field('pscf_settings_nonce'); ?>
        <input type="hidden" name="pscf_save_settings" value="1">

        <table class="form-table">
            <tr>
                <th><label for="email_from"><?php esc_html_e('From Email', 'contact-forms-by-pavel-silinskii'); ?></label></th>
                <td>
                    <input type="email" id="email_from" name="email_from" class="regular-text"
                           value="<?php echo esc_attr(get_option('pscf_email_from', get_option('admin_email'))); ?>">
                    <p class="description"><?php esc_html_e('Email address used as sender for notifications.', 'contact-forms-by-pavel-silinskii'); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Spam Protection', 'contact-forms-by-pavel-silinskii'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="spam_protection"
                               <?php checked(get_option('pscf_spam_protection', '1'), '1'); ?>>
                        <?php esc_html_e('Enable honeypot spam protection', 'contact-forms-by-pavel-silinskii'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Save Settings', 'contact-forms-by-pavel-silinskii')); ?>
    </form>

    <div class="acf-card" style="margin-top:20px">
        <h2><?php esc_html_e('REST API', 'contact-forms-by-pavel-silinskii'); ?></h2>
        <p><?php esc_html_e('Available endpoints:', 'contact-forms-by-pavel-silinskii'); ?></p>
        <ul>
            <li><code>GET <?php echo esc_url(rest_url('pavelsilinskii-cf/v1/forms')); ?></code> — <?php esc_html_e('List all forms (admin)', 'contact-forms-by-pavel-silinskii'); ?></li>
            <li><code>GET <?php echo esc_url(rest_url('pavelsilinskii-cf/v1/forms/{id}')); ?></code> — <?php esc_html_e('Get form fields', 'contact-forms-by-pavel-silinskii'); ?></li>
            <li><code>POST <?php echo esc_url(rest_url('pavelsilinskii-cf/v1/forms/{id}/submit')); ?></code> — <?php esc_html_e('Submit form', 'contact-forms-by-pavel-silinskii'); ?></li>
            <li><code>GET <?php echo esc_url(rest_url('pavelsilinskii-cf/v1/forms/{id}/submissions')); ?></code> — <?php esc_html_e('Get submissions (admin)', 'contact-forms-by-pavel-silinskii'); ?></li>
        </ul>
    </div>
</div>