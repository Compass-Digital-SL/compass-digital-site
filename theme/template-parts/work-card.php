<?php
/**
 * Shared Work (case study) card.
 *
 * One card component, reused by the home "Recent work" preview and the /work/
 * archive, so the two never drift. Call inside a Work loop (the post is already
 * set up via the_post()). Reads the case study's client, year and the first
 * result line (falling back to the excerpt).
 *
 * Emits `data-services` / `data-industries` (space-separated term slugs) so the
 * archive's filter chips can match it client-side — harmless elsewhere.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$card_id   = get_the_ID();
$client    = get_post_meta( $card_id, '_compass_client', true );
$work_year = get_post_meta( $card_id, '_compass_year', true );
$results   = get_post_meta( $card_id, '_compass_results', true );

// Lead result: the first non-empty line of the results field, else the excerpt.
$result = '';
if ( $results ) {
	$lines  = preg_split( '/\r\n|\r|\n/', (string) $results );
	$result = trim( (string) reset( $lines ) );
}
if ( '' === $result ) {
	$result = wp_trim_words( get_the_excerpt(), 12 );
}

$meta_bits = array_filter( array( $client, $work_year ) );

// Term slugs for filtering.
$service_slugs  = wp_get_post_terms( $card_id, 'service', array( 'fields' => 'slugs' ) );
$industry_slugs = wp_get_post_terms( $card_id, 'industry', array( 'fields' => 'slugs' ) );
$service_slugs  = is_wp_error( $service_slugs ) ? array() : $service_slugs;
$industry_slugs = is_wp_error( $industry_slugs ) ? array() : $industry_slugs;
?>
<a
	class="work-card"
	href="<?php the_permalink(); ?>"
	data-work-card
	data-services="<?php echo esc_attr( implode( ' ', $service_slugs ) ); ?>"
	data-industries="<?php echo esc_attr( implode( ' ', $industry_slugs ) ); ?>"
>
	<div class="work-card__media">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail(
				'medium_large',
				array(
					'loading'  => 'lazy',
					'decoding' => 'async',
					'alt'      => esc_attr( get_the_title() ),
				)
			);
		}
		?>
	</div>
	<div class="work-card__body">
		<?php if ( ! empty( $meta_bits ) ) : ?>
			<span class="work-card__meta"><?php echo esc_html( implode( ' · ', $meta_bits ) ); ?></span>
		<?php endif; ?>
		<h3 class="work-card__title"><?php the_title(); ?></h3>
		<?php if ( $result ) : ?>
			<p class="work-card__result"><?php echo esc_html( $result ); ?></p>
		<?php endif; ?>
	</div>
</a>
