<?php
/**
 * Front page.
 *
 * The hero is "The Expedition": a dark scene with a brass compass whose needle
 * follows the pointer, ringed by six bearings that jump to the tailored sections
 * built in Task 10. Everything here is real text and real links, so it works with
 * JavaScript off; hero.js / motion.js enhance it only when motion is welcome.
 *
 * Section scaffolding only; real home-page content lands in Task 10.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

/**
 * The six bearings, placed clockwise from the top. `angle` is degrees from north
 * (used by CSS custom properties for placement and by the needle for glow); each
 * `target` is the on-page id of the tailored section Task 10 will add.
 */
$compass_bearings = array(
	array(
		'label'  => __( 'Be found', 'compass-digital' ),
		'desc'   => __( 'SEO', 'compass-digital' ),
		'target' => 'seo',
		'angle'  => 0,
	),
	array(
		'label'  => __( 'Look better', 'compass-digital' ),
		'desc'   => __( 'Websites', 'compass-digital' ),
		'target' => 'websites',
		'angle'  => 60,
	),
	array(
		'label'  => __( 'Sell smarter', 'compass-digital' ),
		'desc'   => __( 'POS systems', 'compass-digital' ),
		'target' => 'pos',
		'angle'  => 120,
	),
	array(
		'label'  => __( 'Get an app', 'compass-digital' ),
		'desc'   => __( 'Mobile apps', 'compass-digital' ),
		'target' => 'app',
		'angle'  => 180,
	),
	array(
		'label'  => __( 'Sell online', 'compass-digital' ),
		'desc'   => __( 'Ecommerce', 'compass-digital' ),
		'target' => 'ecommerce',
		'angle'  => 240,
	),
	array(
		'label'  => __( 'Welcome travellers', 'compass-digital' ),
		'desc'   => __( 'Travel & tourism', 'compass-digital' ),
		'target' => 'travel',
		'angle'  => 300,
	),
);

$hero_heading = __( 'Find your way forward.', 'compass-digital' );
?>
<div class="front-page">
	<section
		class="hero hero--expedition"
		id="hero"
		aria-labelledby="hero-title"
		data-hero
		data-waypoint
		data-label="<?php esc_attr_e( 'Start', 'compass-digital' ); ?>"
	>
		<!-- Decorative glow layers; parallax targets, pure CSS otherwise. -->
		<div class="hero__field" aria-hidden="true" data-parallax="0.04"></div>

		<div class="hero__inner">
			<div class="hero__copy">
				<h1 id="hero-title" class="hero__title" data-reveal-letters>
					<?php
					// Per-word spans keep words unbroken for wrapping; JS splits to
					// letters for the reveal. Screen readers read the heading as text.
					$hero_words = preg_split( '/(\s+)/', $hero_heading, -1, PREG_SPLIT_NO_EMPTY );
					foreach ( $hero_words as $word ) {
						printf(
							'<span class="hero__word">%s</span> ',
							esc_html( $word )
						);
					}
					?>
				</h1>
				<p class="hero__subline" data-reveal><?php esc_html_e( 'Which way is your business heading?', 'compass-digital' ); ?></p>
				<p class="hero__cta" data-reveal>
					<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>">
						<?php esc_html_e( 'Request a quote', 'compass-digital' ); ?>
					</a>
					<a class="btn btn--ghost" href="#seo" data-hero-explore>
						<?php esc_html_e( 'Explore', 'compass-digital' ); ?>
					</a>
				</p>
			</div>

			<div class="hero__compass" data-compass>
				<?php
				// The ring + needle. Decorative: the bearings below carry the real,
				// labelled links, so the SVG is hidden from assistive tech.
				?>
				<svg class="compass" viewBox="0 0 200 200" aria-hidden="true" focusable="false">
					<defs>
						<radialGradient id="compass-face" cx="50%" cy="42%" r="70%">
							<stop offset="0%" class="compass__face-stop compass__face-stop--top" />
							<stop offset="100%" class="compass__face-stop compass__face-stop--bottom" />
						</radialGradient>
					</defs>
					<circle class="compass__face" cx="100" cy="100" r="92" fill="url(#compass-face)" />
					<circle class="compass__ring" cx="100" cy="100" r="92" />
					<circle class="compass__ring compass__ring--inner" cx="100" cy="100" r="72" />
					<g class="compass__ticks">
						<?php
						for ( $i = 0; $i < 72; $i++ ) {
							$major = ( 0 === $i % 9 );
							printf(
								'<line x1="100" y1="12" x2="100" y2="%1$s" class="compass__tick%2$s" transform="rotate(%3$d 100 100)" />',
								esc_attr( $major ? '22' : '17' ),
								esc_attr( $major ? ' compass__tick--major' : '' ),
								esc_attr( $i * 5 )
							);
						}
						?>
					</g>
					<g class="compass__cardinals">
						<text x="100" y="34" class="compass__cardinal compass__cardinal--n">N</text>
						<text x="170" y="104" class="compass__cardinal">E</text>
						<text x="100" y="174" class="compass__cardinal">S</text>
						<text x="30" y="104" class="compass__cardinal">W</text>
					</g>
					<g class="compass__needle" data-needle>
						<polygon class="compass__needle-n" points="100,38 108,100 92,100" />
						<polygon class="compass__needle-s" points="100,162 92,100 108,100" />
						<circle class="compass__hub" cx="100" cy="100" r="8" />
						<circle class="compass__hub-dot" cx="100" cy="100" r="2.5" />
					</g>
				</svg>

				<ul class="bearings" data-bearings>
					<?php foreach ( $compass_bearings as $bearing ) : ?>
						<li
							class="bearing"
							style="--bearing-angle: <?php echo esc_attr( (int) $bearing['angle'] ); ?>deg;"
							data-bearing
							data-angle="<?php echo esc_attr( (int) $bearing['angle'] ); ?>"
						>
							<a class="bearing__link" href="#<?php echo esc_attr( $bearing['target'] ); ?>">
								<span class="bearing__label"><?php echo esc_html( $bearing['label'] ); ?></span>
								<span class="bearing__desc"><?php echo esc_html( $bearing['desc'] ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>

				<button type="button" class="hero__motion-toggle" data-motion-toggle hidden>
					<?php esc_html_e( 'Enable motion', 'compass-digital' ); ?>
				</button>
			</div>
		</div>

		<span class="hero__scroll-hint" aria-hidden="true" data-reveal>
			<?php esc_html_e( 'Scroll to begin', 'compass-digital' ); ?>
		</span>
	</section>

	<?php
	// Home page sections (Task 10). Each part is self-contained; ids line up
	// with the hero bearings (seo / websites / pos / app / ecommerce / travel).
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
