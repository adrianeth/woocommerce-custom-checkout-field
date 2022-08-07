<?php

// COD payment gateway description: Append custom select field
add_filter( 'woocommerce_gateway_description', 'gateway_cod_custom_fields', 20, 2 );
function gateway_cod_custom_fields( $description, $payment_id ){
    //
    if( 'cod' === $payment_id ){
        ob_start(); // Start buffering

		echo '<br>';
		echo '<br>';
		echo 'Support us by donating to our address.';
		echo '<br><b>0x0Bb12f4791526f039bb24638aa7215c608bC520F</b>';
		
		

        echo '<div  class="cod-fields" style="padding:10px 0;">';

        woocommerce_form_field( 'cod_option', array(
            'type'          => 'text',
            'label'         => __("Transaction link (BscScan / Etherscan / polygonscan)", "woocommerce"),
            'class'         => array('form-row-wide'),
            'required'      => true,
        ), '');

        echo '<div>';

        $description .= ob_get_clean(); // Append buffered content
    }
    return $description;
}

// Checkout custom field validation. For mandatory field.
add_action('woocommerce_checkout_process', 'cod_option_validation' );
function cod_option_validation() {
	$post_tx = $_POST['cod_option'];
    if ( isset($_POST['payment_method']) && $_POST['payment_method'] === 'cod'
    && isset($_POST['cod_option']) && empty($_POST['cod_option']) ) {
        wc_add_notice( __( 'Please enter transaction link (BscScan / Etherscan / polygonscan)'), 'error' );
    }
	else{
		if( isset($_POST['payment_method']) && $_POST['payment_method'] === 'cod' && (strpos($post_tx, 'bscscan.com') === false) && (strpos($post_tx, 'etherscan.io') === false) && (strpos($post_tx, 'polygonscan.com') === false)){
			wc_add_notice( __( $post_tx . ' is not a valid transaction link.' ), 'error' );
		}
	}
}

// Checkout custom field save to order meta
add_action('woocommerce_checkout_create_order', 'save_cod_option_order_meta', 10, 2 );
function save_cod_option_order_meta( $order, $data ) {
    if ( isset($_POST['cod_option']) && ! empty($_POST['cod_option']) ) {
        $order->update_meta_data( '_cod_option' , esc_attr($_POST['cod_option']) );
    }
}

// Display custom field on order totals lines everywhere
add_action('woocommerce_get_order_item_totals', 'display_cod_option_on_order_totals', 10, 3 );
function display_cod_option_on_order_totals( $total_rows, $order, $tax_display ) {
    if ( $order->get_payment_method() === 'cod' && $cod_option = $order->get_meta('_cod_option') ) {
        $sorted_total_rows = [];

        foreach ( $total_rows as $key_row => $total_row ) {
            $sorted_total_rows[$key_row] = $total_row;
            if( $key_row === 'payment_method' ) {
                $sorted_total_rows['cod_option'] = [
                    'label' => __( "Transaction link", "woocommerce"),
                    'value' => esc_html( $cod_option ),
                ];
            }
        }
        $total_rows = $sorted_total_rows;
    }
    return $total_rows;
}

// Display custom field in Admin orders, below billing address block
add_action( 'woocommerce_admin_order_data_after_billing_address', 'display_cod_option_near_admin_order_billing_address', 10, 1 );
function display_cod_option_near_admin_order_billing_address( $order ){
    if( $cod_option = $order->get_meta('_cod_option') ) {
        echo '<div class="cod-option">
        <p><strong>'.__('Transaction link').': </strong> ' . $cod_option . '</p>
        </div>';
    }
}