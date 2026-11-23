<?php
/**
 * Home — "Where we work": worldwide, in the visitor's hours.
 *
 * Compass Digital works remotely with clients worldwide (decided positioning,
 * docs/PROGRESS.md). The card shows the visitor's own local time (filled in by
 * ui.js; a plain label without JS), the regions we work with, how remote
 * projects run, and the contact lines from Site Settings. No street address is
 * shown here on purpose.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$compass_contact = compass_contact_details();
$reviews_url     = function_exists( 'compass_get_setting' ) ? compass_get_setting( 'reviews_url' ) : '';

$regions = array(
	__( 'North America', 'compass-digital' ),
	__( 'UK & Europe', 'compass-digital' ),
	__( 'Middle East', 'compass-digital' ),
	__( 'Asia', 'compass-digital' ),
	__( 'Australia & NZ', 'compass-digital' ),
);
?>
<section
	class="home-section local-area"
	id="worldwide"
	aria-labelledby="local-area-title"
	data-waypoint
	data-label="<?php esc_attr_e( 'Worldwide', 'compass-digital' ); ?>"
>
	<div class="container">
		<div class="section-header" data-reveal>
			<span class="section-header__eyebrow"><?php esc_html_e( 'Where we work', 'compass-digital' ); ?></span>
			<h2 id="local-area-title"><?php esc_html_e( 'Wherever you are,', 'compass-digital' ); ?> <em><?php esc_html_e( 'we work in your hours.', 'compass-digital' ); ?></em></h2>
			<p class="section-header__lede"><?php esc_html_e( 'Clients across time zones, one team. Calls when it suits you, progress you can see.', 'compass-digital' ); ?></p>
		</div>

		<div class="local-area__grid">
			<div class="local-area__card" data-reveal>
				<div class="local-area__head">
					<p class="local-area__time">
						<small><?php esc_html_e( 'Your local time', 'compass-digital' ); ?></small>
						<span data-local-time>--:--</span>
						<span class="local-area__zone" data-local-zone><?php esc_html_e( 'We plan calls around it.', 'compass-digital' ); ?></span>
					</p>
					<span class="local-area__chip" data-map-place><?php esc_html_e( 'Across every time zone', 'compass-digital' ); ?></span>
				</div>

				<div class="local-area__map" data-local-map aria-busy="true"></div>

				<div class="local-area__meta" data-map-meta hidden>
					<p class="local-area__coords">
						<span data-map-coords>&mdash;</span>
						<span class="local-area__source"><?php esc_html_e( 'from', 'compass-digital' ); ?> <span data-map-source><?php esc_html_e( 'your time zone', 'compass-digital' ); ?></span></span>
					</p>
					<p class="local-area__locate">
						<a href="#" class="link" data-map-locate><?php esc_html_e( 'Use my exact location', 'compass-digital' ); ?></a>
						<span class="local-area__msg" data-map-msg role="status" aria-live="polite"></span>
					</p>
				</div>

				<ul class="pills local-area__regions" aria-label="<?php esc_attr_e( 'Regions we work with', 'compass-digital' ); ?>">
					<?php foreach ( $regions as $region ) : ?>
						<li><?php echo esc_html( $region ); ?></li>
					<?php endforeach; ?>
					<li class="is-all"><?php esc_html_e( 'Anywhere online', 'compass-digital' ); ?></li>
				</ul>
			</div>

			<div class="local-area__side" data-reveal>
				<h3 class="local-area__subtitle"><?php esc_html_e( 'How remote works', 'compass-digital' ); ?></h3>
				<p><?php esc_html_e( 'Most projects start with a 30-minute video call where we listen first. After that you get a shared progress page, weekly updates and a named person to message.', 'compass-digital' ); ?></p>
				<p><a class="link" href="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>"><?php esc_html_e( 'Book a first call', 'compass-digital' ); ?></a></p>

				<?php if ( ! empty( $compass_contact['phone'] ) || ! empty( $compass_contact['email'] ) ) : ?>
					<h3 class="local-area__subtitle"><?php esc_html_e( 'Talk to us', 'compass-digital' ); ?></h3>
					<p class="local-area__contact">
						<?php if ( ! empty( $compass_contact['email'] ) ) : ?>
							<a href="<?php echo esc_url( 'mailto:' . $compass_contact['email'] ); ?>"><?php echo esc_html( $compass_contact['email'] ); ?></a>
						<?php endif; ?>
						<?php if ( ! empty( $compass_contact['phone'] ) ) : ?>
							<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $compass_contact['phone'] ) ); ?>"><?php echo esc_html( $compass_contact['phone'] ); ?></a>
						<?php endif; ?>
					</p>
				<?php endif; ?>

				<h3 class="local-area__subtitle"><?php esc_html_e( 'Reviews', 'compass-digital' ); ?></h3>
				<?php if ( $reviews_url ) : ?>
					<p>
						<?php esc_html_e( 'See what our clients say, or leave a review of your own.', 'compass-digital' ); ?>
						<a class="link" href="<?php echo esc_url( $reviews_url ); ?>" rel="noopener" target="_blank">
							<?php esc_html_e( 'Read our Google reviews', 'compass-digital' ); ?>
							<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'compass-digital' ); ?></span>
						</a>
					</p>
				<?php else : ?>
					<p><?php esc_html_e( 'Our Google reviews go live with our Business Profile. Check back soon.', 'compass-digital' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
