<?php
/**
 * Entry summary card for archive/blog listings.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'entry-card' ); ?>>
	<h2 class="entry-card__title">
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</h2>
	<?php if ( has_excerpt() ) : ?>
		<p class="entry-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
	<?php endif; ?>
	<p class="entry-card__meta">
		<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
	</p>
</article>
