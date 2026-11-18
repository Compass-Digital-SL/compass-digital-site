<?php
/**
 * Navigation data source.
 *
 * One place describes the whole site map (services, industries, company),
 * the primary header links and the contact/registration details. The header,
 * command menu, mobile sheet and footer all read from here, so there is no
 * duplicated markup and every link works with JavaScript disabled.
 *
 * Child service/industry pages land in later tasks; their URLs are derived
 * from the section base so they resolve as soon as those pages exist.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build a full URL from a site-relative path.
 *
 * Returns a raw URL; escape with esc_url() at the point of output.
 *
 * @param string $path Relative path, e.g. 'services/seo'.
 * @return string
 */
function compass_nav_url( $path = '' ) {
	return home_url( '/' . ltrim( $path, '/' ) );
}

/**
 * The site map, grouped into sections.
 *
 * Each group: key, label, optional `url` (the group landing page), and `items`
 * (label + url). Filterable so content tasks can extend it.
 *
 * @return array
 */
function compass_nav_map() {
	$map = array(
		'services'   => array(
			'label' => __( 'Services', 'compass-digital' ),
			'url'   => compass_nav_url( 'services/' ),
			'items' => array(
				array(
					'label' => __( 'Website design & development', 'compass-digital' ),
					'url'   => compass_nav_url( 'services/website-design-development/' ),
				),
				array(
					'label' => __( 'SEO', 'compass-digital' ),
					'url'   => compass_nav_url( 'services/seo/' ),
				),
				array(
					'label' => __( 'POS systems', 'compass-digital' ),
					'url'   => compass_nav_url( 'services/pos-systems/' ),
				),
				array(
					'label' => __( 'Mobile app development', 'compass-digital' ),
					'url'   => compass_nav_url( 'services/mobile-app-development/' ),
				),
				array(
					'label' => __( 'Ecommerce websites', 'compass-digital' ),
					'url'   => compass_nav_url( 'services/ecommerce-websites/' ),
				),
				array(
					'label' => __( 'Custom software', 'compass-digital' ),
					'url'   => compass_nav_url( 'services/custom-software/' ),
				),
			),
		),
		'industries' => array(
			'label' => __( 'Industries', 'compass-digital' ),
			'url'   => compass_nav_url( 'industries/' ),
			'items' => array(
				array(
					'label' => __( 'Retail shops', 'compass-digital' ),
					'url'   => compass_nav_url( 'industries/retail-shops/' ),
				),
				array(
					'label' => __( 'Restaurants & cafes', 'compass-digital' ),
					'url'   => compass_nav_url( 'industries/restaurants-cafes/' ),
				),
				array(
					'label' => __( 'Travel & tourism', 'compass-digital' ),
					'url'   => compass_nav_url( 'industries/travel-tourism/' ),
				),
				array(
					'label' => __( 'Ecommerce brands', 'compass-digital' ),
					'url'   => compass_nav_url( 'industries/ecommerce-brands/' ),
				),
				array(
					'label' => __( 'Corporates & SMEs', 'compass-digital' ),
					'url'   => compass_nav_url( 'industries/corporates-smes/' ),
				),
			),
		),
		'work'       => array(
			'label' => __( 'Work', 'compass-digital' ),
			'url'   => compass_nav_url( 'work/' ),
			'items' => array(),
		),
		'company'    => array(
			'label' => __( 'Company', 'compass-digital' ),
			'url'   => '',
			'items' => array(
				array(
					'label' => __( 'About', 'compass-digital' ),
					'url'   => compass_nav_url( 'about/' ),
				),
				array(
					'label' => __( 'Insights', 'compass-digital' ),
					'url'   => compass_nav_url( 'insights/' ),
				),
				array(
					'label' => __( 'Contact', 'compass-digital' ),
					'url'   => compass_nav_url( 'contact/' ),
				),
				array(
					'label' => __( 'Get a quote', 'compass-digital' ),
					'url'   => compass_nav_url( 'get-a-quote/' ),
				),
			),
		),
	);

	/**
	 * Filter the full navigation map.
	 *
	 * @param array $map The grouped site map.
	 */
	return apply_filters( 'compass_nav_map', $map );
}

/**
 * Top-level links shown in the desktop header, in order.
 *
 * Services and Industries carry their `items` so the header can render
 * dropdowns; Work/About/Insights are plain links.
 *
 * @return array
 */
function compass_primary_links() {
	$map = compass_nav_map();

	$links = array(
		$map['services'],
		$map['industries'],
		$map['work'],
		array(
			'label' => __( 'About', 'compass-digital' ),
			'url'   => compass_nav_url( 'about/' ),
			'items' => array(),
		),
		array(
			'label' => __( 'Insights', 'compass-digital' ),
			'url'   => compass_nav_url( 'insights/' ),
			'items' => array(),
		),
	);

	/**
	 * Filter the header's primary links.
	 *
	 * @param array $links Ordered top-level links.
	 */
	return apply_filters( 'compass_primary_links', $links );
}

/**
 * Contact and registration details.
 *
 * Placeholders until the owner provides real details — override any value with
 * the `compass_contact_details` filter (e.g. in a small must-use plugin) rather
 * than editing the theme. The WhatsApp number is digits only, no "+" or spaces.
 *
 * @return array
 */
function compass_contact_details() {
	$details = array(
		'phone'        => '+94 11 000 0000',
		'whatsapp'     => '94110000000',
		'whatsapp_msg' => __( 'Hi Compass Digital, I would like a quote.', 'compass-digital' ),
		'email'        => 'hello@compassdigital.lk',
		'address'      => __( 'Colombo, Sri Lanka', 'compass-digital' ),
		'reg_name'     => __( 'Compass Digital (Pvt) Ltd', 'compass-digital' ),
		'reg_no'       => '',
		'social'       => array(
			'Facebook'  => '',
			'Instagram' => '',
			'LinkedIn'  => '',
		),
	);

	/**
	 * Filter contact + registration details.
	 *
	 * @param array $details Contact details.
	 */
	return apply_filters( 'compass_contact_details', $details );
}

/**
 * Build a wa.me link from the contact details.
 *
 * Returns a raw URL; escape with esc_url() at the point of output.
 *
 * @return string
 */
function compass_whatsapp_url() {
	$c   = compass_contact_details();
	$url = 'https://wa.me/' . preg_replace( '/\D/', '', $c['whatsapp'] );
	if ( ! empty( $c['whatsapp_msg'] ) ) {
		$url = add_query_arg( 'text', rawurlencode( $c['whatsapp_msg'] ), $url );
	}
	return $url;
}
