<?php
/**
 * Template Name: Styleguide
 *
 * Hidden design-system reference (noindex via inc/styleguide.php). Renders every
 * token and component. Not linked publicly; reach it at /styleguide/.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<style>
	/* Styleguide-only layout — inline so it never ships to real pages. */
	.styleguide { max-width: var(--wide-width); margin-inline: auto; padding-inline: var(--gutter); }
	.styleguide__intro { padding-block: var(--space-12) var(--space-8); }
	.styleguide__section { padding-block: var(--space-12); border-top: 1px solid var(--border); }
	.sg-row { display: flex; flex-wrap: wrap; gap: var(--space-4); align-items: center; }
	.sg-swatches { display: grid; gap: var(--space-4); grid-template-columns: repeat(auto-fill, minmax(10rem, 1fr)); }
	.sg-swatch { margin: 0; font-size: var(--fs-sm); }
	.sg-swatch__chip { display: block; height: 4rem; border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: var(--space-2); }
	.sg-swatch code, .styleguide code { color: var(--text-muted); font-size: 0.85em; }
	.sg-bars { display: flex; flex-direction: column; gap: var(--space-2); }
	.sg-bar { display: flex; align-items: center; gap: var(--space-3); }
	.sg-bar__fill { display: block; block-size: 0.75rem; background: var(--accent); border-radius: var(--radius-sm); }
	.sg-demo { display: grid; place-items: center; min-height: 5rem; background: var(--surface-2); border: 1px solid var(--border); }
