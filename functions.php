<?php

function itsme_disable_feed() {
    wp_die(__('Este WordPress tiene desactivados los feed.'), '', array( 'response' => 410 ));
}
foreach (['do_feed', 'do_feed_rdf', 'do_feed_rss', 'do_feed_rss2', 'do_feed_atom', 'do_feed_rss2_comments', 'do_feed_atom_comments'] as $feed) {
    add_action($feed, 'itsme_disable_feed', 1);
}

/**
 * WooCommerce Customizations
 */
// Add & Save custom field in WooCommerce coupon settings
add_action('woocommerce_coupon_options', 'add_coupon_text_field', 10);
add_action('woocommerce_coupon_options_save', 'save_coupon_text_field', 10, 2);

function add_coupon_text_field() {
    woocommerce_wp_text_input([
        'id'          => 'email_recipient',
        'label'       => __('Email recipient', 'woocommerce'),
        'description' => __('Send an email notification to a defined recipient'),
        'desc_tip'    => true,
    ]);
}

function save_coupon_text_field($post_id, $coupon) {
    if (isset($_POST['email_recipient'])) {
        $coupon->update_meta_data('email_recipient', sanitize_text_field($_POST['email_recipient']));
        $coupon->save();
    }
}

// Send custom email for orders with applied coupon
add_action('woocommerce_checkout_order_created', 'custom_email_for_orders_with_applied_coupon');

function custom_email_for_orders_with_applied_coupon($order) {
    foreach ($order->get_used_coupons() as $coupon_code) {
        $coupon = new WC_Coupon($coupon_code);
        $recipient = $coupon->get_meta('email_recipient');
        if ($recipient) {
            $subject = sprintf(__('Micole: Se ha aplicado el cupón "%s'), $coupon_code);
            $content = sprintf(__('Tras aplicar el cupón, el total de la compra ha sido de "%s" euros'), $order->get_total());
            wp_mail($recipient, $subject, $content);
        }
    }
}

// Customizations related to product categories and quantities
add_action('woocommerce_before_add_to_cart_quantity', 'woosuite_echo_qty_front_add_cart');

function woosuite_echo_qty_front_add_cart() {
    global $product;
    if (has_term('formacion', 'product_cat', $product->get_id())) {
        echo "<div class='qty-label'>Selecciona el número de plazas: </div>";
    }
}

// Modify add to cart text based on product price
add_filter('woocommerce_product_single_add_to_cart_text', 'woo_custom_cart_button_text');

function woo_custom_cart_button_text() {
    global $product;
    return ($product->get_price() >= 0) ? __('Comprar', 'woocommerce') : __('Más información', 'woocommerce');
}

// Remove SKU from WooCommerce
add_filter('wc_product_sku_enabled', '__return_false');

// Remove WP version number from scripts and styles
add_filter('style_loader_src', 'remove_css_js_version', 9999);
add_filter('script_loader_src', 'remove_css_js_version', 9999);

function remove_css_js_version($src) {
    return strpos($src, '?ver=') ? remove_query_arg('ver', $src) : $src;
}

// Rename checkout fields conditionally
add_filter('woocommerce_checkout_fields', 'conditionally_rename_checkout_fields', 25, 1);

function conditionally_rename_checkout_fields($fields) {
    // ... [your function content here]
}

// Ensure billing postcode is required
add_filter('woocommerce_checkout_fields', 'astra_custom_checkout_fields');

function astra_custom_checkout_fields($fields) {
    $fields['billing']['billing_postcode']['required'] = true;
    return $fields;
}

// Send an additional email for the "Formación" category after each order
add_action('woocommerce_thankyou', 'send_additional_email_for_formacion_category', 10, 1);

function send_additional_email_for_formacion_category($order_id) {
    // ... [your function content here]
}

// Filter payment methods at checkout based on cart items
add_filter('woocommerce_available_payment_gateways', 'filter_available_payment_methods');

function filter_available_payment_methods($available_gateways) {
    // ... [your function content here]
}
