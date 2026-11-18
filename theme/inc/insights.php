<?php
/**
 * Insights blog wiring (Task 14).
 *
 * The blog lives at /insights/ (the static Posts page) with posts at
 * /insights/<slug>/. This file provides the pieces the Insights templates use:
 * reading time, a table of contents built from the post's own headings, related
 * posts by category, lightweight share links (no third-party scripts), the
 * category filter facets for the archive, and Article + Blog JSON-LD. Titles and
 * metas for the blog index and single posts are set here too, matching the
 * services/work pattern; Task 16 generalises them site-wide.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

/**
 * Estimated reading time in whole minutes (>= 1), at ~200 words per minute.
 *
 * @param int|null $post_id Post ID (defaults to current).
 * @return int
 */
function compass_reading_time( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$text    = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
	$words   = str_word_count( $text );
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Turn an h2/h3 slug fragment into a stable anchor id.
 *
 * @param string $text Heading text (may contain HTML).
 * @param int    $n    Fallback counter for uniqueness.
 * @return string
 */
function compass_heading_id( $text, $n ) {
	$slug = sanitize_title( wp_strip_all_tags( $text ) );
	return $slug ? $slug : 'section-' . $n;
}

/**
 * Process post content once: add anchor ids to h2/h3 and collect a table of
 * contents. Returns [ 'content' => string, 'toc' => array{level,id,text}[] ].
 *
 * @param string $html The rendered post content.
 * @return array{content:string,toc:array<int,array<string,mixed>>}
 */
function compass_process_post_content( $html ) {
	$toc = array();
	$n   = 0;

	$content = preg_replace_callback(
		'/<h([23])([^>]*)>(.*?)<\/h\1>/is',
		function ( $m ) use ( &$toc, &$n ) {
			++$n;
			$level = (int) $m[1];
			$attrs = $m[2];
			$text  = $m[3];

			// Reuse an existing id if the author set one, else derive a stable one.
			if ( preg_match( '/\sid=("|\')(.*?)\1/i', $attrs, $idm ) ) {
				$id = $idm[2];
			} else {
				$id     = compass_heading_id( $text, $n );
				$attrs .= ' id="' . esc_attr( $id ) . '"';
			}

			$toc[] = array(
				'level' => $level,
				'id'    => $id,
				'text'  => wp_strip_all_tags( $text ),
			);

			return '<h' . $level . $attrs . '>' . $text . '</h' . $level . '>';
		},
		$html
	);

	return array(
		'content' => null === $content ? $html : $content,
		'toc'     => $toc,
	);
}

/**
 * Related posts by shared category, newest first.
 *
 * @param int $post_id Current post.
 * @param int $count   How many to return.
 * @return WP_Post[]
 */
function compass_related_posts( $post_id, $count = 3 ) {
	$cats = wp_get_post_categories( $post_id );
	$args = array(
		'post_type'           => 'post',
		'posts_per_page'      => $count,
		'post__not_in'        => array( $post_id ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
	);
	if ( $cats ) {
		$args['category__in'] = $cats;
	}
	$q       = new WP_Query( $args );
	$related = $q->posts;

	// Top up with recent posts if a thin category left us short.
	if ( count( $related ) < $count ) {
		$have    = wp_list_pluck( $related, 'ID' );
		$fill    = new WP_Query(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => $count - count( $related ),
				'post__not_in'        => array_merge( array( $post_id ), $have ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'orderby'             => 'date',
				'order'               => 'DESC',
			)
		);
		$related = array_merge( $related, $fill->posts );
	}
	return $related;
}

/**
 * Share links for a post — plain URLs, no third-party scripts. The "copy" entry
 * is enhanced by insights.js into a copy-to-clipboard button; without JS it is
 * a normal link to the post.
 *
 * @param string $url   Canonical post URL.
 * @param string $title Post title.
 * @return array<int,array<string,string>>
 */
function compass_share_links( $url, $title ) {
	$e_url   = rawurlencode( $url );
	$e_title = rawurlencode( $title );

	return array(
		array(
			'key'   => 'whatsapp',
			'label' => __( 'Share on WhatsApp', 'compass-digital' ),
			'short' => __( 'WhatsApp', 'compass-digital' ),
			'url'   => 'https://wa.me/?text=' . $e_title . '%20' . $e_url,
		),
		array(
			'key'   => 'facebook',
			'label' => __( 'Share on Facebook', 'compass-digital' ),
			'short' => __( 'Facebook', 'compass-digital' ),
			'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . $e_url,
		),
		array(
			'key'   => 'linkedin',
			'label' => __( 'Share on LinkedIn', 'compass-digital' ),
			'short' => __( 'LinkedIn', 'compass-digital' ),
			'url'   => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $e_url,
		),
		array(
			'key'   => 'x',
			'label' => __( 'Share on X', 'compass-digital' ),
			'short' => __( 'X', 'compass-digital' ),
			'url'   => 'https://twitter.com/intent/tweet?text=' . $e_title . '&url=' . $e_url,
		),
		array(
			'key'   => 'email',
			'label' => __( 'Share by email', 'compass-digital' ),
			'short' => __( 'Email', 'compass-digital' ),
			'url'   => 'mailto:?subject=' . $e_title . '&body=' . $e_url,
		),
		array(
			'key'   => 'copy',
			'label' => __( 'Copy link', 'compass-digital' ),
			'short' => __( 'Copy link', 'compass-digital' ),
			'url'   => $url,
		),
	);
}

/**
 * Category filter facets for the Insights archive: categories that actually have
 * published posts, ordered by name.
 *
 * @return WP_Term[]
 */
function compass_insights_categories() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'orderby'    => 'name',
		)
	);
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Author display name and bio for the author box.
 *
 * @param int $post_id Post ID.
 * @return array{name:string,bio:string,url:string}
 */
