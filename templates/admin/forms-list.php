<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap acf-wrap">
    <h1 class="wp-heading-inline">
        <?php esc_html_e('Contact Forms', 'pavel-silinskii-contact-forms'); ?>
    </h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=pavel-silinskii-contact-forms-new-form')); ?>" class="page-title-action">
        <?php esc_html_e('Add New', 'pavel-silinskii-contact-forms'); ?>
    </a>
    <hr class="wp-header-end">

    <?php if (empty($forms)): ?>
        <div class="acf-empty-state">
            <div class="acf-empty-icon">📋</div>
            <h2><?php esc_html_e('No forms yet', 'pavel-silinskii-contact-forms'); ?></h2>
            <p><?php esc_html_e('Create your first contact form to get started.', 'pavel-silinskii-contact-forms'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=pavel-silinskii-contact-forms-new-form')); ?>" class="button button-primary button-large">
                <?php esc_html_e('Create Your First Form', 'pavel-silinskii-contact-forms'); ?>
            </a>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Name', 'pavel-silinskii-contact-forms'); ?></th>
                    <th><?php esc_html_e('Shortcode', 'pavel-silinskii-contact-forms'); ?></th>
                    <th><?php esc_html_e('Submissions', 'pavel-silinskii-contact-forms'); ?></th>
                    <th><?php esc_html_e('Status', 'pavel-silinskii-contact-forms'); ?></th>
                    <th><?php esc_html_e('Created', 'pavel-silinskii-contact-forms'); ?></th>
                    <th><?php esc_html_e('Actions', 'pavel-silinskii-contact-forms'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- local to this template, included from a class method; not a real global. ?>
                <?php foreach ($forms as $form): ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=pavel-silinskii-contact-forms-new-form&form_id=' . $form['id'])); ?>">
                                    <?php echo esc_html($form['name']); ?>
                                </a>
                            </strong>
                            <?php if ($form['description']): ?>
                                <p class="description"><?php echo esc_html($form['description']); ?></p>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code>[contact_form id="<?php echo esc_attr($form['id']); ?>"]</code>
                            <button class="button button-small acf-copy-shortcode"
                                    data-shortcode='[contact_form id="<?php echo esc_attr($form['id']); ?>"]'>
                                <?php esc_html_e('Copy', 'pavel-silinskii-contact-forms'); ?>
                            </button>
                        </td>
                        <td>
                            <?php
                            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- local to this template, included from a class method; not a real global.
                            $count = \PavelSilinskii\ContactForms\Core\Database::countSubmissions((int)$form['id']);
                            echo esc_html($count);
                            if ($count > 0): ?>
                                <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=pavel_silinskii_contact_forms_export_csv&form_id=' . $form['id'] . '&nonce=' . wp_create_nonce('pavel_silinskii_contact_forms_admin_nonce'))); ?>"
                                   class="button button-small" style="margin-left:5px">
                                    CSV
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($form['is_active']): ?>
                                <span class="acf-badge acf-badge-active"><?php esc_html_e('Active', 'pavel-silinskii-contact-forms'); ?></span>
                            <?php else: ?>
                                <span class="acf-badge acf-badge-inactive"><?php esc_html_e('Inactive', 'pavel-silinskii-contact-forms'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html(gmdate('M j, Y', strtotime($form['created_at']))); ?></td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=pavel-silinskii-contact-forms-new-form&form_id=' . $form['id'])); ?>"
                               class="button button-small">
                                <?php esc_html_e('Edit', 'pavel-silinskii-contact-forms'); ?>
                            </a>
                            <button class="button button-small acf-delete-form"
                                    data-form-id="<?php echo esc_attr($form['id']); ?>">
                                <?php esc_html_e('Delete', 'pavel-silinskii-contact-forms'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>