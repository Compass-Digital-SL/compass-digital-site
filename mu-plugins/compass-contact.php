<?php
/**
 * Compass Digital — contact details (SAMPLE / local).
 *
 * Must-use plugin: WordPress loads it automatically and it cannot be
 * deactivated, so these details survive theme and plugin changes. It overrides
 * the placeholders in theme/inc/nav.php (`compass_contact_details`) and feeds
 * the footer, the contact page, and the Organization / LocalBusiness schema.
 *
 * ⚠️  SAMPLE DATA — replace the social URLs (and confirm phone/email) before
 * go-live. Tracked in docs/GO-LIVE.md.
 *
 * @package CompassCore
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'compass_contact_details',
	function ( $details ) {
		// Phone shown to people; WhatsApp is digits only (E.164 without the +).
		$details['phone']    = '+94 76 427 8496';
		$details['whatsapp'] = '94764278496';
		$details['email']    = 'info@compassdigital.com';

		// Digital agency serving worldwide — keep the registered base, no local area push.
		$details['address']  = __( 'Colombo, Sri Lanka', 'compass-digital' );
		$details['reg_name'] = __( 'Compass Digital (Pvt) Ltd', 'compass-digital' );

		// Placeholder social profiles — swap for the real pages before launch.
		$details['social'] = array(
			'Facebook'  => 'https://www.facebook.com/compassdigital',
			'Instagram' => 'https://www.instagram.com/compassdigital',
			'LinkedIn'  => 'https://www.linkedin.com/company/compassdigital',
		);

		return $details;
	}
);
