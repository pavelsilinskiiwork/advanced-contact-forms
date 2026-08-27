<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap acf-wrap">
    <h1 class="wp-heading-inline">
        <?php esc_html_e('Contact Forms', 'contact-forms-by-pavel-silinskii'); ?>
    </h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=pscf-new-form')); ?>" class="page-title-action">
        <?php esc_html_e('Add New', 'contact-forms-by-pavel-silinskii'); ?>
    </a>
    <hr class="wp-header-end">

    <?php if (empty($forms)): ?>
        <div class="acf-empty-state">
            <div class="acf-empty-icon">📋</div>
            <h2><?php esc_html_e('No forms yet', 'contact-forms-by-pavel-silinskii'); ?></h2>
            <p><?php esc_html_e('Create your first contact form to get started.', 'contact-forms-by-pavel-silinskii'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=pscf-new-form')); ?>" class="button button-primary button-large">
                <?php esc_html_e('Create Your First Form', 'contact-forms-by-pavel-silinskii'); ?>
            </a>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Name', 'contact-forms-by-pavel-silinskii'); ?></th>
                    <th><?php esc_html_e('Shortcode', 'contact-forms-by-pavel-silinskii'); ?></th>
                    <th><?php esc_html_e('Submissions', 'contact-forms-by-pavel-silinskii'); ?></th>
                    <th><?php esc_html_e('Status', 'contact-forms-by-pavel-silinskii'); ?></th>
                    <th><?php esc_html_e('Created', 'contact-forms-by-pavel-silinskii'); ?></th>
                    <th><?php esc_html_e('Actions', 'contact-forms-by-pavel-silinskii'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- local to this template, included from a class method; not a real global. ?>
                <?php foreach ($forms as $form): ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=pscf-new-form&form_id=' . $form['id'])); ?>">
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
                                <?php esc_html_e('Copy', 'contact-forms-by-pavel-silinskii'); ?>
                            </button>
                        </td>
                        <td>
                            <?php
                            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- local to this template, included from a class method; not a real global.
                            $count = \PSCF\Core\Database::countSubmissions((int)$form['id']);
                            echo esc_html($count);
                            if ($count > 0): ?>
                                <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=pscf_export_csv&form_id=' . $form['id'] . '&nonce=' . wp_create_nonce('pscf_admin_nonce'))); ?>"
                                   class="button button-small" style="margin-left:5px">
                                    CSV
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($form['is_active']): ?>
                                <span class="acf-badge acf-badge-active"><?php esc_html_e('Active', 'contact-forms-by-pavel-silinskii'); ?></span>
                            <?php else: ?>
                                <span class="acf-badge acf-badge-inactive"><?php esc_html_e('Inactive', 'contact-forms-by-pavel-silinskii'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html(gmdate('M j, Y', strtotime($form['created_at']))); ?></td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=pscf-new-form&form_id=' . $form['id'])); ?>"
                               class="button button-small">
                                <?php esc_html_e('Edit', 'contact-forms-by-pavel-silinskii'); ?>
                            </a>
                            <button class="button button-small acf-delete-form"
                                    data-form-id="<?php echo esc_attr($form['id']); ?>">
                                <?php esc_html_e('Delete', 'contact-forms-by-pavel-silinskii'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>