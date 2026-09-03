jQuery(function ($) {
    'use strict';

    let fields = [];

    // Init fields from hidden input
    try {
        fields = JSON.parse($('#acf-fields-data').val() || '[]');
    } catch (e) {
        fields = [];
    }

    initSortable();
    // Copy shortcode
    $(document).on('click', '.acf-copy-shortcode', function () {
        const shortcode = $(this).data('shortcode');
        navigator.clipboard.writeText(shortcode).then(() => {
            const btn = $(this);
            btn.text('Copied!');
            setTimeout(() => btn.text('Copy'), 2000);
        });
    });

    // Delete form
    $(document).on('click', '.acf-delete-form', function () {
        if (!confirm(pavelSilinskiiContactFormsAdmin.strings.confirmDelete)) return;

        const formId = $(this).data('form-id');
        const row = $(this).closest('tr');

        $.post(pavelSilinskiiContactFormsAdmin.ajaxUrl, {
            action: 'pavel_silinskii_contact_forms_delete_form',
            form_id: formId,
            nonce: pavelSilinskiiContactFormsAdmin.nonce,
        }, function (response) {
            if (response.success) {
                row.fadeOut(300, function () { row.remove(); });
            } else {
                alert(pavelSilinskiiContactFormsAdmin.strings.error);
            }
        });
    });

    // Quick add field
    $(document).on('click', '.acf-quick-add-field', function () {
        const type = $(this).data('type');
        openModal({ type });
    });

    // Add field button
    $('#acf-add-field').on('click', function () {
        openModal({});
    });

    // Edit field
    $(document).on('click', '.acf-edit-field', function () {
        const index = parseInt($(this).data('index'));
        openModal(fields[index], index);
    });

    // Remove field
    $(document).on('click', '.acf-remove-field', function () {
        const index = parseInt($(this).data('index'));
        fields.splice(index, 1);
        renderFields();
    });

    // Modal type change
    $('#modal-type').on('change', function () {
        const needsOptions = ['select', 'radio'].includes($(this).val());
        $('#modal-options-row').toggle(needsOptions);
    });

    // Modal save
    $('#modal-save').on('click', function () {
        const label = $('#modal-label').val().trim();
        const name = $('#modal-name').val().trim() ||
            label.toLowerCase().replace(/[^a-z0-9]+/g, '_');

        if (!label) {
            alert('Field label is required');
            return;
        }

        const field = {
            label,
            name,
            type: $('#modal-type').val(),
            placeholder: $('#modal-placeholder').val(),
            required: $('#modal-required').is(':checked'),
        };

        const optionsText = $('#modal-options').val().trim();
        if (optionsText) {
            field.options = optionsText.split('\n').map(o => o.trim()).filter(Boolean);
        }

        const editIndex = $('#modal-save').data('edit-index');
        if (editIndex !== undefined && editIndex !== '') {
            fields[editIndex] = field;
        } else {
            fields.push(field);
        }

        closeModal();
        renderFields();
    });

    // Modal cancel
    $('#modal-cancel').on('click', closeModal);

    // Save form
    $('#acf-save-form').on('click', function () {
        const btn = $(this);
        const formId = btn.data('form-id');
        const name = $('#acf-name').val().trim();

        if (!name) {
            alert('Form name is required');
            return;
        }

        btn.prop('disabled', true).text('Saving...');
        $('#acf-save-status').text('');

        $.post(pavelSilinskiiContactFormsAdmin.ajaxUrl, {
            action: 'pavel_silinskii_contact_forms_save_form',
            nonce: pavelSilinskiiContactFormsAdmin.nonce,
            form_id: formId,
            name,
            description: $('#acf-description').val(),
            email_to: $('#acf-email-to').val(),
            email_subject: $('#acf-email-subject').val(),
            success_message: $('#acf-success-message').val(),
            is_active: $('#acf-is-active').is(':checked') ? 1 : 0,
            fields: JSON.stringify(fields),
        }, function (response) {
            btn.prop('disabled', false);

            if (response.success) {
                btn.text('Update Form');
                btn.data('form-id', response.data.form_id);
                $('#acf-save-status').text(pavelSilinskiiContactFormsAdmin.strings.saved).css('color', 'green');

                if (!formId) {
                    window.location.href =
                        `admin.php?page=pavel-silinskii-contact-forms-new-form&form_id=${response.data.form_id}&saved=1`;
                }
            } else {
                btn.text('Save Form');
                $('#acf-save-status').text(pavelSilinskiiContactFormsAdmin.strings.error).css('color', 'red');
            }
        });
    });

    function openModal(field = {}, editIndex = '') {
        $('#modal-label').val(field.label || '');
        $('#modal-name').val(field.name || '');
        $('#modal-type').val(field.type || 'text').trigger('change');
        $('#modal-placeholder').val(field.placeholder || '');
        $('#modal-required').prop('checked', !!field.required);
        $('#modal-options').val((field.options || []).join('\n'));
        $('#modal-save').data('edit-index', editIndex);
        $('#acf-field-modal').show();
    }

    function closeModal() {
        $('#acf-field-modal').hide();
        $('#modal-save').removeData('edit-index');
    }

    function renderFields() {
        const list = $('#acf-fields-list');
        list.empty();

        fields.forEach((field, index) => {
            list.append(`
                <div class="acf-field-row" data-index="${index}">
                    <div class="acf-field-handle">⠿</div>
                    <div class="acf-field-info">
                        <strong>${escHtml(field.label)}</strong>
                        <span class="acf-field-type">${escHtml(field.type)}</span>
                        ${field.required ? '<span class="acf-required-badge">Required</span>' : ''}
                    </div>
                    <div class="acf-field-actions">
                        <button class="button button-small acf-edit-field" data-index="${index}">Edit</button>
                        <button class="button button-small acf-remove-field" data-index="${index}">Remove</button>
                    </div>
                </div>
            `);
        });
         initSortable();
    }

    function initSortable() {
    $('#acf-fields-list').sortable({
        handle: '.acf-field-handle',
        placeholder: 'acf-field-placeholder',
        update: function() {
            const newFields = [];
            $('#acf-fields-list .acf-field-row').each(function() {
                const index = parseInt($(this).data('index'));
                newFields.push(fields[index]);
            });
            fields = newFields;
            renderFields();
        }
    });
}

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
});