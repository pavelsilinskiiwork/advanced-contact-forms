<?php if (!defined('ABSPATH')) exit; ?>

<div class="acf-form-wrap" id="acf-form-<?php echo esc_attr($form_id); ?>">
    <form class="acf-form" data-form-id="<?php echo esc_attr($form_id); ?>">
        <?php wp_nonce_field('wp_rest', '_wpnonce'); ?>

        <?php // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- local to this template, included from a class method; not a real global. ?>
        <?php foreach ($fields as $field): ?>
            <div class="acf-field acf-field-<?php echo esc_attr($field['type']); ?>">
                <label for="acf-<?php echo esc_attr($field['name']); ?>">
                    <?php echo esc_html($field['label']); ?>
                    <?php if (!empty($field['required'])): ?>
                        <span class="acf-required">*</span>
                    <?php endif; ?>
                </label>

                <?php switch ($field['type']):
                    case 'textarea': ?>
                        <textarea
                            id="acf-<?php echo esc_attr($field['name']); ?>"
                            name="<?php echo esc_attr($field['name']); ?>"
                            placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                            <?php echo !empty($field['required']) ? 'required' : ''; ?>
                            rows="5"></textarea>
                        <?php break;
                    case 'select': ?>
                        <select
                            id="acf-<?php echo esc_attr($field['name']); ?>"
                            name="<?php echo esc_attr($field['name']); ?>"
                            <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                            <option value=""><?php esc_html_e('Select...', 'contact-forms-by-pavel-silinskii'); ?></option>
                            <?php // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- local to this template, included from a class method; not a real global. ?>
                            <?php foreach ($field['options'] ?? [] as $option): ?>
                                <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php break;
                    case 'checkbox': ?>
                        <label class="acf-checkbox-label">
                            <input type="checkbox"
                                   id="acf-<?php echo esc_attr($field['name']); ?>"
                                   name="<?php echo esc_attr($field['name']); ?>"
                                   value="1"
                                   <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                            <?php echo esc_html($field['placeholder'] ?? $field['label']); ?>
                        </label>
                        <?php break;
                    default: ?>
                        <input
                            type="<?php echo esc_attr($field['type']); ?>"
                            id="acf-<?php echo esc_attr($field['name']); ?>"
                            name="<?php echo esc_attr($field['name']); ?>"
                            placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                            <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                <?php endswitch; ?>

                <span class="acf-field-error" id="acf-error-<?php echo esc_attr($field['name']); ?>"></span>
            </div>
        <?php endforeach; ?>

        <!-- Honeypot spam protection -->
        <div style="display:none" aria-hidden="true">
            <input type="text" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="acf-submit">
            <button type="submit" class="acf-submit-btn">
                <?php esc_html_e('Send Message', 'contact-forms-by-pavel-silinskii'); ?>
            </button>
            <span class="acf-spinner" style="display:none">⏳</span>
        </div>

        <div class="acf-form-success" style="display:none"></div>
        <div class="acf-form-error" style="display:none"></div>
    </form>
</div>