<?php
/**
 * Breadcrumbs for inner pages.
 *
 * Semantic ordered list inside a labelled nav. The front page shows none.
 * Hierarchical pages list their ancestors; posts show their section. The last
 * crumb is the current page (no link, marked aria-current). BreadcrumbList
 * structured data is added in the SEO task.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

if ( is_front_page() ) {
	return;
}

$crumbs = array(
	array(
		'label' => __( 'Home', 'compass-digital' ),
		'url'   => home_url( '/' ),
	),
);

if ( is_page() ) {
	$ancestors = array_reverse( get_post_ancestors( get_the_ID() ) );
	foreach ( $ancestors as $ancestor_id ) {
		$crumbs[] = array(
			'label' => get_the_title( $ancestor_id ),
			'url'   => get_permalink( $ancestor_id ),
		);
	}
	$crumbs[] = array(
		'label' => get_the_title(),
		'url'   => '',
	);
} elseif ( is_singular( 'post' ) ) {
	$crumbs[] = array(
		'label' => __( 'Insights', 'compass-digital' ),
		'url'   => home_url( '/insights/' ),
	);
	$crumbs[] = array(
		'label' => get_the_title(),
		'url'   => '',
	);
} elseif ( is_search() ) {
	$crumbs[] = array(
		'label' => __( 'Search results', 'compass-digital' ),
		'url'   => '',
	);
} elseif ( is_404() ) {
	$crumbs[] = array(
		'label' => __( 'Page not found', 'compass-digital' ),
		'url'   => '',
	);
} else {
	$compass_title = wp_get_document_title();
	$crumbs[]      = array(
		'label' => $compass_title ? $compass_title : get_the_archive_title(),
		'url'   => '',
	);
}

if ( count( $crumbs ) < 2 ) {
	return;
}
?>
<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'compass-digital' ); ?>">
	<ol class="breadcrumbs__list">
		<?php
		$last = count( $crumbs ) - 1;
		foreach ( $crumbs as $index => $crumb ) :
			?>
			<li class="breadcrumbs__item">
				<?php if ( $index === $last || empty( $crumb['url'] ) ) : ?>
					<span class="breadcrumbs__current" aria-current="page"><?php echo esc_html( $crumb['label'] ); ?></span>
				<?php else : ?>
					<a class="breadcrumbs__link" href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['label'] ); ?></a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
