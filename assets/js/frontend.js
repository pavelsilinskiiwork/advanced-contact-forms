jQuery(function ($) {
    'use strict';

    $(document).on('submit', '.acf-form', function (e) {
        e.preventDefault();

        const form = $(this);
        const formId = form.data('form-id');
        const submitBtn = form.find('.acf-submit-btn');
        const spinner = form.find('.acf-spinner');

        // Clear previous errors
        form.find('.acf-field-error').text('');
        form.find('.acf-field').removeClass('has-error');
        form.find('.acf-form-success, .acf-form-error').hide().text('');

        // Collect data
        const formData = {};
        form.serializeArray().forEach(function (item) {
            formData[item.name] = item.value;
        });

        // Checkboxes
        form.find('input[type="checkbox"]').each(function () {
            formData[$(this).attr('name')] = $(this).is(':checked') ? '1' : '';
        });

        submitBtn.prop('disabled', true);
        spinner.show();

      $.ajax({
    url: acfFrontend.restUrl + 'forms/' + formId + '/submit',
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify(formData),
    beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', acfFrontend.nonce);
    },
            success: function (response) {
                if (response.success) {
                    form.find('.acf-form-success')
                        .text(response.message)
                        .show();
                    form[0].reset();
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;

                if (xhr.status === 422 && response.data && response.data.errors) {
                    Object.entries(response.data.errors).forEach(([field, message]) => {
                        const fieldEl = form.find(`[name="${field}"]`).closest('.acf-field');
                        fieldEl.addClass('has-error');
                        fieldEl.find('.acf-field-error').text(message);
                    });
                } else {
                    form.find('.acf-form-error')
                        .text(response?.message || 'An error occurred. Please try again.')
                        .show();
                }
            },
            complete: function () {
                submitBtn.prop('disabled', false);
                spinner.hide();
            }
        });
    });
});