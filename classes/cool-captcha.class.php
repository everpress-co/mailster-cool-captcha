<?php
/**
 * Mailster Cool Captcha integration class.
 *
 * @package Mailster_Cool_Captcha
 */

/**
 * Integrates Cool Captcha with Mailster forms and settings.
 *
 * @since 1.0
 */
class MailsterCoolCaptcha {

	/**
	 * Absolute filesystem path to the plugin directory.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	private $plugin_path;

	/**
	 * URL to the plugin directory.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	private $plugin_url;

	/**
	 * Sets plugin paths and registers activation and init hooks.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->plugin_path = plugin_dir_path( MAILSTER_COOLCAPTCHA_FILE );
		$this->plugin_url  = plugin_dir_url( MAILSTER_COOLCAPTCHA_FILE );

		register_activation_hook( MAILSTER_COOLCAPTCHA_FILE, array( &$this, 'activate' ) );

		load_plugin_textdomain( 'mailster-coolcaptcha' );

		add_action( 'init', array( &$this, 'init' ) );
	}

	/**
	 * Seeds default Cool Captcha options on plugin activation.
	 *
	 * @since 1.0
	 *
	 * @param bool $network_wide Whether the plugin is network-activated.
	 * @return void
	 */
	public function activate( $network_wide ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Hook signature.

		if ( function_exists( 'mailster' ) ) {

			$defaults = array(
				'coolcaptcha_error_msg' => __( 'Enter the text of the captcha', 'mailster-coolcaptcha' ),
				'coolcaptcha_formlabel' => __( 'Enter the text of the captcha', 'mailster-coolcaptcha' ),
				'coolcaptcha_forms'     => array(),
				'coolcaptcha_format'    => 'jpeg',
				'coolcaptcha_quality'   => 2,
				'coolcaptcha_width'     => 200,
				'coolcaptcha_height'    => 70,
				'coolcaptcha_blur'      => true,
				'coolcaptcha_min'       => 5,
				'coolcaptcha_max'       => 8,
				'coolcaptcha_yp'        => 12,
				'coolcaptcha_ya'        => 14,
				'coolcaptcha_xp'        => 11,
				'coolcaptcha_xa'        => 5,
				'coolcaptcha_rot'       => 8,
				'coolcaptcha_language'  => 'en',
			);

			$mailster_options = mailster_options();

			foreach ( $defaults as $key => $value ) {
				if ( ! isset( $mailster_options[ $key ] ) ) {
					mailster_update_option( $key, $value );
				}
			}
		}
	}

