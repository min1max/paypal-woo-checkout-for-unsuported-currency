<?php


//kn currency to WooCommerce

add_filter( 'woocommerce_currencies', 'add_kn_currency' );  

function add_kn_currency( $currencies ) {  
   $currencies['KN'] = __( 'Hrvatska_kuna', 'woocommerce' );  
   return $currencies;  
}  

//add currency symbol in WooCommerce

add_filter('woocommerce_currency_symbol', 'add_kn_currency_symbol', 10, 2);  

function add_kn_currency_symbol( $currency_symbol, $currency ) {  
  switch( $currency ) {  
  case 'KN': $currency_symbol = 'KN'; break;  
  }  
 return $currency_symbol;  
}  

add_filter( 'woocommerce_paypal_supported_currencies', 'add_kn_paypal_valid_currency' );       

function add_kn_paypal_valid_currency( $currencies ) {    
   array_push ( $currencies , 'KN' );  
   return $currencies;    
} 

//change 'KN' currency to '€' before checking out with Paypal

add_filter('woocommerce_paypal_args', 'convert_hrk_to_eur', 11 );  

function get_currency($from_Currency='HRK', $to_Currency='EUR') {
    $url = "http://www.google.com/finance/converter?a=1&from=$from_Currency&to=$to_Currency";
    $ch = curl_init();
    $timeout = 0;
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_USERAGENT,
                 "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $rawdata = curl_exec($ch);
    curl_close($ch);
    $data = explode('bld>', $rawdata);
    $data = explode($to_Currency, $data[1]);
    return round($data[0], 2);
}
function convert_hrk_to_eur($paypal_args){
  if ( $paypal_args['currency_code'] == 'KN'){  
    $hnb_tecajna_lista = '';
    $podaci = array();
    $datum = date('dmy');
    $hnb_tecajna_lista = 'http://www.hnb.hr/tecajn/f'.$datum.'.dat';
    $hnb_tecajna_lista = file_get_contents($hnb_tecajna_lista);
    $podaci = explode("\n", $hnb_tecajna_lista);
    $eur_lista = explode('    ', $podaci[12], 5);
    $eur_danas = ($eur_lista[2]);
    $eur_with_dot = str_replace(',', '.', $eur_danas);
    $eur_2dec = round($eur_with_dot, 2);
    $convert_rate = round($eur_2dec, 2);

	$paypal_args['currency_code'] = 'EUR'; //change KN to €  
    $i = 1;  
        while (isset($paypal_args['amount_' . $i])) {  
            $paypal_args['amount_' . $i] = round( $paypal_args['amount_' . $i] / $convert_rate, 2);
            ++$i;  
        }  
	}
	if ( $paypal_args['discount_amount_cart'] > 0 ) {
         $paypal_args['discount_amount_cart'] = round( $paypal_args['discount_amount_cart'] / $convert_rate, 2);
        }
		
	if ( $paypal_args['tax_cart'] > 0 ) {
         $paypal_args['tax_cart'] = round( $paypal_args['tax_cart'] / $convert_rate, 2);
        }
		
	if ( $paypal_args['shipping_1'] > 0 ) {
			          $paypal_args['shipping_1'] = round( $paypal_args['shipping_1'] / $convert_rate, 2);
		}


	return $paypal_args;  
}  

