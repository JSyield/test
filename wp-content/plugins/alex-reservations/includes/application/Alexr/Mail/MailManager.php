<?php

namespace Alexr\Mail;
use Alexr\Settings\EmailConfig;
use PHPMailer\PHPMailer\PHPMailer;

class MailManager {

	public $config;

	public function __construct( $restaurant_id ) {

		$this->config = EmailConfig::where('restaurant_id', $restaurant_id)->get()->first();

		add_filter( 'wp_mail', array( $this, 'wp_mail' ), 99999 );
		add_filter( 'wp_mail_content_type', array( $this, 'content_type' ), 99999 );
		add_action( 'phpmailer_init', array( $this, 'init_smtp' ), 99999 );
		add_action( 'wp_mail_failed', array( $this, 'wp_mail_failed' ) );

		add_filter('wp_mail_from_name', array($this, 'wp_mail_from_name'), 99999, 1);
		add_filter('wp_mail_from', array($this, 'wp_mail_from'), 99999, 1);

	}

	public function content_type(){
		return 'text/html';
	}

	public function wp_mail_from_name($from_name) {
		if (!$this->is_configured()) return $from_name;

		return $this->config->smtp_from_name;
	}

	public function wp_mail_from($from_email) {
		if (!$this->is_configured()) return $from_email;

		return $this->config->smtp_from_email;
	}


	public function is_configured() {

		if (!$this->config) return false;
		if (!$this->config->use_smtp) return false;

		$smtp_from_email = $this->config->smtp_from_email;
		if ( empty($smtp_from_email) ) return false;

		$smtp_from_name = $this->config->smtp_from_name;
		if (empty($smtp_from_name)) return false;

		return true;
	}

	public function init_smtp(  &$phpmailer ) {

		if (!$this->is_configured()) {
			//throw new \Exception(__eva('SMTP not configured.'));
			return;
		}

		$phpmailer->IsSMTP();

		// From
		$from_email = $this->config->smtp_from_email;
		$from_name = $this->config->smtp_from_name;
		$phpmailer->From = $from_email;
		$phpmailer->FromName = $from_name;
		$phpmailer->SetFrom( $phpmailer->From, $phpmailer->FromName );

		// Reply
		$replay_to_email = $this->config->smtp_reply_to;
		$phpmailer->clearReplyTos();
		if (!empty($replay_to_email)){
			$phpmailer->AddReplyTo( $replay_to_email, $from_name );
		} else {
			$phpmailer->AddReplyTo( $from_email, $from_name );
		}

		// BBC
		$bbc_emails = $this->config->smtp_bbc_email;
		if (!empty($bbc_emails)){
			$bbc_emails = explode(',', $bbc_emails);
			foreach($bbc_emails as $bcc_email) {
				$bbc_email = trim($bcc_email);
				$phpmailer->AddBcc($bbc_email);
			}
		}

		// HTML
		$phpmailer->ContentType = 'content="text/html; charset=utf-8';
		$phpmailer->IsHTML( true );

		// SMTP
		$type_encryption = $this->config->smtp_type_encryption;
		if ('none' != $type_encryption ) {
			$phpmailer->SMTPSecure = $type_encryption;
		}
		$phpmailer->Host = $this->config->smtp_host;
		$phpmailer->Port = $this->config->smtp_port;

		// Auth
		$phpmailer->SMTPAuth = false;
		if ( 'yes' === $this->config->smtp_authentication ) {
			$phpmailer->SMTPAuth = true;
			$phpmailer->Username = $this->config->smtp_username;
			$phpmailer->Password = $this->get_password();
		}

		//Set reasonable timeout
		$phpmailer->Timeout = 10;
	}

	// @todo decrypt password
	public function get_password() {
		return $this->config->smtp_password;
	}

	public function send_email($to, $subject, $message) {
		//ray('send_email');
		//ray($to);
		//ray($subject);

		if ($this->config->use_smtp_phpmailer) {
			//ray('USE PHPMAILER');
			try {
				$debug_log =  $this->send_email_phpmailer($to, $subject, $this->messageFormatted($message), true, false);
				return $debug_log;
			} catch (\Exception $e) {
				//ray($e);
				//ray($e->getMessage());
			}

		}

		//ray('Enviando un email a traves de WP');
		$result = wp_mail($to, $subject, $this->messageFormatted($message));
		return $result;
	}

