<?php
/**
 * Chat provider adapter — the ONE module to change to swap AI providers.
 *
 * The REST handler (chat.php) is provider-agnostic: it builds a system prompt
 * and a list of {role, text} turns, then calls compass_chat_complete(). To move
 * from Gemini to Groq, Cloudflare Workers AI, OpenAI, etc., add a matching
 * `compass_chat_provider_<name>()` function and point the `compass_chat_provider`
 * filter (or the COMPASS_CHAT_PROVIDER constant) at it. Nothing else changes.
 *
 * Default: Google Gemini API free tier (gemini-2.0-flash). The API key is read
 * server-side via compass_core_secret() and never reaches the browser.
 *
 * Each provider returns:
 *   array( 'ok' => true,  'text' => string )
 *   array( 'ok' => false, 'code' => 'no_key'|'quota'|'error', 'message' => string )
 *
 * @package CompassCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Complete a chat turn with the active provider.
 *
 * @param string $system   System / persona prompt.
 * @param array  $turns    List of array( 'role' => 'user'|'assistant', 'text' => string ).
 * @return array Result array (see file header).
 */
function compass_chat_complete( $system, $turns ) {
	$provider = defined( 'COMPASS_CHAT_PROVIDER' ) ? (string) COMPASS_CHAT_PROVIDER : 'gemini';
	/**
	 * Filter the active chat provider slug.
	 *
	 * @param string $provider Provider slug, e.g. 'gemini'.
	 */
	$provider = (string) apply_filters( 'compass_chat_provider', $provider );

	$fn = 'compass_chat_provider_' . preg_replace( '/[^a-z0-9_]/', '', $provider );
	if ( ! function_exists( $fn ) ) {
		$fn = 'compass_chat_provider_gemini';
	}

	return (array) call_user_func( $fn, $system, $turns );
}

/**
 * Google Gemini (generativelanguage.googleapis.com) — free tier default.
 *
 * @param string $system System prompt.
 * @param array  $turns  Conversation turns.
 * @return array
 */
function compass_chat_provider_gemini( $system, $turns ) {
	$key = function_exists( 'compass_core_secret' ) ? compass_core_secret( 'gemini_api_key' ) : '';
	if ( '' === $key ) {
		return array(
			'ok'      => false,
			'code'    => 'no_key',
			'message' => 'The assistant is not configured yet.',
		);
	}

	$model = function_exists( 'compass_core_secret' ) ? compass_core_secret( 'gemini_model' ) : '';
	$model = $model ? $model : 'gemini-2.0-flash';
	$model = preg_replace( '/[^a-zA-Z0-9.\-]/', '', $model );

	$contents = array();
	foreach ( $turns as $turn ) {
		$role = ( isset( $turn['role'] ) && 'assistant' === $turn['role'] ) ? 'model' : 'user';
		$text = isset( $turn['text'] ) ? (string) $turn['text'] : '';
		if ( '' === trim( $text ) ) {
			continue;
		}
		$contents[] = array(
			'role'  => $role,
			'parts' => array( array( 'text' => $text ) ),
		);
	}

	$body = array(
		'system_instruction' => array(
			'parts' => array( array( 'text' => $system ) ),
		),
		'contents'           => $contents,
		'generationConfig'   => array(
			'temperature'     => 0.6,
			'maxOutputTokens' => 400,
			'topP'            => 0.9,
		),
		'safetySettings'     => array(
			array(
				'category'  => 'HARM_CATEGORY_HARASSMENT',
				'threshold' => 'BLOCK_ONLY_HIGH',
			),
			array(
				'category'  => 'HARM_CATEGORY_HATE_SPEECH',
				'threshold' => 'BLOCK_ONLY_HIGH',
			),
			array(
				'category'  => 'HARM_CATEGORY_DANGEROUS_CONTENT',
				'threshold' => 'BLOCK_ONLY_HIGH',
			),
		),
	);

	$endpoint = sprintf(
		'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
		rawurlencode( $model )
	);

	$response = wp_remote_post(
		$endpoint,
		array(
			'timeout' => 20,
			'headers' => array(
				'Content-Type'   => 'application/json',
				'x-goog-api-key' => $key,
			),
			'body'    => wp_json_encode( $body ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return array(
			'ok'      => false,
			'code'    => 'error',
			'message' => $response->get_error_message(),
		);
	}

	$status = (int) wp_remote_retrieve_response_code( $response );
	$json   = json_decode( (string) wp_remote_retrieve_body( $response ), true );

	if ( 429 === $status ) {
		return array(
			'ok'      => false,
			'code'    => 'quota',
			'message' => 'The assistant is busy right now.',
		);
	}

	if ( $status < 200 || $status >= 300 || ! is_array( $json ) ) {
		$msg = isset( $json['error']['message'] ) ? (string) $json['error']['message'] : ( 'HTTP ' . $status );
		return array(
			'ok'      => false,
			'code'    => 'error',
			'message' => $msg,
		);
	}

	// Blocked prompt (no candidate) — treat as a graceful decline.
	if ( empty( $json['candidates'][0]['content']['parts'] ) ) {
		return array(
			'ok'      => false,
			'code'    => 'error',
			'message' => 'No answer returned.',
		);
	}

	$text = '';
	foreach ( $json['candidates'][0]['content']['parts'] as $part ) {
		if ( isset( $part['text'] ) ) {
			$text .= $part['text'];
		}
	}
	$text = trim( $text );

	if ( '' === $text ) {
		return array(
			'ok'      => false,
			'code'    => 'error',
			'message' => 'Empty answer.',
		);
	}

	return array(
		'ok'   => true,
		'text' => $text,
	);
}
