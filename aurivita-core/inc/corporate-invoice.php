<?php
defined('ABSPATH') || exit;

function av_invoice_sanitize_text($value): string {
    return is_scalar($value) ? trim(sanitize_text_field((string) $value)) : '';
}

function av_invoice_validate_text($value, array $field): ?WP_Error {
    if ($field['required'] === true && $value === '') {
        return new WP_Error('av_invoice_required', sprintf(__('Please complete the %s field.', 'aurivita'), $field['label']));
    }
    if (mb_strlen((string) $value) > 120) {
        return new WP_Error('av_invoice_length', sprintf(__('%s en fazla 120 karakter olabilir.', 'aurivita'), $field['label']));
    }
    return null;
}

function av_invoice_validate_tax_number($value, array $field): ?WP_Error {
    if ($value === '' && $field['required'] !== true) return null;
    if (!preg_match('/\A[0-9]{10,11}\z/', (string) $value)) {
        return new WP_Error('av_invoice_tax_number', __('Enter a 10 or 11-digit tax ID.', 'aurivita'));
    }
    return null;
}

function av_register_corporate_invoice_fields(): void {
    if (!function_exists('woocommerce_register_additional_checkout_field') || version_compare(WC_VERSION, '9.9.0', '<')) return;
    $selected = [
        'type' => 'object',
        'required' => ['checkout'],
        'properties' => [
            'checkout' => [
                'type' => 'object',
                'required' => ['additional_fields'],
                'properties' => [
                    'additional_fields' => [
                        'type' => 'object',
                        'required' => ['aurivita/corporate-invoice'],
                        'properties' => ['aurivita/corporate-invoice' => ['const' => true]],
                    ],
                ],
            ],
        ],
    ];
    $label = __('I need a business invoice.', 'aurivita');
    woocommerce_register_additional_checkout_field([
        'id' => 'aurivita/corporate-invoice',
        'label' => $label,
        'optionalLabel' => $label,
        'location' => 'order',
        'type' => 'checkbox',
        'required' => false,
        'attributes' => ['data-av-invoice-field' => 'toggle'],
    ]);
    foreach ([
        'company-name' => __('Company name', 'aurivita'),
        'tax-number' => __('Tax ID', 'aurivita'),
        'tax-office' => __('Tax office', 'aurivita'),
    ] as $key => $label) {
        $tax_number = $key === 'tax-number';
        woocommerce_register_additional_checkout_field([
            'id' => 'aurivita/' . $key,
            'label' => $label,
            'optionalLabel' => $label,
            'location' => 'order',
            'type' => 'text',
            'required' => $selected,
            'hidden' => ['not' => $selected],
            'attributes' => array_merge([
                'data-av-invoice-field' => $key,
                'autocomplete' => $key === 'company-name' ? 'organization' : 'off',
                'maxLength' => $tax_number ? 11 : 120,
            ], $tax_number ? ['pattern' => '[0-9]{10,11}'] : []),
            'validation' => $tax_number ? ['type' => 'string', 'pattern' => '^[0-9]{10,11}$', 'maxLength' => 11] : ['type' => 'string', 'maxLength' => 120],
            'sanitize_callback' => 'av_invoice_sanitize_text',
            'validate_callback' => $tax_number ? 'av_invoice_validate_tax_number' : 'av_invoice_validate_text',
        ]);
    }
}
add_action('woocommerce_init', 'av_register_corporate_invoice_fields');

function av_invoice_clear_unselected_order_fields(WC_Order $order, WP_REST_Request $request): void {
    $fields = (array) $request->get_param('additional_fields');
    if (($fields['aurivita/corporate-invoice'] ?? null) !== false) return;
    foreach (['company-name', 'tax-number', 'tax-office'] as $key) {
        $order->delete_meta_data('_wc_other/aurivita/' . $key);
    }
}
add_action('woocommerce_store_api_checkout_update_order_from_request', 'av_invoice_clear_unselected_order_fields', 20, 2);

function av_invoice_filter_confirmation_fields(bool $show, array $field, array $fields, array $context): bool {
    if (!in_array($field['id'], ['aurivita/corporate-invoice', 'aurivita/company-name', 'aurivita/tax-number', 'aurivita/tax-office'], true)) return $show;
    $order = $context['order'] ?? null;
    return $show && $order instanceof WC_Order && $order->get_meta('_wc_other/aurivita/corporate-invoice', true) === '1';
}
add_filter('woocommerce_filter_fields_for_order_confirmation', 'av_invoice_filter_confirmation_fields', 10, 4);
