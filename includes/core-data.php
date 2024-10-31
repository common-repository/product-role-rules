<?php
/**
 * Role based pricing plugin data structure.
 *
 * @package    WordPress
 * @subpackage Role Based Pricing for WooCommerce
 * @since      1.0
 */

global $proler__;

$proler__ = array(
	'name'       => __( 'Product Role Rules', 'product-role-rules' ),
	'notice'     => array(),
	'has_pro'    => false,
	'prostate'   => 'none',
	'notice_gap' => 15, // days.
	'screen'     => array(
		'_page_proler-settings', // main setting page.
		'_page_proler-newrole',
		'product',
	),
	'url'        => array(
		'free'    => 'https://wordpress.org/plugins/product-role-rules/',
		'review'  => 'https://wordpress.org/support/plugin/product-role-rules/reviews/?rate=5#new-post',
		'support' => 'https://webfixlab.com/contact/',
		'pro'     => 'https://webfixlab.com/plugins/role-based-pricing-woocommerce/',
		'wc'      => 'https://wordpress.org/plugins/woocommerce/',
	),
);

// hook to modify global $proler__ data variable.
do_action( 'proler_modify_core_data' );
