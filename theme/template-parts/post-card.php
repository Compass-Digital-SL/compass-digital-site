<?php
/**
 * Shared Insights post card.
 *
 * Reused by the Insights index (home.php) and category/archive listings
 * (archive.php) so the two never drift. Call inside a post loop (the post is set
 * up via the_post()). Emits `data-categories` (space-separated term slugs) so the
 * index filter chips can match it client-side — harmless elsewhere.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$card_id   = get_the_ID();
$cats      = get_the_category();
$cat_slugs = $cats ? wp_list_pluck( $cats, 'slug' ) : array();
$primary   = $cats ? $cats[0] : null;
$read_mins = compass_reading_time( $card_id );
?>
<article
	class="post-card"
	data-post-card
	data-categories="<?php echo esc_attr( implode( ' ', $cat_slugs ) ); ?>"
>
	<a class="post-card__link" href="<?php the_permalink(); ?>">
		<span class="post-card__media">
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
		</span>
		<span class="post-card__body">
			<span class="post-card__meta">
				<?php if ( $primary ) : ?>
					<span class="post-card__cat"><?php echo esc_html( $primary->name ); ?></span>
				<?php endif; ?>
				<span class="post-card__read">
					<?php
					/* translators: %d: minutes to read. */
					printf( esc_html__( '%d min read', 'compass-digital' ), (int) $read_mins );
					?>
				</span>
			</span>
			<span class="post-card__title"><?php the_title(); ?></span>
			<span class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></span>
			<span class="post-card__more"><?php esc_html_e( 'Read article', 'compass-digital' ); ?></span>
		</span>
	</a>
</article>
