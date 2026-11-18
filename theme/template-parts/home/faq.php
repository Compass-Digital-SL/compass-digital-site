<?php
/**
 * Home — FAQ (6 questions) with FAQPage structured data.
 *
 * The accordion markup works without JS (all answers visible); ui.js enhances
 * it into collapsible panels. The JSON-LD mirrors the same copy so the on-page
 * answers and the structured data never drift apart.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$faqs = array(
	array(
		'q' => __( 'How much does a website or project cost?', 'compass-digital' ),
		'a' => __( 'It depends on what you need, so we give you a fixed quote before any work starts — no hourly surprises. A simple business website costs less than a full online store or a custom POS. Tell us where you want to go and we’ll send a clear price for getting there.', 'compass-digital' ),
	),
	array(
		'q' => __( 'How long will it take?', 'compass-digital' ),
		'a' => __( 'Most business websites take a few weeks from first conversation to launch; larger stores, apps and custom software take longer. We agree a realistic timeline up front and keep you updated as we go, so you’re never left wondering.', 'compass-digital' ),
	),
	array(
		'q' => __( 'Do I actually own my website?', 'compass-digital' ),
		'a' => __( 'Yes — completely. The domain, hosting and accounts are set up in your name, and the site is yours to keep, move or hand to another developer at any time. We never hold your business hostage.', 'compass-digital' ),
	),
	array(
		'q' => __( 'Can I update the site myself?', 'compass-digital' ),
		'a' => __( 'Yes. We build on tools that are easy to edit, and we show you how to change text, images and prices yourself. For anything bigger, we’re a message away — but you’ll never have to pay us for a small tweak.', 'compass-digital' ),
	),
	array(
		'q' => __( 'Do you work with businesses outside Colombo?', 'compass-digital' ),
		'a' => __( 'Absolutely. We’re based in Colombo and work with businesses across Sri Lanka — Kandy, Galle, Jaffna and everywhere between — as well as Sri Lankan-owned businesses overseas. Most of the work happens remotely, simply and clearly.', 'compass-digital' ),
	),
	array(
		'q' => __( 'What happens after launch?', 'compass-digital' ),
		'a' => __( 'We stay on. We’re here to fix, update and grow what we built, and to answer questions in plain language. Launch is the start of the relationship, not the end of it.', 'compass-digital' ),
	),
);

$faq_schema = array(
	'@context'   => 'https://schema.org',
	'@type'      => 'FAQPage',
	'mainEntity' => array(),
);
foreach ( $faqs as $faq ) {
	$faq_schema['mainEntity'][] = array(
		'@type'          => 'Question',
		'name'           => wp_strip_all_tags( $faq['q'] ),
		'acceptedAnswer' => array(
			'@type' => 'Answer',
			'text'  => wp_strip_all_tags( $faq['a'] ),
		),
	);
}
?>
<section class="home-section faq-section" id="faq" aria-labelledby="faq-title">
	<div class="container">
		<div class="section-header" data-reveal>
			<span class="section-header__eyebrow"><?php esc_html_e( 'Questions', 'compass-digital' ); ?></span>
			<h2 id="faq-title"><?php esc_html_e( 'The things people usually ask.', 'compass-digital' ); ?></h2>
			<p class="section-header__lede"><?php esc_html_e( 'Straight answers. If yours isn’t here, just ask us for a quote and we’ll talk it through.', 'compass-digital' ); ?></p>
		</div>

		<div class="accordion faq" data-reveal>
			<?php foreach ( $faqs as $i => $faq ) : ?>
				<?php $panel_id = 'faq-panel-' . $i; ?>
				<div class="accordion__item">
					<h3 class="accordion__heading">
						<button
							type="button"
							class="accordion__trigger"
							aria-expanded="false"
							aria-controls="<?php echo esc_attr( $panel_id ); ?>"
							id="faq-trigger-<?php echo esc_attr( $i ); ?>"
						>
							<span><?php echo esc_html( $faq['q'] ); ?></span>
							<span class="accordion__icon" aria-hidden="true"></span>
						</button>
					</h3>
					<div
						class="accordion__panel"
						id="<?php echo esc_attr( $panel_id ); ?>"
						role="region"
						aria-labelledby="faq-trigger-<?php echo esc_attr( $i ); ?>"
					>
						<p><?php echo esc_html( $faq['a'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<script type="application/ld+json">
<?php echo wp_json_encode( $faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?>
</script>
