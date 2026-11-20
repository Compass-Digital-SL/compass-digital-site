<?php
/**
 * Front page.
 *
 * Premium v2 hero: an oversized editorial headline beside a fine-line compass
 * rose whose needle follows the pointer (hero.js). Everything is real text and
 * real links, so it works with JavaScript off; motion only enhances it.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

/*
 * Compass rose geometry (800 x 800 viewBox, centre 400,400): a tick every 5°,
 * a longer tick and a bearing label every 30°, N/E/S/W at the cardinals.
 */
$compass_ticks  = '';
$compass_labels = '';
$compass_cardinals = array( 0 => 'N', 90 => 'E', 180 => 'S', 270 => 'W' );
for ( $compass_d = 0; $compass_d < 360; $compass_d += 5 ) {
	$compass_major = ( 0 === $compass_d % 30 );
	$compass_a     = deg2rad( $compass_d - 90 );
	$compass_r2    = $compass_major ? 350 : 362;
	$compass_ticks .= sprintf(
		'<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f"%s/>',
		400 + 372 * cos( $compass_a ),
		400 + 372 * sin( $compass_a ),
		400 + $compass_r2 * cos( $compass_a ),
		400 + $compass_r2 * sin( $compass_a ),
		$compass_major ? ' class="is-major"' : ''
	);
	if ( $compass_major ) {
		$compass_labels .= sprintf(
			'<text x="%.1f" y="%.1f">%s</text>',
			400 + 322 * cos( $compass_a ),
			404 + 322 * sin( $compass_a ),
			esc_html( isset( $compass_cardinals[ $compass_d ] ) ? $compass_cardinals[ $compass_d ] : str_pad( (string) $compass_d, 3, '0', STR_PAD_LEFT ) )
		);
	}
}

$compass_spec = array(
	array( '95', '+', __( 'Lighthouse score every site is built to', 'compass-digital' ) ),
	array( '&lt;2', 's', __( 'Page load we design for on mobile', 'compass-digital' ) ),
	array( '6', '', __( 'Services, one team to call', 'compass-digital' ) ),
	array( '100', '%', __( 'Yours to keep. Code, content and domain', 'compass-digital' ) ),
);
?>
<div class="front-page">
	<section
		class="hero hero--v2"
		id="hero"
		aria-labelledby="hero-title"
		data-hero
		data-waypoint
		data-label="<?php esc_attr_e( 'Home', 'compass-digital' ); ?>"
	>
		<div class="hero__light" aria-hidden="true"><span class="hero__light-a"></span><span class="hero__light-b"></span></div>

		<svg class="hero__rose" viewBox="0 0 800 800" fill="none" aria-hidden="true" focusable="false" data-rose>
			<g class="hero__ring hero__ring--a" stroke="currentColor">
				<circle cx="400" cy="400" r="372" stroke-opacity=".10"/>
				<circle cx="400" cy="400" r="340" stroke-opacity=".07"/>
				<g class="hero__ticks"><?php echo $compass_ticks; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Numbers formatted with sprintf above. ?></g>
				<g class="hero__labels"><?php echo $compass_labels; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped when built above. ?></g>
			</g>
			<g class="hero__ring hero__ring--b" stroke="currentColor" stroke-opacity=".08">
				<circle cx="400" cy="400" r="250" stroke-dasharray="2 10"/>
				<circle cx="400" cy="400" r="170"/>
			</g>
			<g class="hero__needle" data-needle>
				<path d="M400 150 418 400 400 650 382 400Z" stroke="currentColor" stroke-opacity=".16"/>
				<path class="hero__needle-n" d="M400 150 418 400 382 400Z"/>
				<path class="hero__needle-s" d="M400 650 418 400 382 400Z"/>
				<circle class="hero__hub" cx="400" cy="400" r="5"/>
			</g>
		</svg>

		<div class="hero__inner">
			<p class="eyebrow hero__eyebrow"><?php esc_html_e( 'Websites · SEO · POS systems — for businesses worldwide', 'compass-digital' ); ?></p>
			<h1 id="hero-title" class="hero__title">
				<span class="hero__line"><span><?php esc_html_e( 'Find your way', 'compass-digital' ); ?></span></span>
				<span class="hero__line"><span><em><?php esc_html_e( 'forward.', 'compass-digital' ); ?></em></span></span>
			</h1>
			<p class="hero__lede"><?php esc_html_e( 'Fast websites, search that brings enquiries, and POS systems that keep the queue moving. Built by one team, explained in plain language.', 'compass-digital' ); ?></p>
			<p class="hero__cta">
				<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>">
					<?php esc_html_e( 'Request a quote', 'compass-digital' ); ?> <span class="btn__arrow" aria-hidden="true">→</span>
				</a>
				<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/work/' ) ); ?>">
					<?php esc_html_e( 'See our work', 'compass-digital' ); ?>
				</a>
			</p>
			<p class="hero__meta">
				<span><b><?php esc_html_e( 'Fixed quotes', 'compass-digital' ); ?></b><?php esc_html_e( ', no surprises', 'compass-digital' ); ?></span>
				<span><b><?php esc_html_e( 'Reply within 1', 'compass-digital' ); ?></b> <?php esc_html_e( 'working day', 'compass-digital' ); ?></span>
				<span class="hero__bearing" aria-hidden="true" data-bearing-readout><?php esc_html_e( 'Heading', 'compass-digital' ); ?> <b>000° N</b></span>
			</p>
		</div>

		<div class="hero__spec-wrap">
			<dl class="hero__spec">
				<?php foreach ( $compass_spec as $compass_item ) : ?>
					<div>
						<dt><?php echo esc_html( $compass_item[2] ); ?></dt>
						<dd class="hero__spec-n"><?php echo wp_kses( $compass_item[0], array() ); ?><small><?php echo esc_html( $compass_item[1] ); ?></small></dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</div>
	</section>

	<?php
	// Home page sections. Each part is self-contained; the pain selector's panel
	// ids (seo / websites / pos / app / ecommerce / travel) are deep-linkable.
	get_template_part( 'template-parts/home/pain-selector' );
	get_template_part( 'template-parts/home/services' );
	get_template_part( 'template-parts/home/industries' );
	get_template_part( 'template-parts/home/work' );
	get_template_part( 'template-parts/home/process' );
	get_template_part( 'template-parts/home/proof' );
	get_template_part( 'template-parts/home/local-area' );
	get_template_part( 'template-parts/home/faq' );
	get_template_part( 'template-parts/home/cta' );
	?>

	<?php
	// If an editor has added content to the Home page, show it below the hero.
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			if ( '' !== trim( get_the_content() ) ) :
				?>
				<section class="home-section front-page__content">
					<div class="container entry-content">
						<?php the_content(); ?>
					</div>
				</section>
				<?php
			endif;
		endwhile;
	endif;
	?>
</div>
<?php
get_footer();
