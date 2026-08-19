<?php

namespace ACF\Api;

class RestApi
{
    private string $namespace = 'acf/v1';

    public function init(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        // Get all forms
        register_rest_route($this->namespace, '/forms', [
            'methods' => 'GET',
            'callback' => [$this, 'getForms'],
            'permission_callback' => [$this, 'adminPermission'],
        ]);

        // Get single form
        register_rest_route($this->namespace, '/forms/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getForm'],
            'permission_callback' => '__return_true',
        ]);

        // Submit form
        register_rest_route($this->namespace, '/forms/(?P<id>\d+)/submit', [
            'methods' => 'POST',
            'callback' => [$this, 'submitForm'],
            'permission_callback' => '__return_true',
        ]);

        // Get submissions
        register_rest_route($this->namespace, '/forms/(?P<id>\d+)/submissions', [
            'methods' => 'GET',
            'callback' => [$this, 'getSubmissions'],
            'permission_callback' => [$this, 'adminPermission'],
        ]);
    }

    public function adminPermission(): bool
    {
        return current_user_can('manage_options');
    }

    public function getForms(\WP_REST_Request $request): \WP_REST_Response
    {
        $forms = \ACF\Core\Database::getForms();
        return new \WP_REST_Response($forms, 200);
    }

    public function getForm(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $form = \ACF\Core\Database::getForm((int) $request['id']);

        if (!$form) {
            return new \WP_Error('not_found', 'Form not found', ['status' => 404]);
        }

        $form['fields'] = json_decode($form['fields'], true);
        return new \WP_REST_Response($form, 200);
    }

    public function submitForm(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $form_id = (int) $request['id'];
        $form = \ACF\Core\Database::getForm($form_id);

        if (!$form) {
            return new \WP_Error('not_found', 'Form not found', ['status' => 404]);
        }

        if (!$form['is_active']) {
            return new \WP_Error('inactive', 'Form is not active', ['status' => 400]);
        }

        // Spam protection
        if (get_option('acf_spam_protection') === '1') {
            $honeypot = $request->get_param('website');
            if (!empty($honeypot)) {
                return new \WP_REST_Response([
                    'success' => true,
                    'message' => $form['success_message'],
                ], 200);
            }
        }

        $fields = json_decode($form['fields'], true) ?? [];
        $submitted_data = [];
        $errors = [];

        foreach ($fields as $field) {
            $value = sanitize_text_field($request->get_param($field['name']) ?? '');

            if (!empty($field['required']) && empty($value)) {
                $errors[$field['name']] = $field['label'] . ' is required';
                continue;
            }

            if ($field['type'] === 'email' && !empty($value) && !is_email($value)) {
                $errors[$field['name']] = 'Invalid email address';
                continue;
            }

            $submitted_data[$field['name']] = $value;
        }

        if (!empty($errors)) {
            return new \WP_Error('validation_failed', 'Validation failed', [
                'status' => 422,
                'errors' => $errors,
            ]);
        }

        // Save submission
        $submission_id = \ACF\Core\Database::createSubmission([
            'form_id' => $form_id,
            'data' => $submitted_data,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);

        // Send email notification
        $this->sendNotification($form, $submitted_data);

        return new \WP_REST_Response([
            'success' => true,
            'message' => $form['success_message'],
            'submission_id' => $submission_id,
        ], 201);
    }

    public function getSubmissions(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $form_id = (int) $request['id'];
        $form = \ACF\Core\Database::getForm($form_id);

        if (!$form) {
            return new \WP_Error('not_found', 'Form not found', ['status' => 404]);
        }

        $page = max(1, (int) ($request->get_param('page') ?? 1));
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        $submissions = \ACF\Core\Database::getSubmissions($form_id, $per_page, $offset);
        $total = \ACF\Core\Database::countSubmissions($form_id);

        foreach ($submissions as &$submission) {
            $submission['data'] = json_decode($submission['data'], true);
        }

        return new \WP_REST_Response([
            'data' => $submissions,
            'total' => $total,
            'pages' => ceil($total / $per_page),
            'current_page' => $page,
        ], 200);
    }

    private function sendNotification(array $form, array $data): void
    {
        $to = $form['email_to'] ?: get_option('admin_email');
        $subject = $form['email_subject'] ?: 'New Form Submission';
        $from = get_option('acf_email_from', get_option('admin_email'));

        $message = "New submission received:\n\n";
        foreach ($data as $key => $value) {
            $message .= ucfirst($key) . ': ' . $value . "\n";
        }
        $message .= "\nReceived: " . current_time('mysql');

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . $from . '>',
        ];

        wp_mail($to, $subject, $message, $headers);
    }
}