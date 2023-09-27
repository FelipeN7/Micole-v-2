/**
 * Desactivar feed
 */
 function itsme_disable_feed() {
    wp_die(__('Este WordPress tiene desactivados los feed.'), '', array( 'response' => 410 ));
   }
   
   add_action('do_feed', 'itsme_disable_feed', 1);
   add_action('do_feed_rdf', 'itsme_disable_feed', 1);
   add_action('do_feed_rss', 'itsme_disable_feed', 1);
   add_action('do_feed_rss2', 'itsme_disable_feed', 1);
   add_action('do_feed_atom', 'itsme_disable_feed', 1);
   add_action('do_feed_rss2_comments', 'itsme_disable_feed', 1);
   add_action('do_feed_atom_comments', 'itsme_disable_feed', 1);
   
   // Add a custom field to Admin coupon settings pages
   add_action( 'woocommerce_coupon_options', 'add_coupon_text_field', 10 );
   function add_coupon_text_field() {
       woocommerce_wp_text_input( array(
           'id'                => 'email_recipient',
           'label'             => __( 'Email recipient', 'woocommerce' ),
           'placeholder'       => '',
           'description'       => __( 'Send an email notification to a defined recipient' ),
           'desc_tip'    => true, // Or false
   
       ) );
   }
   
   // Save the custom field value from Admin coupon settings pages
   add_action( 'woocommerce_coupon_options_save', 'save_coupon_text_field', 10, 2 );
   function save_coupon_text_field( $post_id, $coupon ) {
       if( isset( $_POST['email_recipient'] ) ) {
           $coupon->update_meta_data( 'email_recipient', sanitize_text_field( $_POST['email_recipient'] ) );
           $coupon->save();
       }
   }
   
   // For Woocommerce version 4.3+
   add_action( 'woocommerce_checkout_order_created', 'custom_email_for_orders_with_applied_coupon' );
   function custom_email_for_orders_with_applied_coupon( $order ){
       $used_coupons = $order->get_used_coupons();
       $price = $order->get_total();
       if( ! empty($used_coupons) ){
           foreach ( $used_coupons as $coupon_code ) {
               $coupon    = new WC_Coupon( $coupon_code ); // WC_Coupon Object
               $recipient = $coupon->get_meta('email_recipient'); // get recipient
   
               if( ! empty($recipient) ) {
                   $subject = sprintf( __('Micole: Se ha aplicado el cupón "%s'), $coupon_code );
                   $content = sprintf( __('Tras aplicar el cupón, el total de la compra ha sido de "%s" euros'), $price );
                   wp_mail( $recipient, $subject, $content ); // Send email
               }
           }
       }
   }
   
   // para añadir etiqueta cantidad
   add_action( 'woocommerce_before_add_to_cart_quantity', 'woosuite_echo_qty_front_add_cart' );
   function woosuite_echo_qty_front_add_cart() {
       global $product;
       if ( has_term( 'formacion', 'product_cat', $product->get_id() ) ) {
           echo "<div class='qty-label'>Selecciona el número de plazas: </div>";
       }
   }
   
   //para no mostrar cantidades en caso de kit herramientas o asesoramiento
   add_action( 'woocommerce_before_add_to_cart_quantity', 'woosuite_echo_qty_front_add_cart' );
   function bbloomer_only_one_in_cart( $passed, $added_product_id ) {
       if (is_product_category('Formación')) {{}}
           else
       wc_empty_cart();
       return $passed;
   }
   
   add_filter('woocommerce_product_single_add_to_cart_text', 'woo_custom_cart_button_text');
   function woo_custom_cart_button_text() {
       global $product;
       $product_title = $product->get_name();
       $product_price = $product->get_price();
        echo "<script>console.log('Debug Objects: " . $product_price . "' );</script>";
       if($product_price >= 0){
           return __('Comprar', 'woocommerce');
       }
       else{
           return __('Más información', 'woocommerce');
       }
   }
   
   add_filter( 'wc_product_sku_enabled', '__return_false' );
   
   // 2.1 +
   
   // remove wp version number from scripts and styles
   function remove_css_js_version( $src ) {
       if( strpos( $src, '?ver=' ) )
           $src = remove_query_arg( 'ver', $src );
       return $src;
   }
   add_filter( 'style_loader_src', 'remove_css_js_version', 9999 );
   add_filter( 'script_loader_src', 'remove_css_js_version', 9999 );
   
   //para renombrar campos del checkout
   add_filter( 'woocommerce_checkout_fields', 'conditionally_rename_checkout_fields', 25, 1 );
   function conditionally_rename_checkout_fields( $fields ) {
   
       // HERE the defined product Categories
       $categories = array('Formación');
       $found = false;
       // CHECK CART ITEMS: search for items from our defined product category
       foreach ( WC()->cart->get_cart() as $cart_item ){
           if( has_term( $categories, 'product_cat', $cart_item['product_id'] ) ) {
               $found = true;
               break;
           }
       }
       // If a special category is in the cart, rename some shipping fields
       if ( $found ) {
           //rename the billing fields
           $fields['billing']['billing_first_name']['placeholder'] = 'Denominación social del centro';
           $fields['billing']['billing_first_name']['label'] = 'Denominación social del centro';
           $fields['billing']['billing_last_name']['placeholder'] = 'CIF';
           $fields['billing']['billing_last_name']['label'] = 'CIF';		
       }
       else {
         unset($fields['billing']['billing_address_1']);
         unset($fields['billing']['billing_postcode']);
         unset($fields['billing']['billing_city']);
         unset($fields['billing']['billing_country']);
       }
       return $fields;
   }
   /*
   //para quitar cupón en cursos formación
   add_filter( 'woocommerce_coupons_enabled', 'product_category_hide_cart_coupon_field', 20, 1 );
   function product_category_hide_cart_coupon_field( $enabled ) {
       // Only on frontend
       if( is_admin() ) 
          return $enabled;
   
       // Set your special categories names, slugs or IDs here in the array:
       $categories = array('Formación');
       $found = false; // Initializing
   
       // Loop through cart items
       foreach ( WC()->cart->get_cart() as $cart_item ) {
           if ( has_term( $categories, 'product_cat', $cart_item['product_id'] ) ){
               $found = true;
               break; // We stop the loop
           }
       }
   
       if ( $found && is_cart() ) {
           $enabled = false;
       }
       return $enabled;
   }
   */
   add_filter( 'woocommerce_checkout_fields', 'astra_custom_checkout_fields' );
   
   function astra_custom_checkout_fields( $fields ) {
       $fields['billing']['billing_postcode']['required'] = true;
       return $fields;
   }
   
   //para no generar factura en pedidos de Formación
   add_filter( 'wpo_wcpdf_document_is_allowed', 'wpo_wcpdf_not_allow_invoice_for_certain_categories', 10, 2 );
   function wpo_wcpdf_not_allow_invoice_for_certain_categories ( $condition, $document ) {
       if ( $document->type == 'invoice' ) {
   
           //Set categories here (comma separated)
           $not_allowed_cats = array( 'formacion','Formación' );
   
           $order_cats = array();
           if ( $order = $document->order ) {
               //Get order categories
               foreach ( $order->get_items() as $item_id => $item ) {
                   // get categories for item, requires product
                   if ( $product = $item->get_product() ) {
                       $id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
                       $terms = get_the_terms( $id, 'product_cat' );
   
                       if ( empty( $terms ) ) {
                           continue;
                       } else {
                           foreach ( $terms as $key => $term ) {
                               $order_cats[$term->term_id] = $term->slug;
                           }
                       }
                   }
               }
           }
   
           // get array of category matches
           $cat_matches = array_intersect( $not_allowed_cats, $order_cats );
           if ( count( $cat_matches ) > 0 ) {
               return false; // 1 or more matches: do not allow invoice
           }
       }
       return $condition;
   }
   
   // Enviar correo adicional para la categoría "Formación" después de cada pedido
   add_action('woocommerce_thankyou', 'send_additional_email_for_formacion_category', 10, 1);
   /*si queremos que se mande para cada pedido realizado add_action('woocommerce_thankyou', 'send_additional_email_for_formacion_category', 10, 1);*/
   /*si queremos que se mande para cada pedido completado add_action('woocommerce_order_status_completed', 'send_additional_email_for_formacion_category', 10, 1);*/
   function send_additional_email_for_formacion_category($order_id) {
       $order = wc_get_order($order_id);
   
       // Verificar si la categoría del producto está presente en el pedido
       $category_slug = 'formacion'; // Reemplaza 'formacion' con el slug de tu categoría de producto
       $found = false;
   
       foreach ($order->get_items() as $item) {
           if (has_term($category_slug, 'product_cat', $item->get_product_id())) {
               $found = true;
               break;
           }
       }
   
       // Si la categoría está presente, enviar el correo adicional
       if ($found) {
           $to = $order->get_billing_email();
           $subject = 'IMPORTANTE: Formación Micole. Registro de alumnos';
           $message = '¡Gracias por contratar la formación Micole! Para reservar las plazas, necesitamos que cada alumno que vaya a participar complete el siguiente formulario: https://tally.so/r/nPDRGB';
           $headers = array(
               'Bcc: notificaciones@micole.net', // Añade la dirección de correo oculta
               'Content-Type: text/html'
           );
           wp_mail($to, $subject, $message, $headers);
       }
   }
   
   // Función para filtrar los métodos de pago en el proceso de pago
   function filter_available_payment_methods($available_gateways) {
       // Verificar si hay productos en el carrito que no sean de la categoría "Formación"
       if (WC()->cart) {
           $products = WC()->cart->get_cart_contents();
           $non_formacion_products = false;
           foreach ($products as $product) {
               $product_id = $product['product_id'];
               $categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'slugs'));
               if (!in_array('formacion', $categories)) {
                   $non_formacion_products = true;
                   break;
               }
           }
           // Filtrar los métodos de pago según los productos en el carrito
           if ($non_formacion_products) {
               unset($available_gateways['bacs']); // Ocultar transferencia bancaria
               // Imprimir contenido del array para inspeccionar las claves disponibles
               unset($available_gateways['stripe_sepa']); // Ocultar domiciliación SEPA
           }
       }
       return $available_gateways;
   }
   add_filter('woocommerce_available_payment_gateways', 'filter_available_payment_methods');
