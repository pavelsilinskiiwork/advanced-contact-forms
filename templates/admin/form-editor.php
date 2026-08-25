<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap acf-wrap">
    <h1><?php echo $form ? esc_html__('Edit Form', 'contact-forms-by-pavel-silinskii') : esc_html__('Add New Form', 'contact-forms-by-pavel-silinskii'); ?></h1>

    <div class="acf-editor-layout">
        <div class="acf-editor-main">
            <div class="acf-card">
                <h2><?php esc_html_e('Form Settings', 'contact-forms-by-pavel-silinskii'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th><label for="acf-name"><?php esc_html_e('Form Name', 'contact-forms-by-pavel-silinskii'); ?> *</label></th>
                        <td>
                            <input type="text" id="acf-name" class="regular-text"
                                   value="<?php echo esc_attr($form['name'] ?? ''); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="acf-description"><?php esc_html_e('Description', 'contact-forms-by-pavel-silinskii'); ?></label></th>
                        <td>
                            <textarea id="acf-description" class="large-text" rows="3"><?php echo esc_textarea($form['description'] ?? ''); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="acf-email-to"><?php esc_html_e('Send Notifications To', 'contact-forms-by-pavel-silinskii'); ?></label></th>
                        <td>
                            <input type="email" id="acf-email-to" class="regular-text"
                                   value="<?php echo esc_attr($form['email_to'] ?? get_option('admin_email')); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="acf-email-subject"><?php esc_html_e('Email Subject', 'contact-forms-by-pavel-silinskii'); ?></label></th>
                        <td>
                            <input type="text" id="acf-email-subject" class="regular-text"
                                   value="<?php echo esc_attr($form['email_subject'] ?? 'New Form Submission'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="acf-success-message"><?php esc_html_e('Success Message', 'contact-forms-by-pavel-silinskii'); ?></label></th>
                        <td>
                            <textarea id="acf-success-message" class="large-text" rows="2"><?php echo esc_textarea($form['success_message'] ?? 'Thank you for your message!'); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Status', 'contact-forms-by-pavel-silinskii'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" id="acf-is-active" <?php checked($form['is_active'] ?? 1, 1); ?>>
                                <?php esc_html_e('Active', 'contact-forms-by-pavel-silinskii'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="acf-card">
                <h2><?php esc_html_e('Form Fields', 'contact-forms-by-pavel-silinskii'); ?></h2>
                <p class="description"><?php esc_html_e('Add fields to your form. Drag to reorder.', 'contact-forms-by-pavel-silinskii'); ?></p>

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
                                    <?php esc_html_e('Edit', 'contact-forms-by-pavel-silinskii'); ?>
                                </button>
                                <button class="button button-small acf-remove-field" data-index="<?php echo $index; ?>">
                                    <?php esc_html_e('Remove', 'contact-forms-by-pavel-silinskii'); ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" id="acf-add-field" class="button button-secondary">
                    + <?php esc_html_e('Add Field', 'contact-forms-by-pavel-silinskii'); ?>
                </button>
            </div>
        </div>

        <div class="acf-editor-sidebar">
            <div class="acf-card">
                <?php if ($form): ?>
                    <p><strong><?php esc_html_e('Shortcode:', 'contact-forms-by-pavel-silinskii'); ?></strong></p>
                    <code>[contact_form id="<?php echo esc_attr($form['id']); ?>"]</code>
                <?php endif; ?>

                <button type="button" id="acf-save-form" class="button button-primary button-large"
                        data-form-id="<?php echo esc_attr($form['id'] ?? 0); ?>">
                    <?php echo $form ? esc_html__('Update Form', 'contact-forms-by-pavel-silinskii') : esc_html__('Save Form', 'contact-forms-by-pavel-silinskii'); ?>
                </button>

                <p id="acf-save-status"></p>
            </div>

            <div class="acf-card">
                <h3><?php esc_html_e('Add Field Type', 'contact-forms-by-pavel-silinskii'); ?></h3>
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
            <h2><?php esc_html_e('Field Settings', 'contact-forms-by-pavel-silinskii'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><label><?php esc_html_e('Field Label', 'contact-forms-by-pavel-silinskii'); ?></label></th>
                    <td><input type="text" id="modal-label" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e('Field Name', 'contact-forms-by-pavel-silinskii'); ?></label></th>
                    <td><input type="text" id="modal-name" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e('Type', 'contact-forms-by-pavel-silinskii'); ?></label></th>
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
                    <th><label><?php esc_html_e('Placeholder', 'contact-forms-by-pavel-silinskii'); ?></label></th>
                    <td><input type="text" id="modal-placeholder" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e('Required', 'contact-forms-by-pavel-silinskii'); ?></label></th>
                    <td><input type="checkbox" id="modal-required"></td>
                </tr>
                <tr id="modal-options-row" style="display:none">
                    <th><label><?php esc_html_e('Options (one per line)', 'contact-forms-by-pavel-silinskii'); ?></label></th>
                    <td><textarea id="modal-options" rows="4" class="large-text"></textarea></td>
                </tr>
            </table>
            <div class="acf-modal-actions">
                <button class="button button-primary" id="modal-save"><?php esc_html_e('Save Field', 'contact-forms-by-pavel-silinskii'); ?></button>
                <button class="button" id="modal-cancel"><?php esc_html_e('Cancel', 'contact-forms-by-pavel-silinskii'); ?></button>
            </div>
        </div>
    </div>
</div>