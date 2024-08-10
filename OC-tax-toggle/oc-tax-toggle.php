<?php
  
/**
* Plugin Name: OC-tax-toggle
* Description: This plugin allows create  Text-plugin.
* Version: 1.0
* Copyright: 2024
* Text Domain: Oc-tax-toggle-plugin
* Domain Path: /languages 
*/

function tax_toggle_shortcode() {

    $include_tax = isset($_COOKIE['include_tax']) ? $_COOKIE['include_tax'] : 'yes';

    ob_start();
    ?>
    <div class="tax-toggle">
       
        <a href="javascript:void(0)" id="exclude-vat-button" data-include-tax="no" class="<?php echo $include_tax === 'no' ? 'active' : ''; ?>">
            FÖRETAG
        </a>
		 <a href="javascript:void(0)" id="include-vat-button" data-include-tax="yes" class="<?php echo $include_tax === 'yes' ? 'active' : ''; ?>">
           PRIVAT
        </a>
    </div>
    <script type="text/javascript">
       document.addEventListener('DOMContentLoaded', function() {
			var includeButton = document.getElementById('include-vat-button');
			var excludeButton = document.getElementById('exclude-vat-button');

			includeButton.addEventListener('click', function() {
				document.cookie = 'include_tax=yes;path=/';
				triggerCartUpdate();
			});

			excludeButton.addEventListener('click', function() {
				document.cookie = 'include_tax=no;path=/';
				triggerCartUpdate();
			});

			function triggerCartUpdate() {
				jQuery('body').trigger('wc_fragment_refresh');
				location.reload();
			}
		});

    </script>
    <style>
		#exclude-vat-button{
			    border-right: 1px solid #919191;
    padding-right: 5px;
    margin-right: 5px;
		}
		.tax-toggle a{
			color:#929292;
			font-size: 13px;
		}
		
        .tax-toggle a.active {
            font-weight: bold;
			color:#555555;
        }
    </style>
    <?php
    $output = ob_get_clean();
    return $output;
}
add_shortcode('tax_toggle', 'tax_toggle_shortcode');


// add_action('wp_footer', 'tax_toggle_button');

function clear_cart_prices_on_tax_toggle() {
    if ( isset($_COOKIE['include_tax']) ) {
        if ( isset($_GET['clear_cart_prices']) && $_GET['clear_cart_prices'] === '1' ) {
            WC()->cart->calculate_totals();
            WC()->cart->set_session();
            wc_add_notice('Cart prices updated based on tax toggle.', 'success');
        }
    }
}
add_action('init', 'clear_cart_prices_on_tax_toggle');

function add_clear_cart_prices_param( $url ) {
    if ( isset($_COOKIE['include_tax']) ) {
        $url = add_query_arg('clear_cart_prices', '1', $url);
    }
    return $url;
}
add_filter('woocommerce_get_checkout_url', 'add_clear_cart_prices_param');
add_filter('woocommerce_get_cart_url', 'add_clear_cart_prices_param');

function custom_display_prices_including_tax() {
    $include_tax = isset($_COOKIE['include_tax']) ? $_COOKIE['include_tax'] : 'yes';
    return $include_tax === 'yes';
}
add_filter('woocommerce_cart_display_prices_including_tax', 'custom_display_prices_including_tax');


function custom_display_shop_prices_including_tax( $price, $product ) {
    $include_tax = isset($_COOKIE['include_tax']) ? $_COOKIE['include_tax'] : 'yes';

    if ( $include_tax === 'no' ) {
        $tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
        $taxes = WC_Tax::calc_tax( $product->get_price(), $tax_rates, true );
        $price_excl_tax = wc_price( $product->get_price() - array_sum( $taxes ) );
        $price = $price_excl_tax." excl tax";
    }

    return $price;
}
add_filter( 'woocommerce_get_price_html', 'custom_display_shop_prices_including_tax', 10, 2 );
add_filter( 'woocommerce_get_variation_price_html', 'custom_display_shop_prices_including_tax', 10, 2 );