	// @todo - what to do when wp_mail failed -> add error message to the dashboard?
	public function wp_mail_failed( $wp_error ) {
		if ( ! empty( $wp_error->errors ) && ! empty( $wp_error->errors['wp_mail_failed'] ) && is_array( $wp_error->errors['wp_mail_failed'] ) ) {
			$error =  '*** ' . implode( ' | ', $wp_error->errors['wp_mail_failed'] ) . " ***\r\n" ;
		}
	}

	public function wp_mail($args)
	{
		return $args;
	}

	public function send_email_phpmailer($to, $subject, $message, $is_message_formatted = true, $return_debug = false)
	{
		//ray('Enviando email a traves de send_email_phpmailer');

		if (!$this->is_configured()) {
			throw new \Exception(__eva('SMTP not configured.'));
			//return;
		}

		global $wp_version;

		if ( version_compare( $wp_version, '5.4.99' ) > 0 ) {
			require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
			require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
			require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
			$phpmailer = new PHPMailer(true);
		} else {
			require_once ABSPATH . WPINC . '/class-phpmailer.php';
			$phpmailer = new \PHPMailer( true );
		}


		$ret = [];

		global $debug_msg;
		$debug_msg         = '';

		try {

			//ray('TRY phpmailer');
			$charset       = get_bloginfo( 'charset' );
			$phpmailer->CharSet = $charset;

			$this->init_smtp($phpmailer);

			$phpmailer->Subject = $subject;
			$phpmailer->Body = "HOLA";
			$phpmailer->Body    = $is_message_formatted ? $message : $this->messageFormatted($message);
			if (is_array($to)) {
				foreach ($to as $to_email) {
					$phpmailer->AddAddress( $to_email );
				}
			} else if (is_string($to)) {
				$phpmailer->AddAddress( $to );
			}


			// send plain text test email
			$phpmailer->ContentType = 'content="text/html; charset=utf-8';
			$phpmailer->IsHTML( true );

			// PHPMailer 5.2.10 introduced this option. However, this might cause issues if the server is advertising TLS with an invalid certificate.
			$phpmailer->SMTPAutoTLS = false;

			// Prepare debugging
			$phpmailer->Debugoutput = function ( $str, $level ) {
				global $debug_msg;
				$debug_msg .= $str;
			};
			$phpmailer->SMTPDebug   = 1;

			// Send mail and return result
			$phpmailer->Send();
			$phpmailer->ClearAddresses();
			$phpmailer->ClearAllRecipients();

		} catch ( \Exception $e ) {
			$ret['error'] = $phpmailer->ErrorInfo;
		} //catch ( \Throwable $e ) {
			//$ret['error'] = $phpmailer->ErrorInfo;
		//}

		$ret['debug_log'] = $debug_msg;

		return $return_debug ? $ret : true;
	}



	public function messageFormatted($content)
	{
		$content = alexr_transform_new_lines_to_br($content);

		ob_start();
		include ALEXR_PLUGIN_DIR.'includes/dashboard/templates/email-layout/layout.php';
		$message = ob_get_clean();

		$tags = [
			'{logo}' => $this->getTemplateLogo(),
			'{backcolor}' => 'white',
			'{linecolor}' => '#cccccc',
			'{header}' => base64_decode($this->config->email_header),
			'{footer}' => base64_decode($this->config->email_footer),
			'{content}' => $content
		];

		foreach($tags as $tag => $value) {
			$message = str_replace($tag, $value, $message);
		}

		return $message;
	}

	/**
	 * Prepare the template html for the logo
	 * @return string
	 */
	protected function getTemplateLogo()
	{
		$logo_image_uploaded = $this->config->email_logo_img_url;
		$logo_image_url = $this->config->email_logo_img_url_2;

		if (empty($logo_image_url) || strlen($logo_image_url) < 10) {
			$logo_image = $logo_image_uploaded;
		} else {
			$logo_image = $logo_image_url;
		}

		$logo_link =  $this->config->email_logo_link;

		$template_logo =false;

		if (!empty($logo_image) && $logo_image != 'null') {
			if (empty($logo_link)) {
				$template_logo = 'logo-image-only.php';
			} else {
				$template_logo = 'logo-image-with-link.php';
			}
		}

		if ($template_logo) {
			ob_start();
			require_once ALEXR_PLUGIN_DIR.'includes/dashboard/templates/email-layout/logo/'.$template_logo;
			$logo_html = ob_get_clean();

			$tags = [
				'{logo_img}' => $logo_image,
				'{logo_link}' => $this->config->email_logo_link,
			];

			foreach($tags as $tag => $value) {
				$logo_html = str_replace($tag, $value, $logo_html);
			}

			return $logo_html;
		}

		return '';
	}

}
