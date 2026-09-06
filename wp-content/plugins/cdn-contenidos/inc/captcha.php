<?php
/**
 * Captcha aritmético "stateless" basado en HMAC.
 *
 * La verificación no requiere sesión ni almacenamiento: el token que viaja en el
 * formulario es un HMAC calculado con los operandos, el instante y un secreto de
 * WordPress (wp_salt). El servidor recalcula y compara con hash_equals().
 *
 * @package cdn_contenidos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Genera los operandos de una suma simple (1..9).
 *
 * @return int[]
 */
function cdn_captcha_operandos() {
	$a = wp_rand( 1, 9 );
	$b = wp_rand( 1, 9 );
	return array( $a, $b );
}

/**
 * Calcula el token HMAC para un par de operandos y tiempo dados.
 *
 * @param int    $a    Primer operando.
 * @param int    $b    Segundo operando.
 * @param string $salt Sal aleatoria del formulario.
 * @param int    $t    Instante Unix de generación.
 * @return string
 */
function cdn_captcha_token( $a, $b, $salt, $t ) {
	return hash_hmac( 'sha256', $a . ':' . $b . ':' . $t, $salt . '|' . wp_salt( 'nonce' ) );
}

/**
 * Entrega los datos listos para el formulario (ocultos + pregunta).
 *
 * @return array
 */
function cdn_captcha_datos() {
	list( $a, $b ) = cdn_captcha_operandos();
	$salt = bin2hex( random_bytes( 8 ) );
	$t    = time();
	return array(
		'a'      => $a,
		'b'      => $b,
		'salt'   => $salt,
		't'      => $t,
		'token'  => cdn_captcha_token( $a, $b, $salt, $t ),
		'pregunta' => $a . ' + ' . $b,
	);
}

/**
 * Renderiza los campos del captcha (inputs ocultos + input de respuesta).
 * Debe llamarse dentro de un formulario.
 */
function cdn_captcha_render() {
	$d = cdn_captcha_datos();
	?>
	<div class="form-campo captcha">
		<label for="cdn_cap_r">Verificación: ¿cuánto es <?php echo esc_html( $d['pregunta'] ); ?>?</label>
		<input type="hidden" name="cdn_cap_a" value="<?php echo esc_attr( $d['a'] ); ?>">
		<input type="hidden" name="cdn_cap_b" value="<?php echo esc_attr( $d['b'] ); ?>">
		<input type="hidden" name="cdn_cap_salt" value="<?php echo esc_attr( $d['salt'] ); ?>">
		<input type="hidden" name="cdn_cap_t" value="<?php echo esc_attr( $d['t'] ); ?>">
		<input type="hidden" name="cdn_cap_tok" value="<?php echo esc_attr( $d['token'] ); ?>">
		<input type="number" id="cdn_cap_r" name="cdn_cap_r" min="2" max="18" inputmode="numeric" autocomplete="off" required>
	</div>
	<?php
}

/**
 * Verifica la respuesta del captcha (validez + antigüedad mínima/máxima).
 *
 * @param string $respuesta Respuesta enviada por el usuario.
 * @param string $a         Operando A (raw del request).
 * @param string $b         Operando B (raw del request).
 * @param string $salt      Sal del request.
 * @param string $t         Tiempo del request.
 * @param string $token     Token HMAC del request.
 * @return bool
 */
function cdn_captcha_verificar( $respuesta, $a, $b, $salt, $t, $token ) {
	$a   = absint( $a );
	$b   = absint( $b );
	$t   = absint( $t );
	$now = time();

	// Integridad del token frente a manipulaciones.
	$esperado = cdn_captcha_token( $a, $b, (string) $salt, $t );
	if ( ! is_string( $token ) || ! hash_equals( $esperado, $token ) ) {
		return false;
	}

	// "Time-trap": la respuesta humana tarda entre 2 y 300 segundos.
	if ( ( $now - $t ) < 2 || ( $now - $t ) > 300 ) {
		return false;
	}

	return ( is_numeric( $respuesta ) && (int) $respuesta === ( $a + $b ) );
}
