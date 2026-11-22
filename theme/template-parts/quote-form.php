<?php
/**
 * The quote / contact form.
 *
 * One real <form> with every field in the DOM, so it works fully without
 * JavaScript as a single long form. `quote.js` (loaded only where this form
 * exists) layers a 3-step wizard on top. Validation runs on the client (native
 * + JS) and again on the server (compass-core). Failed submits replay the
 * visitor's input and show per-field errors via the flash helpers.
 *
 * @param array $args {
 *     @type string $form    'quote' or 'contact'. Default 'quote'.
 *     @type string $submit  Submit button label.
 * }
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$cf_args    = wp_parse_args(
	$args ?? array(),
	array(
		'form'   => 'quote',
		'submit' => __( 'Send my request', 'compass-digital' ),
	)
);
$is_contact = 'contact' === $cf_args['form'];

$services   = compass_quote_services();
$industries = compass_quote_industries();
$timelines  = compass_quote_timelines();
$budgets    = compass_quote_budgets();
$methods    = compass_quote_contact_methods();
$flash      = compass_quote_flash();
$cf_errors  = $flash['errors'] ?? array();

// Steps: labels for the (JS) progress indicator.
$steps = array(
	__( 'What you need', 'compass-digital' ),
	__( 'Your project', 'compass-digital' ),
	__( 'Your details', 'compass-digital' ),
);
?>
<form
	id="quote-form"
	class="quote-form"
	method="post"
	action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
	data-quote-form
>
	<input type="hidden" name="action" value="compass_quote" />
	<input type="hidden" name="compass_form" value="<?php echo esc_attr( $is_contact ? 'contact' : 'quote' ); ?>" />
	<input type="hidden" name="source" value="<?php echo esc_url( get_permalink() ); ?>" />
	<input type="hidden" name="compass_ts" value="<?php echo esc_attr( time() ); ?>" />
	<?php wp_nonce_field( 'compass_quote', 'compass_nonce' ); ?>

	<?php // Honeypot — hidden from people, irresistible to bots. ?>
	<div class="quote-hp" aria-hidden="true">
		<label for="compass_hp_url"><?php esc_html_e( 'Leave this field empty', 'compass-digital' ); ?></label>
		<input type="text" id="compass_hp_url" name="compass_hp_url" tabindex="-1" autocomplete="off" />
	</div>

	<?php if ( ! empty( $cf_errors ) ) : ?>
		<div class="quote-errors" role="alert" tabindex="-1" data-quote-errors>
			<p class="quote-errors__title"><?php esc_html_e( 'Please check the highlighted fields:', 'compass-digital' ); ?></p>
			<ul>
				<?php foreach ( $cf_errors as $field => $message ) : ?>
					<li>
						<?php if ( '_form' === $field ) : ?>
							<?php echo esc_html( $message ); ?>
						<?php else : ?>
							<a href="#quote-<?php echo esc_attr( $field ); ?>"><?php echo esc_html( $message ); ?></a>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php // Progress (shown only when JS enhances the form into steps). ?>
	<ol class="quote-progress" data-quote-progress hidden>
		<?php foreach ( $steps as $i => $label ) : ?>
			<li class="quote-progress__step<?php echo 0 === $i ? ' is-current' : ''; ?>">
				<span class="quote-progress__num"><?php echo esc_html( $i + 1 ); ?></span>
				<span class="quote-progress__label"><?php echo esc_html( $label ); ?></span>
			</li>
		<?php endforeach; ?>
	</ol>

	<!-- Step 1 — what you need -->
	<fieldset class="quote-step" data-quote-step="0">
		<legend class="quote-step__legend"><?php esc_html_e( 'What you need', 'compass-digital' ); ?></legend>

		<div class="field<?php echo isset( $cf_errors['services'] ) ? ' field--error' : ''; ?>">
			<span class="field__label" id="quote-services"><?php esc_html_e( 'What can we help with?', 'compass-digital' ); ?> <span class="field__req">*</span></span>
			<span class="field__hint"><?php esc_html_e( 'Pick everything that applies.', 'compass-digital' ); ?></span>
			<div class="quote-checks" role="group" aria-labelledby="quote-services">
				<?php foreach ( $services as $slug => $label ) : ?>
					<label class="quote-check">
						<input
							type="checkbox"
							name="services[]"
							value="<?php echo esc_attr( $slug ); ?>"
							<?php checked( compass_quote_checked( 'services', $slug, $label ) ); ?>
						/>
						<span><?php echo esc_html( $label ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
			<?php if ( isset( $cf_errors['services'] ) ) : ?>
				<span class="field__error"><?php echo esc_html( $cf_errors['services'] ); ?></span>
			<?php endif; ?>
		</div>

		<div class="field">
			<label class="field__label" for="quote-industry"><?php esc_html_e( 'Your industry', 'compass-digital' ); ?></label>
			<select class="field__control" id="quote-industry" name="industry">
				<?php
				$current_industry = compass_quote_value( 'industry' );
				foreach ( $industries as $slug => $label ) :
					?>
					<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( strtolower( $current_industry ), strtolower( $slug ) ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</fieldset>

	<!-- Step 2 — your project -->
	<fieldset class="quote-step" data-quote-step="1">
		<legend class="quote-step__legend"><?php esc_html_e( 'Your project', 'compass-digital' ); ?></legend>

		<div class="field<?php echo isset( $cf_errors['message'] ) ? ' field--error' : ''; ?>">
			<label class="field__label" for="quote-message"><?php esc_html_e( 'Tell us about your project', 'compass-digital' ); ?> <span class="field__req">*</span></label>
			<span class="field__hint"><?php esc_html_e( 'What are you trying to do, and what does a win look like? A couple of lines is plenty.', 'compass-digital' ); ?></span>
			<textarea class="field__control" id="quote-message" name="message" rows="5" required><?php echo esc_textarea( compass_quote_value( 'message' ) ); ?></textarea>
			<?php if ( isset( $cf_errors['message'] ) ) : ?>
				<span class="field__error"><?php echo esc_html( $cf_errors['message'] ); ?></span>
			<?php endif; ?>
		</div>

		<div class="quote-row">
			<div class="field">
				<label class="field__label" for="quote-timeline"><?php esc_html_e( 'Timeline', 'compass-digital' ); ?></label>
				<select class="field__control" id="quote-timeline" name="timeline">
					<?php
					$current_timeline = compass_quote_value( 'timeline' );
					foreach ( $timelines as $slug => $label ) :
						?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current_timeline, $slug ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="field">
				<label class="field__label" for="quote-budget"><?php esc_html_e( 'Budget (optional)', 'compass-digital' ); ?></label>
				<select class="field__control" id="quote-budget" name="budget" aria-describedby="quote-budget-hint">
					<?php
					$current_budget = compass_quote_value( 'budget' );
					foreach ( $budgets as $slug => $label ) :
						?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current_budget, $slug ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<span class="field__hint" id="quote-budget-hint"><?php esc_html_e( 'A rough guide, in Sri Lankan rupees (LKR). Working from abroad? Give any figure or currency in your message — we work with clients worldwide.', 'compass-digital' ); ?></span>
			</div>
		</div>
	</fieldset>

	<!-- Step 3 — your details -->
	<fieldset class="quote-step" data-quote-step="2">
		<legend class="quote-step__legend"><?php esc_html_e( 'Your details', 'compass-digital' ); ?></legend>

		<div class="quote-row">
			<div class="field<?php echo isset( $cf_errors['name'] ) ? ' field--error' : ''; ?>">
				<label class="field__label" for="quote-name"><?php esc_html_e( 'Name', 'compass-digital' ); ?> <span class="field__req">*</span></label>
				<input class="field__control" type="text" id="quote-name" name="name" autocomplete="name" required value="<?php echo esc_attr( compass_quote_value( 'name' ) ); ?>" />
				<?php if ( isset( $cf_errors['name'] ) ) : ?>
					<span class="field__error"><?php echo esc_html( $cf_errors['name'] ); ?></span>
				<?php endif; ?>
			</div>

			<div class="field">
				<label class="field__label" for="quote-company"><?php esc_html_e( 'Company (optional)', 'compass-digital' ); ?></label>
				<input class="field__control" type="text" id="quote-company" name="company" autocomplete="organization" value="<?php echo esc_attr( compass_quote_value( 'company' ) ); ?>" />
			</div>
		</div>

		<div class="quote-row">
			<div class="field<?php echo isset( $cf_errors['email'] ) ? ' field--error' : ''; ?>">
				<label class="field__label" for="quote-email"><?php esc_html_e( 'Email', 'compass-digital' ); ?> <span class="field__req">*</span></label>
				<input class="field__control" type="email" id="quote-email" name="email" autocomplete="email" required value="<?php echo esc_attr( compass_quote_value( 'email' ) ); ?>" />
				<?php if ( isset( $cf_errors['email'] ) ) : ?>
					<span class="field__error"><?php echo esc_html( $cf_errors['email'] ); ?></span>
				<?php endif; ?>
			</div>

			<div class="field<?php echo isset( $cf_errors['phone'] ) ? ' field--error' : ''; ?>">
				<label class="field__label" for="quote-phone"><?php esc_html_e( 'Phone / WhatsApp', 'compass-digital' ); ?> <span class="field__req">*</span></label>
				<input class="field__control" type="tel" id="quote-phone" name="phone" autocomplete="tel" required value="<?php echo esc_attr( compass_quote_value( 'phone' ) ); ?>" />
				<?php if ( isset( $cf_errors['phone'] ) ) : ?>
					<span class="field__error"><?php echo esc_html( $cf_errors['phone'] ); ?></span>
				<?php endif; ?>
			</div>
		</div>

		<div class="field">
			<span class="field__label" id="quote-contact_method"><?php esc_html_e( 'How should we reach you?', 'compass-digital' ); ?></span>
			<div class="quote-methods" role="radiogroup" aria-labelledby="quote-contact_method">
				<?php
				$current_method = compass_quote_value( 'contact_method', 'email' );
				foreach ( $methods as $slug => $label ) :
					?>
					<label class="quote-check">
						<input type="radio" name="contact_method" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $current_method, $slug ); ?> />
						<span><?php echo esc_html( $label ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="field<?php echo isset( $cf_errors['consent'] ) ? ' field--error' : ''; ?>">
			<label class="quote-check quote-consent">
				<input type="checkbox" id="quote-consent" name="consent" value="1" required <?php checked( '1' === compass_quote_value( 'consent' ) ); ?> />
				<span>
					<?php
					printf(
						/* translators: %s: link to the privacy policy. */
						esc_html__( 'I agree that Compass Digital can contact me about my request. See our %s.', 'compass-digital' ),
						'<a href="' . esc_url( home_url( '/privacy/' ) ) . '">' . esc_html__( 'privacy policy', 'compass-digital' ) . '</a>'
					);
					?>
				</span>
			</label>
			<?php if ( isset( $cf_errors['consent'] ) ) : ?>
				<span class="field__error"><?php echo esc_html( $cf_errors['consent'] ); ?></span>
			<?php endif; ?>
		</div>
	</fieldset>

	<div class="quote-actions">
		<button type="button" class="btn btn--ghost" data-quote-back hidden><?php esc_html_e( 'Back', 'compass-digital' ); ?></button>
		<button type="button" class="btn btn--primary" data-quote-next hidden><?php esc_html_e( 'Next', 'compass-digital' ); ?></button>
		<button type="submit" class="btn btn--primary" data-quote-submit><?php echo esc_html( $cf_args['submit'] ); ?></button>
	</div>

	<p class="quote-reassure"><?php esc_html_e( 'No obligation, no spam — just a clear, honest quote. We reply within one business day.', 'compass-digital' ); ?></p>
</form>