	/**
	 * Registers admin settings and front-end captcha hooks.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function init() {

		if ( is_admin() ) {

			add_filter( 'mailster_setting_sections', array( &$this, 'settings_tab' ) );

			add_action( 'mailster_section_tab_coolcaptcha', array( &$this, 'settings' ) );

		}

		add_filter( 'mailster_form_fields', array( &$this, 'form_fields' ), 10, 3 );
		add_filter( 'mailster_submit_errors', array( &$this, 'check_captcha_v1' ), 10, 1 );
		add_filter( 'mailster_submit', array( &$this, 'check_captcha' ), 10, 1 );
		add_action( 'wp_ajax_mailster_coolcaptcha_img', array( &$this, 'coolcaptcha_img' ) );
		add_action( 'wp_ajax_nopriv_mailster_coolcaptcha_img', array( &$this, 'coolcaptcha_img' ) );
	}

	/**
	 * Builds the admin-ajax URL used to render the captcha image.
	 *
	 * @since 1.4.0
	 *
	 * @param int $formid Optional. Mailster form ID. Default 0.
	 * @return string Captcha image URL.
	 */
	public function get_image_url( $formid = 0 ) {

		return add_query_arg(
			array(
				'action'  => 'mailster_coolcaptcha_img',
				'formid'  => (int) $formid,
				'nocache' => time(),
			),
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Adds the Cool Captcha tab to Mailster settings sections.
	 *
	 * @since 1.0
	 *
	 * @param array $settings Associative array of setting section slugs and labels.
	 * @return array Modified settings sections.
	 */
	public function settings_tab( $settings ) {

		$position = 4;
		$settings = array_slice( $settings, 0, $position, true ) +
					array( 'coolcaptcha' => 'Cool Captcha' ) +
					array_slice( $settings, $position, null, true );

		return $settings;
	}

	/**
	 * Outputs the Cool Captcha settings fields markup.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function settings() {

		?>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Error Message', 'mailster-coolcaptcha' ); ?></th>
			<td><p><input type="text" name="mailster_options[coolcaptcha_error_msg]" value="<?php echo esc_attr( mailster_option( 'coolcaptcha_error_msg' ) ); ?>" class="large-text"></p></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Form Label', 'mailster-coolcaptcha' ); ?></th>
			<td><p><input type="text" name="mailster_options[coolcaptcha_formlabel]" value="<?php echo esc_attr( mailster_option( 'coolcaptcha_formlabel' ) ); ?>" class="large-text"></p></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Disable for logged in users', 'mailster-coolcaptcha' ); ?></th>
			<td><label><input type="hidden" name="mailster_options[coolcaptcha_loggedin]" value=""><input type="checkbox" name="mailster_options[coolcaptcha_loggedin]" value="1" <?php checked( mailster_option( 'coolcaptcha_loggedin' ) ); ?>> <?php esc_html_e( 'disable the captcha for logged in users', 'mailster-coolcaptcha' ); ?></label></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Forms', 'mailster-coolcaptcha' ); ?><p class="description"><?php esc_html_e( 'select forms which require a captcha', 'mailster-coolcaptcha' ); ?></p></th>
			<td>
				<ul>
				<?php
				$forms   = mailster( 'forms' )->get_all();
				$enabled = mailster_option( 'coolcaptcha_forms', array() );
				foreach ( $forms as $form ) {
					$form = (object) $form;
					$id   = isset( $form->ID ) ? $form->ID : $form->id;
					printf(
						'<li><label><input name="mailster_options[coolcaptcha_forms][]" type="checkbox" value="%1$s" %2$s>%3$s</label></li>',
						esc_attr( $id ),
						checked( in_array( $id, $enabled, true ), true, false ),
						esc_html( $form->name )
					);
				}

				?>
				</ul>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Preview', 'mailster-coolcaptcha' ); ?>
			<p class="description"><?php esc_html_e( 'you have to save the settings to update the preview!', 'mailster-coolcaptcha' ); ?></p></th>
			<td>
				<?php
				printf(
					'<br><img src="%1$s" width="%2$s" height="%3$s" style="border:1px solid #ccc">',
					esc_url( $this->get_image_url( 0 ) ),
					esc_attr( mailster_option( 'coolcaptcha_width' ) ),
					esc_attr( mailster_option( 'coolcaptcha_height', 70 ) )
				);
				?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Image Format', 'mailster-coolcaptcha' ); ?></th>
			<td><select name="mailster_options[coolcaptcha_format]">
				<?php
				$themes      = array(
					'jpeg' => 'JPG',
					'png'  => 'PNG',
				);
					$current = mailster_option( 'coolcaptcha_format' );
				foreach ( $themes as $key => $name ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
						esc_attr( $key ),
						selected( $key, $current, false ),
						esc_html( $name )
					);
				}

				?>
			</select></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Quality', 'mailster-coolcaptcha' ); ?></th>
			<td><select name="mailster_options[coolcaptcha_quality]">
				<?php
				$themes      = array(
					1 => 'low',
					2 => 'medium',
					3 => 'high',
				);
					$current = mailster_option( 'coolcaptcha_quality' );
				foreach ( $themes as $key => $name ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
						esc_attr( $key ),
						selected( $key, $current, false ),
						esc_html( $name )
					);
				}

				?>
			</select></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Dimensions', 'mailster-coolcaptcha' ); ?></th>
			<td><p><input type="text" name="mailster_options[coolcaptcha_width]" value="<?php echo esc_attr( mailster_option( 'coolcaptcha_width', 200 ) ); ?>" class="small-text"> &times; <input type="text" name="mailster_options[coolcaptcha_height]" value="<?php echo esc_attr( mailster_option( 'coolcaptcha_height' ) ); ?>" class="small-text"> px</p></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Blur', 'mailster-coolcaptcha' ); ?></th>
			<td><label><input type="hidden" name="mailster_options[coolcaptcha_blur]" value=""><input type="checkbox" name="mailster_options[coolcaptcha_blur]" value="1" <?php checked( mailster_option( 'coolcaptcha_blur' ) ); ?>> <?php esc_html_e( 'use blur', 'mailster-coolcaptcha' ); ?></label></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Line', 'mailster-coolcaptcha' ); ?></th>
			<td><label><input type="hidden" name="mailster_options[coolcaptcha_line]" value=""><input type="checkbox" name="mailster_options[coolcaptcha_line]" value="1" <?php checked( mailster_option( 'coolcaptcha_line' ) ); ?>> <?php esc_html_e( 'strike out the text', 'mailster-coolcaptcha' ); ?></label></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Word length', 'mailster-coolcaptcha' ); ?></th>
			<td><p><?php esc_html_e( 'use at least', 'mailster-coolcaptcha' ); ?> <input type="text" name="mailster_options[coolcaptcha_min]" value="<?php echo esc_attr( mailster_option( 'coolcaptcha_min' ) ); ?>" class="small-text"> <?php esc_html_e( 'letters per word but max', 'mailster-coolcaptcha' ); ?> <input type="text" name="mailster_options[coolcaptcha_max]" value="<?php echo esc_attr( mailster_option( 'coolcaptcha_max' ) ); ?>" class="small-text"></p></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Wave configuration', 'mailster-coolcaptcha' ); ?></th>
			<td><p>Y-period: <input type="text" name="mailster_options[coolcaptcha_yp]" value="<?php echo esc_attr( mailster_option( 'coolcaptcha_yp' ) ); ?>" class="small-text">
				Y-amplitude: <input type="text" name="mailster_options[coolcaptcha_ya]" value="<?php echo esc_attr( mailster_option( 'coolcaptcha_ya' ) ); ?>" class="small-text">
				X-period: <input type="text" name="mailster_options[coolcaptcha_xp]" value="<?php echo esc_attr( mailster_option( 'coolcaptcha_xp' ) ); ?>" class="small-text">
				X-amplitude: <input type="text" name="mailster_options[coolcaptcha_xa]" value="<?php echo esc_attr( mailster_option( 'coolcaptcha_xa' ) ); ?>" class="small-text"> </p></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Max. rotation', 'mailster-coolcaptcha' ); ?></th>
			<td><p><input type="text" name="mailster_options[coolcaptcha_rot]" value="<?php echo esc_attr( mailster_option( 'coolcaptcha_rot' ) ); ?>" class="small-text"></p></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Language', 'mailster-coolcaptcha' ); ?></th>
			<td><select name="mailster_options[coolcaptcha_language]">
				<?php
				$languages   = array(
					'en' => 'English',
					'es' => 'Spanish',
				);
					$current = mailster_option( 'coolcaptcha_language' );
				foreach ( $languages as $key => $name ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
						esc_attr( $key ),
						selected( $key, $current, false ),
						esc_html( $name )
					);
				}

				?>
			</select></td>
		</tr>
	</table>

		<?php
	}

	/**
	 * Injects the captcha field into enabled Mailster forms.
	 *
	 * @since 1.0
	 *
	 * @param array $fields Form field definitions.
	 * @param int   $formid Mailster form ID.
	 * @param array $form   Form configuration.
	 * @return array Modified form fields.
	 */
	public function form_fields( $fields, $formid, $form ) {

		if ( is_user_logged_in() && mailster_option( 'coolcaptcha_loggedin' ) ) {
			return $fields;
		}

		if ( ! in_array( (int) $formid, array_map( 'intval', (array) mailster_option( 'coolcaptcha_forms', array() ) ), true ) ) {
			return $fields;
		}

		$offset = mailster_option( 'gdpr_forms' ) && isset( $fields['_gdpr'] ) ? 2 : 1;

		$position = count( $fields ) - $offset;
		$fields   = array_slice( $fields, 0, $position, true ) +
					array( '_coolcaptcha' => $this->get_field( $form, $formid, $form ) ) +
					array_slice( $fields, $position, null, true );

		return $fields;
	}

	/**
	 * AJAX handler that renders the captcha image and terminates the request.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function coolcaptcha_img() {

		if ( ! session_id() ) {
			session_start();
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public captcha image endpoint.
		$formid = isset( $_GET['formid'] ) ? absint( wp_unslash( $_GET['formid'] ) ) : 0;

		require_once $this->plugin_path . 'captcha/captcha.php';
		$captcha = new SimpleCaptcha();

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- SimpleCaptcha API.
		$captcha->resourcesPath = $this->plugin_path . 'captcha';
		$captcha->wordsFile     = 'words/' . mailster_option( 'coolcaptcha_language' ) . '.php';
		$captcha->session_var   = 'mailster_coolcaptcha_' . $formid;

		$captcha->width  = intval( mailster_option( 'coolcaptcha_width' ) );
		$captcha->height = intval( mailster_option( 'coolcaptcha_height' ) );

		$captcha->imageFormat = mailster_option( 'coolcaptcha_format', 'jpg' );

		$captcha->lineWidth = mailster_option( 'coolcaptcha_line' ) ? 3 : 0;
		$captcha->scale     = intval( mailster_option( 'coolcaptcha_quality' ) );

		$captcha->minWordLength = intval( mailster_option( 'coolcaptcha_min' ) );
		$captcha->maxWordLength = intval( mailster_option( 'coolcaptcha_max' ) );

		$captcha->maxRotation = intval( mailster_option( 'coolcaptcha_rot' ) );

		$captcha->blur       = (bool) mailster_option( 'coolcaptcha_blur' );
		$captcha->Yperiod    = intval( mailster_option( 'coolcaptcha_yp' ) );
		$captcha->Yamplitude = intval( mailster_option( 'coolcaptcha_ya' ) );
		$captcha->Xperiod    = intval( mailster_option( 'coolcaptcha_xp' ) );
		$captcha->Xamplitude = intval( mailster_option( 'coolcaptcha_xa' ) );
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		$captcha->CreateImage();
		exit;
	}

	/**
	 * Returns HTML markup for the captcha image and input field.
	 *
	 * @since 1.0
	 *
	 * @param array $html   Form configuration (legacy parameter name).
	 * @param int   $formid Mailster form ID.
	 * @param array $form   Form configuration.
	 * @return string Captcha field HTML.
	 */
	public function get_field( $html, $formid, $form ) {

		$form = (object) $form;

		$width  = intval( mailster_option( 'coolcaptcha_width' ) );
		$height = intval( mailster_option( 'coolcaptcha_height' ) );

		$label = mailster_option( 'coolcaptcha_formlabel' );

		$html = '<div class="mailster-wrapper mailster-_coolcaptcha-wrapper">';
		if ( empty( $form->inline ) ) {
			$html .= '<label for="mailster-_coolcaptcha-' . $formid . '">' . $label . '</label>';
		}
		$img = sprintf(
			'<div class="mailster-coolcaptcha-wrap"><img title="' . esc_attr__( 'click to reload', 'mailster-coolcaptcha' ) . '" onclick="var s=this.src;this.src=s.replace(/nocache=\d+/, \'nocache=\'+(+new Date()))" src="%s" style="cursor:pointer;width:%dpx;height:%dpx"></div>',
			esc_url( $this->get_image_url( $formid ) ),
			$width,
			$height
		);

		$input = '<input id="mailster-_coolcaptcha-' . $formid . '" name="mailster__coolcaptcha" type="text" value="" class="input mailster-coolcaptcha" placeholder="' . ( ! empty( $form->inline ) ? $label : '' ) . '">';

		$html = $html . $img . $input;

		$html .= '</div>';

		return $html;
	}

	/**
	 * Validates the captcha on Mailster form submit (v2 submit payload).
	 *
	 * @since 1.0
	 *
	 * @param array $submission Submit data including errors array.
	 * @return array Submit data with captcha error when validation fails.
	 */
	public function check_captcha( $submission ) {

		if ( is_user_logged_in() && mailster_option( 'coolcaptcha_loggedin' ) ) {
			return $submission;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified by Mailster form submit handler.
		$formid = isset( $_POST['formid'] ) ? absint( wp_unslash( $_POST['formid'] ) ) : 0;

		if ( ! in_array( $formid, array_map( 'intval', (array) mailster_option( 'coolcaptcha_forms', array() ) ), true ) ) {
			// phpcs:enable WordPress.Security.NonceVerification.Missing
			return $submission;
		}

		if ( ! $this->is_captcha_correct( $formid ) ) {
			$submission['errors']['_coolcaptcha'] = mailster_option( 'coolcaptcha_error_msg' );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		return $submission;
	}

	/**
	 * Validates the captcha on Mailster form submit (legacy errors array).
	 *
	 * @since 1.1
	 *
	 * @param array $errors Existing submit errors.
	 * @return array Errors with captcha message when validation fails.
	 */
	public function check_captcha_v1( $errors ) {

		if ( is_user_logged_in() && mailster_option( 'coolcaptcha_loggedin' ) ) {
			return $errors;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified by Mailster form submit handler.
		$formid = isset( $_POST['formid'] ) ? absint( wp_unslash( $_POST['formid'] ) ) : 0;

		if ( ! in_array( $formid, array_map( 'intval', (array) mailster_option( 'coolcaptcha_forms', array() ) ), true ) ) {
			// phpcs:enable WordPress.Security.NonceVerification.Missing
			return $errors;
		}

		if ( ! $this->is_captcha_correct( $formid ) ) {
			$errors['_coolcaptcha'] = mailster_option( 'coolcaptcha_error_msg' );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		return $errors;
	}

	/**
	 * Compares the user captcha input with the value stored in the session.
	 *
	 * @since 1.0
	 *
	 * @param int $formid Mailster form ID.
	 * @return bool True when the captcha matches, false otherwise.
	 */
	private function is_captcha_correct( $formid ) {

		if ( ! session_id() ) {
			session_start();
		}

		$session_var = 'mailster_coolcaptcha_' . $formid;

		if ( empty( $_SESSION[ $session_var ] ) ) {
			return false;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Public form field on Mailster submit.
		$submitted = isset( $_REQUEST['mailster__coolcaptcha'] )
			? strtolower( trim( sanitize_text_field( wp_unslash( $_REQUEST['mailster__coolcaptcha'] ) ) ) )
			: '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$expected = strtolower( sanitize_text_field( (string) $_SESSION[ $session_var ] ) );

		return $expected === $submitted;
	}
}
