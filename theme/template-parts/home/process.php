<?php
/**
 * Home — "How we work": four steps with an animated line.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$steps = array(
	array(
		'title' => __( 'Listen', 'compass-digital' ),
		'text'  => __( 'We start with your business, not a template. We learn how you work, who your customers are, and what “better” actually looks like for you.', 'compass-digital' ),
	),
	array(
		'title' => __( 'Plan', 'compass-digital' ),
		'text'  => __( 'We agree on a clear scope, a fixed quote and a timeline — in plain language, with no surprises and nothing you don’t need.', 'compass-digital' ),
	),
	array(
		'title' => __( 'Build', 'compass-digital' ),
		'text'  => __( 'We design and build in the open, sharing progress as we go, so you can steer early and there are no big reveals that miss the mark.', 'compass-digital' ),
	),
	array(
		'title' => __( 'Support', 'compass-digital' ),
		'text'  => __( 'We don’t disappear after launch. We’re here to fix, update and grow what we built — and the site is always yours to take anywhere.', 'compass-digital' ),
	),
);
?>
<section class="home-section process" id="process" aria-labelledby="process-title" data-waypoint data-label="<?php esc_attr_e( 'Process', 'compass-digital' ); ?>">
	<div class="container">
		<div class="section-header" data-reveal>
			<span class="section-header__eyebrow"><?php esc_html_e( 'How we work', 'compass-digital' ); ?></span>
			<h2 id="process-title"><?php esc_html_e( 'A calm, clear way of working.', 'compass-digital' ); ?></h2>
			<p class="section-header__lede"><?php esc_html_e( 'Four steps, start to finish. You always know where things stand and what happens next.', 'compass-digital' ); ?></p>
		</div>

		<ol class="process__steps" data-reveal>
			<span class="process__line" aria-hidden="true"></span>
			<?php foreach ( $steps as $step ) : ?>
				<li class="process__step">
					<h3><?php echo esc_html( $step['title'] ); ?></h3>
					<p><?php echo esc_html( $step['text'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