</style>
<article class="styleguide">
	<header class="styleguide__intro">
		<p class="section-header__eyebrow">Compass Digital</p>
		<h1>Design system</h1>
		<p class="section-header__lede">
			Dark base, brass accent, teal secondary, ivory text. Every value below is a token.
		</p>
	</header>

	<!-- Colour -->
	<section class="styleguide__section" aria-labelledby="sg-colour">
		<div class="section-header">
			<p class="section-header__eyebrow">Tokens</p>
			<h2 id="sg-colour">Colour</h2>
		</div>
		<div class="sg-swatches">
			<?php
			$swatches = array(
				'Navy (surface)'  => 'var(--color-navy)',
				'Navy 700'        => 'var(--color-navy-700)',
				'Ink (sunken)'    => 'var(--color-ink)',
				'Brass (accent)'  => 'var(--color-brass)',
				'Teal'            => 'var(--color-teal)',
				'Teal 300 (link)' => 'var(--color-teal-300)',
				'Ivory (text)'    => 'var(--color-ivory)',
				'Muted text'      => 'var(--text-muted)',
			);
			foreach ( $swatches as $label => $value ) :
				?>
				<figure class="sg-swatch">
					<span class="sg-swatch__chip" style="background: <?php echo esc_attr( $value ); ?>"></span>
					<figcaption><?php echo esc_html( $label ); ?><br><code><?php echo esc_html( $value ); ?></code></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
		<p class="field__hint">Contrast table and rules: see <code>docs/DESIGN.md</code>. All text pairs pass WCAG&nbsp;2.2&nbsp;AA.</p>
	</section>

	<!-- Typography -->
	<section class="styleguide__section" aria-labelledby="sg-type">
		<div class="section-header">
			<p class="section-header__eyebrow">Tokens</p>
			<h2 id="sg-type">Typography</h2>
		</div>
		<h1>Display — Fraunces (fs-2xl)</h1>
		<h2>Heading 2 — Fraunces (fs-xl)</h2>
		<h3>Heading 3 — Fraunces (fs-lg)</h3>
		<p>Body — Inter (fs-base). Find your way forward. Websites, SEO and POS systems that are clear, dependable and built around how you work.</p>
		<p class="field__hint">Small / hint text (fs-sm).</p>
	</section>

	<!-- Spacing, radii, elevation -->
	<section class="styleguide__section" aria-labelledby="sg-scale">
		<div class="section-header">
			<p class="section-header__eyebrow">Tokens</p>
			<h2 id="sg-scale">Spacing, radii &amp; elevation</h2>
		</div>
		<div class="sg-bars">
			<?php foreach ( array( '2', '4', '6', '8', '12', '16' ) as $step ) : ?>
				<div class="sg-bar">
					<span class="sg-bar__fill" style="inline-size: var(--space-<?php echo esc_attr( $step ); ?>)"></span>
					<code>space-<?php echo esc_html( $step ); ?></code>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="grid" style="margin-top: var(--space-6)">
			<div class="sg-demo" style="border-radius: var(--radius-sm)"><code>radius-sm</code></div>
			<div class="sg-demo" style="border-radius: var(--radius)"><code>radius</code></div>
			<div class="sg-demo" style="border-radius: var(--radius-lg)"><code>radius-lg</code></div>
		</div>
		<div class="grid" style="margin-top: var(--space-6)">
			<div class="sg-demo" style="box-shadow: var(--shadow-1)"><code>shadow-1</code></div>
			<div class="sg-demo" style="box-shadow: var(--shadow-2)"><code>shadow-2</code></div>
			<div class="sg-demo" style="box-shadow: var(--shadow-3)"><code>shadow-3</code></div>
		</div>
	</section>

	<!-- Buttons & links -->
	<section class="styleguide__section" aria-labelledby="sg-buttons">
		<div class="section-header">
			<p class="section-header__eyebrow">Components</p>
			<h2 id="sg-buttons">Buttons &amp; links</h2>
		</div>
		<div class="sg-row">
			<a class="btn btn--primary" href="#sg-buttons">Request a quote</a>
			<a class="btn btn--ghost" href="#sg-buttons">See our work</a>
			<button class="btn btn--primary" type="button">Button element</button>
		</div>
		<p style="margin-top: var(--space-4)">
			A paragraph with a <a class="link" href="#sg-buttons">text link</a> inside it, teal and underlined on hover.
		</p>
	</section>

	<!-- Tags & badges -->
	<section class="styleguide__section" aria-labelledby="sg-tags">
		<div class="section-header">
			<p class="section-header__eyebrow">Components</p>
			<h2 id="sg-tags">Tags &amp; badges</h2>
		</div>
		<div class="sg-row">
			<span class="tag">SEO</span>
			<span class="tag">POS systems</span>
			<span class="tag">Ecommerce</span>
		</div>
		<div class="sg-row" style="margin-top: var(--space-4)">
			<span class="badge badge--brass">Featured</span>
			<span class="badge badge--teal">New</span>
			<span class="badge">Neutral</span>
		</div>
	</section>

	<!-- Cards -->
	<section class="styleguide__section" aria-labelledby="sg-cards">
		<div class="section-header">
			<p class="section-header__eyebrow">Components</p>
			<h2 id="sg-cards">Cards</h2>
		</div>
		<div class="grid">
			<div class="card">
				<h3 class="card__title">Website design</h3>
				<p class="card__text">A solid card on a raised surface with a soft border and shadow.</p>
			</div>
			<div class="card card--glass">
				<h3 class="card__title">Glass hover card</h3>
				<p class="card__text">Restrained glass — hover to lift. Used only on header, hover cards and the command menu.</p>
			</div>
			<div class="card">
				<h3 class="card__title">POS systems</h3>
				<p class="card__text">Till, stock and reporting built around how the shop actually runs.</p>
			</div>
		</div>
	</section>

	<!-- Section header -->
	<section class="styleguide__section" aria-labelledby="sg-sechead">
		<div class="section-header">
			<p class="section-header__eyebrow">Components</p>
			<h2 id="sg-sechead">Section header</h2>
		</div>
		<div class="section-header">
			<p class="section-header__eyebrow">What we do</p>
			<h2>Clear sites, real search visibility</h2>
			<p class="section-header__lede">Eyebrow, heading and lede with consistent rhythm — the standard section opener.</p>
		</div>
	</section>

	<!-- Form fields -->
	<section class="styleguide__section" aria-labelledby="sg-forms">
		<div class="section-header">
			<p class="section-header__eyebrow">Components</p>
			<h2 id="sg-forms">Form fields</h2>
		</div>
		<form class="container--narrow" style="padding-inline: 0" onsubmit="return false">
			<div class="field">
				<label class="field__label" for="sg-name">Name</label>
				<input class="field__control" id="sg-name" type="text" placeholder="Your name">
			</div>
			<div class="field">
				<label class="field__label" for="sg-email">Email</label>
				<input class="field__control" id="sg-email" type="email" placeholder="you@business.lk">
				<span class="field__hint">We only use this to reply to your quote.</span>
			</div>
			<div class="field">
				<label class="field__label" for="sg-service">Service</label>
				<select class="field__control" id="sg-service">
					<option>Website design and development</option>
					<option>SEO</option>
					<option>POS systems</option>
				</select>
			</div>
			<div class="field field--error">
				<label class="field__label" for="sg-msg">Message</label>
				<textarea class="field__control" id="sg-msg" placeholder="Tell us about your project"></textarea>
				<span class="field__error">Please add a short description.</span>
			</div>
			<button class="btn btn--primary" type="submit">Request a quote</button>
		</form>
	</section>

	<!-- Accordion -->
	<section class="styleguide__section" aria-labelledby="sg-accordion">
		<div class="section-header">
			<p class="section-header__eyebrow">Components</p>
			<h2 id="sg-accordion">Accordion</h2>
		</div>
		<div class="accordion">
			<?php
			$faqs = array(
				'How long does a website take?' => 'Most sites take four to eight weeks, depending on the number of pages and content readiness.',
				'Do you offer SEO?'             => 'Yes — technical SEO, on-page and local SEO for businesses worldwide.',
				'Can you build a POS system?'   => 'We build POS systems for retail shops, restaurants and cafes.',
			);
			$i    = 0;
			foreach ( $faqs as $q => $a ) :
				++$i;
				$pid = 'sg-acc-panel-' . $i;
				$tid = 'sg-acc-trigger-' . $i;
				?>
				<div class="accordion__item">
					<h3 style="margin: 0">
						<button class="accordion__trigger" id="<?php echo esc_attr( $tid ); ?>" type="button" aria-expanded="<?php echo 1 === $i ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $pid ); ?>">
							<?php echo esc_html( $q ); ?>
							<span class="accordion__icon" aria-hidden="true"></span>
						</button>
					</h3>
					<div class="accordion__panel" id="<?php echo esc_attr( $pid ); ?>" role="region" aria-labelledby="<?php echo esc_attr( $tid ); ?>">
						<p style="margin: 0"><?php echo esc_html( $a ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- Tabs -->
	<section class="styleguide__section" aria-labelledby="sg-tabs">
		<div class="section-header">
			<p class="section-header__eyebrow">Components</p>
			<h2 id="sg-tabs">Tabs</h2>
		</div>
		<div class="tabs">
			<div class="tabs__list" role="tablist" aria-label="Services">
				<button class="tabs__tab" role="tab" id="sg-tab-1" aria-controls="sg-panel-1" aria-selected="true" tabindex="0">Websites</button>
				<button class="tabs__tab" role="tab" id="sg-tab-2" aria-controls="sg-panel-2" aria-selected="false" tabindex="-1">SEO</button>
				<button class="tabs__tab" role="tab" id="sg-tab-3" aria-controls="sg-panel-3" aria-selected="false" tabindex="-1">POS</button>
			</div>
			<div class="tabs__panel" role="tabpanel" id="sg-panel-1" aria-labelledby="sg-tab-1">
				<p>Fast, accessible websites built on a classic WordPress theme — no page builders.</p>
			</div>
			<div class="tabs__panel" role="tabpanel" id="sg-panel-2" aria-labelledby="sg-tab-2" hidden>
				<p>Technical, on-page and local SEO that gets you found.</p>
			</div>
			<div class="tabs__panel" role="tabpanel" id="sg-panel-3" aria-labelledby="sg-tab-3" hidden>
				<p>POS systems for shops, restaurants and cafes.</p>
			</div>
		</div>
	</section>

	<!-- Light section -->
	<section class="styleguide__section section--light" aria-labelledby="sg-light">
		<div class="section-header">
			<p class="section-header__eyebrow">Surface</p>
			<h2 id="sg-light">Light section</h2>
			<p class="section-header__lede">The rare light block. Ink text on ivory, teal links, white cards.</p>
		</div>
		<div class="grid">
			<div class="card">
				<h3 class="card__title">On ivory</h3>
				<p class="card__text">Ink text on a white card, <a class="link" href="#sg-light">teal link</a>.</p>
			</div>
			<div class="card">
				<h3 class="card__title">Still readable</h3>
				<p class="card__text">Muted text stays AA on the light surface too.</p>
			</div>
		</div>
	</section>
</article>
<?php
get_footer();
