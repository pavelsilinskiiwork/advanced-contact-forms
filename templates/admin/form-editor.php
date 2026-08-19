<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap acf-wrap">
    <h1><?php echo $form ? esc_html__('Edit Form', 'advanced-contact-forms') : esc_html__('Add New Form', 'advanced-contact-forms'); ?></h1>

    <div class="acf-editor-layout">
        <div class="acf-editor-main">
            <div class="acf-card">
                <h2><?php esc_html_e('Form Settings', 'advanced-contact-forms'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th><label for="acf-name"><?php esc_html_e('Form Name', 'advanced-contact-forms'); ?> *</label></th>
                        <td>
                            <input type="text" id="acf-name" class="regular-text"
                                   value="<?php echo esc_attr($form['name'] ?? ''); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="acf-description"><?php esc_html_e('Description', 'advanced-contact-forms'); ?></label></th>
                        <td>
                            <textarea id="acf-description" class="large-text" rows="3"><?php echo esc_textarea($form['description'] ?? ''); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="acf-email-to"><?php esc_html_e('Send Notifications To', 'advanced-contact-forms'); ?></label></th>
                        <td>
                            <input type="email" id="acf-email-to" class="regular-text"
                                   value="<?php echo esc_attr($form['email_to'] ?? get_option('admin_email')); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="acf-email-subject"><?php esc_html_e('Email Subject', 'advanced-contact-forms'); ?></label></th>
                        <td>
                            <input type="text" id="acf-email-subject" class="regular-text"
                                   value="<?php echo esc_attr($form['email_subject'] ?? 'New Form Submission'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="acf-success-message"><?php esc_html_e('Success Message', 'advanced-contact-forms'); ?></label></th>
                        <td>
                            <textarea id="acf-success-message" class="large-text" rows="2"><?php echo esc_textarea($form['success_message'] ?? 'Thank you for your message!'); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Status', 'advanced-contact-forms'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" id="acf-is-active" <?php checked($form['is_active'] ?? 1, 1); ?>>
                                <?php esc_html_e('Active', 'advanced-contact-forms'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="acf-card">
                <h2><?php esc_html_e('Form Fields', 'advanced-contact-forms'); ?></h2>
                <p class="description"><?php esc_html_e('Add fields to your form. Drag to reorder.', 'advanced-contact-forms'); ?></p>

                <div id="acf-fields-list">
                    <?php
                    $fields = $form['fields'] ?? [];
                    foreach ($fields as $index => $field):
                    ?>
                        <div class="acf-field-row" data-index="<?php echo $index; ?>">
                            <div class="acf-field-handle">⠿</div>
                            <div class="acf-field-info">
                                <strong><?php echo esc_html($field['label']); ?></strong>
                                <span class="acf-field-type"><?php echo esc_html($field['type']); ?></span>
                                <?php if (!empty($field['required'])): ?>
                                    <span class="acf-required-badge">Required</span>
                                <?php endif; ?>
                            </div>
                            <div class="acf-field-actions">
                                <button class="button button-small acf-edit-field" data-index="<?php echo $index; ?>">
                                    <?php esc_html_e('Edit', 'advanced-contact-forms'); ?>
                                </button>
                                <button class="button button-small acf-remove-field" data-index="<?php echo $index; ?>">
                                    <?php esc_html_e('Remove', 'advanced-contact-forms'); ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" id="acf-add-field" class="button button-secondary">
                    + <?php esc_html_e('Add Field', 'advanced-contact-forms'); ?>
                </button>
            </div>
        </div>

        <div class="acf-editor-sidebar">
            <div class="acf-card">
                <?php if ($form): ?>
                    <p><strong><?php esc_html_e('Shortcode:', 'advanced-contact-forms'); ?></strong></p>
                    <code>[contact_form id="<?php echo esc_attr($form['id']); ?>"]</code>
                <?php endif; ?>

                <button type="button" id="acf-save-form" class="button button-primary button-large"
                        data-form-id="<?php echo esc_attr($form['id'] ?? 0); ?>">
                    <?php echo $form ? esc_html__('Update Form', 'advanced-contact-forms') : esc_html__('Save Form', 'advanced-contact-forms'); ?>
                </button>

                <p id="acf-save-status"></p>
            </div>

            <div class="acf-card">
                <h3><?php esc_html_e('Add Field Type', 'advanced-contact-forms'); ?></h3>
                <div class="acf-field-types">
                    <?php
                    $types = [
                        'text' => '📝 Text',
                        'email' => '📧 Email',
                        'phone' => '📞 Phone',
                        'textarea' => '📄 Textarea',
                        'select' => '📋 Select',
                        'checkbox' => '☑️ Checkbox',
                        'radio' => '🔘 Radio',
                    ];
                    foreach ($types as $type => $label):
                    ?>
                        <button class="button acf-quick-add-field" data-type="<?php echo esc_attr($type); ?>">
                            <?php echo esc_html($label); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="acf-fields-data" value="<?php echo esc_attr(wp_json_encode($form['fields'] ?? [])); ?>">
</div>

<!-- Field Modal -->
<div id="acf-field-modal" style="display:none;">
    <div class="acf-modal-overlay">
        <div class="acf-modal">
            <h2><?php esc_html_e('Field Settings', 'advanced-contact-forms'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><label><?php esc_html_e('Field Label', 'advanced-contact-forms'); ?></label></th>
                    <td><input type="text" id="modal-label" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e('Field Name', 'advanced-contact-forms'); ?></label></th>
                    <td><input type="text" id="modal-name" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e('Type', 'advanced-contact-forms'); ?></label></th>
                    <td>
                        <select id="modal-type">
                            <option value="text">Text</option>
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                            <option value="textarea">Textarea</option>
                            <option value="select">Select</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="radio">Radio</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e('Placeholder', 'advanced-contact-forms'); ?></label></th>
                    <td><input type="text" id="modal-placeholder" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e('Required', 'advanced-contact-forms'); ?></label></th>
                    <td><input type="checkbox" id="modal-required"></td>
                </tr>
                <tr id="modal-options-row" style="display:none">
                    <th><label><?php esc_html_e('Options (one per line)', 'advanced-contact-forms'); ?></label></th>
                    <td><textarea id="modal-options" rows="4" class="large-text"></textarea></td>
                </tr>
            </table>
            <div class="acf-modal-actions">
                <button class="button button-primary" id="modal-save"><?php esc_html_e('Save Field', 'advanced-contact-forms'); ?></button>
                <button class="button" id="modal-cancel"><?php esc_html_e('Cancel', 'advanced-contact-forms'); ?></button>
            </div>
        </div>
    </div>
</div>