function compass_post_author( $post_id ) {
	$author_id = (int) get_post_field( 'post_author', $post_id );
	$name      = get_the_author_meta( 'display_name', $author_id );
	$bio       = get_the_author_meta( 'description', $author_id );
	if ( ! $bio ) {
		$bio = __( 'The Compass Digital team — a Sri Lankan software and digital services firm helping businesses find their way online with clear, dependable work.', 'compass-digital' );
	}
	return array(
		'name' => $name ? $name : get_bloginfo( 'name' ),
		'bio'  => $bio,
		'url'  => get_author_posts_url( $author_id ),
	);
}

/**
 * Document title for the Insights index and single posts.
 *
 * @param string $title Default.
 * @return string
 */
function compass_insights_document_title( $title ) {
	if ( is_home() ) {
		return __( 'Insights — Practical Digital Advice for Sri Lanka | Compass', 'compass-digital' );
	}
	if ( is_singular( 'post' ) ) {
		return get_the_title( get_queried_object_id() ) . ' | Compass Digital';
	}
	if ( is_category() ) {
		/* translators: %s: category name. */
		return sprintf( __( '%s — Insights | Compass Digital', 'compass-digital' ), single_cat_title( '', false ) );
	}
	return $title;
}
add_filter( 'pre_get_document_title', 'compass_insights_document_title' );

/**
 * Meta description for the Insights index and single posts.
 */
function compass_insights_head() {
	$description = '';

	if ( is_home() ) {
		$description = __( 'Practical, jargon-free advice on websites, SEO, POS and selling online — written for Sri Lankan business owners by Compass Digital.', 'compass-digital' );
	} elseif ( is_singular( 'post' ) ) {
		$id          = get_queried_object_id();
		$description = has_excerpt( $id ) ? get_the_excerpt( $id ) : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $id ) ), 30 );
	} elseif ( is_category() ) {
		$description = wp_strip_all_tags( get_the_archive_description() );
		if ( ! $description ) {
			/* translators: %s: category name. */
			$description = sprintf( __( 'Insights on %s from Compass Digital — plain-language advice for Sri Lankan businesses.', 'compass-digital' ), single_cat_title( '', false ) );
		}
	}

	if ( $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	}
}
add_action( 'wp_head', 'compass_insights_head', 1 );

/**
 * Build the Article (BlogPosting) schema for a single post.
 *
 * @param int    $post_id Post ID.
 * @param string $url     Canonical URL.
 * @return array<string,mixed>
 */
function compass_article_schema( $post_id, $url ) {
	$author = compass_post_author( $post_id );

	$schema = array(
		'@context'         => 'https://schema.org',
		'@type'            => 'BlogPosting',
		'headline'         => get_the_title( $post_id ),
		'url'              => $url,
		'mainEntityOfPage' => $url,
		'datePublished'    => get_the_date( 'c', $post_id ),
		'dateModified'     => get_the_modified_date( 'c', $post_id ),
		'author'           => array(
			'@type' => 'Person',
			'name'  => $author['name'],
		),
		'publisher'        => array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		),
	);

	$excerpt = get_the_excerpt( $post_id );
	if ( $excerpt ) {
		$schema['description'] = wp_strip_all_tags( $excerpt );
	}
	if ( has_post_thumbnail( $post_id ) ) {
		$img = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), 'large' );
		if ( $img ) {
			$schema['image'] = $img;
		}
	}
	$cats = get_the_category( $post_id );
	if ( $cats ) {
		$schema['articleSection'] = wp_list_pluck( $cats, 'name' );
	}
	return $schema;
}
