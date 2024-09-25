<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WP Admin
 *
 * @since 1.0.0
 */
class ALEXR_Wp_admin{
	protected $script_handle_whitelist = array();
	protected $style_handle_whitelist = array();

    /**
     * Constructor
     * SI el PRO esta activo este no arranca
     * @since 1.0.0
     */
    public function __construct( ) {
	    //ray('ALEXR_Wp_admin FREE');
	    //ray('HOOKS FREE');
        $this->hooks();
    }

    public function hooks()
    {
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
	    add_action( 'admin_init', array( $this, 'maybe_redirect' ), 0 );
	    add_action( 'admin_init', array( $this, 'store_enqueued_styles_scripts' ), 0 );
	    add_action( 'admin_enqueue_scripts', array( $this, 'disable_third_party_styles_scripts' ), 9999999 );
	    add_action( 'admin_body_class', array( $this, 'body_class' ) );

	    add_action( 'admin_print_scripts', array( $this, 'remove_admin_notices' ) );
    }

    public function register_admin_menu() {

        $callback = array( $this, 'render_admin_page_wp' );

        add_menu_page(
            __('Reservations', 'alexr'),
            __('Reservations', 'alexr'),
            'edit_posts',
            'admin-alex-reservations',
	        array( $this, 'render_admin_page_wp' ),
            'dashicons-calendar-alt'
        );

	    add_submenu_page(
		    'admin-alex-reservations',
		    __('Bookings', 'alexr'),
		    __('Bookings', 'alexr'),
		    'edit_posts',
		    'admin-alex-reservations#/t/1/bookings/list',
		    array( $this, 'render_admin_page_wp' )
	    );

	    add_submenu_page(
		    'admin-alex-reservations',
		    __('Customers', 'alexr'),
		    __('Customers', 'alexr'),
		    'edit_posts',
		    'admin-alex-reservations#/t/1/customers/list',
		    array( $this, 'render_admin_page_wp' )
	    );

	    add_submenu_page(
		    'admin-alex-reservations',
		    __('Settings', 'alexr'),
		    __('Settings', 'alexr'),
		    'edit_posts',
		    'admin-alex-reservations#/t/1/app/settings/general',
		    array( $this, 'render_admin_page_wp' )
	    );

	    add_submenu_page(
		    'admin-alex-reservations',
		    __('Support', 'alexr'),
		    __('Support', 'alexr'),
		    'edit_posts',
		    'admin-alex-reservations#/app/support',
		    array( $this, 'render_admin_page_wp' )
	    );
    }

    public function body_class( $classes )
    {
	    if ( !$this->is_admin_page() ) {
		    return $classes;
	    }
	    $classes = "$classes srr-admin-app ";

	    return $classes;
    }

    public function render_admin_page_wp()
    {
	    //ray('FREE render_admin_page_wp');
	    $updated = $this->check_DB_Action();

		if ($updated) {
			wp_safe_redirect('/wp-admin/admin.php?page=admin-alex-reservations');
			exit();
		}

	    // Launch the framework
	    require_once EVAVEL_FRAMEWORK.'bootstrap.php';

	    if (!is_user_logged_in()) {
		    evavel_login();
	    }

	    // Needs to be registered in the system
	    // administrators are automatically registered
	    $user = \Evavel\Eva::make('user');
	    if (!$user) {
		    evavel_login();
	    }

        $assets_url = ALEXR_PLUGIN_URL.'assets/';

        wp_enqueue_style('srr-dashboard-css', $assets_url.'dashboard-mix/index.css', array(), ALEXR_VERSION);

	    wp_enqueue_script('srr-windowsfet-js', $assets_url.'js/windowSfet.js', array(), ALEXR_VERSION, true);
	    wp_enqueue_script('srr-crypto-js', $assets_url.'js/crypto-js.min.js', array(), ALEXR_VERSION, true);

        wp_register_script('srr-dashboard-script', $assets_url.'dashboard-mix/main.js', array(), ALEXR_VERSION, true);
        wp_localize_script('srr-dashboard-script', 'publicKey', [$this->getPublicKey()], array(), ALEXR_VERSION);
        wp_localize_script('srr-dashboard-script', 'SRR_config', $this->getConfig(), array(), ALEXR_VERSION);

        wp_register_script('srr-dashboard-launch', $assets_url.'js/dashboard-launch.js', array('srr-dashboard-script'), ALEXR_VERSION, true);

	    wp_enqueue_script( 'srr-dashboard-script' );
	    wp_enqueue_script( 'srr-dashboard-launch' );

		//ray('RENDERING ADMIN PAGE');

        echo '
        <style>
            body {background: #cbd5e1;}
			body.wp-admin.admin-bar.srr-admin-app #wpadminbar,
			body.wp-admin.admin-bar.srr-admin-app #adminmenumain,
			body.wp-admin.admin-bar.srr-admin-app #wpfooter,
			body.wp-admin.admin-bar.srr-admin-app .hidden,
			body.wp-admin.admin-bar.srr-admin-app .wpsso-notice.notice,
			#wpadminbar,
			#adminmenumain,
			#wpfooter,
			.hidden {
				display: none;
			}
		</style>
		
		<div id="alexr-dashboard" class="alexr-dashboard min-h-full bg-slate-300 dark:bg-slate-700">
            <div id="app"></div>
            <div id="modal"></div>
            <div id="dropdowns"></div>
        </div>
        ';
    }

    protected function getPublicKey()
    {
        return "LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUlJQ0lqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FnOEFNSUlDQ2dLQ0FnRUF2czJvbHpBdFE3Y0lmOWlYT3YzcQpHQVNZcnZEMlpwL2lMbVowWXZ2eHoyZkRiUUQwOTUvTEFmMTUzVFBOTGtONFVBS21mbU5DUHZlMVBDU05SY09jCkEyVUl2aUxTcmtUeWR0Si9lOXUrN2pJdGtUeFlKSC9xY1RYNFNESjZzSkRYb2lxcW5IK0VKQU5XY1U1N25jd2sKdVVEZ2E4ZEVERTVsV3VPZVpGbHVVaWE5emJDU0lZRWpVWDMrTmFod3ZsSUtmbkFsS2xqQ1lzbzAvakNHL3pneApKS1JGQnpjbWNFSExJdGM3c2VjaFQ0Rk1LaU5SSnpRSEFZcms4MU5IZHRQRHcydnVuSjFYVDNuemVFRXk0M2V2CkJBNHo2ZitPTDdpQ3dPbFRoVnNBN0tTOGVhYW5xNmpOdWx1Wm0yNys5RmpLZnVDUVZjL2htRVVSNGptR1U3TDAKbG1UYUE0allaRlo2YWQ2RjMvcXVHYitwbEVsRk8rVXdOU3Y5OUFvanFVOUxuKys3VDRIdEVBODdUK29qOU1GOQpKM0NuUjhkZnJMdkw2YzNQWlhQa0lQUTRxWDdrWFpSOUU5RVczV2lnOXhOdERaYm5ORlhwYkUxNUQxdHcrbDdGClpMbVY0ZENGQ3JGTElzUjZqLy92V1U0UVlrRkdxK2o0R2lVS3krdnJYaUhDd29hM0NHd0hZbWNkTXNkc2g2U1gKN0Z3VVA2RUtQMjM0a2luT05DZlBIQ1M1OXNQVWp1eHdsQldFMHBZVjRVRmJ6SDdFNmpsVDB0Nm1xM0RhQTNPZgo1c0lsbFJFL3BqaTMyOWU3TElxek1iU0VJck9wekViWnBNZUhDMG9oVjFZV1IyR1ZiQ251QmJTMHFvRUFIcDA0Cm11SU1jK0VDejlRQXo3b21oZ29YejQwQ0F3RUFBUT09Ci0tLS0tRU5EIFBVQkxJQyBLRVktLS0tLQ==";
    }

	// Este se usa cuando esta activo solamente el FREE
    protected function getConfig()
    {
	    $config_arr = [
		    // For dashboard requests
		    'nonce' => evavel_create_nonce('alex-reservations'),
		    'background_img' => ALEXR_PLUGIN_URL.'assets/img/back2.jpg',
	        'main_sidebar_width' => 80,
	        'refreshBookingsList' => 15, // seconds
	        'refreshBookingsMonth' => 15,
	        'refreshBookingsWeek' => 60,
	        'appLink' => ALEXR_APP_LINK,
	        'appName' => ALEXR_APP_NAME,
	        'appVersion' => 'v.'.ALEXR_VERSION,
	        'appCopyright' => '© '.date('Y').' CodigoPlus',
	        'api' => '/wp-json/' . \Evavel\Http\RegisterRoutes::$namespace.'/',
	        //'token' => 'alex-reservations-FREE',
	        'translations' => ['en' => []],
	        'languages' => ['en'],
	        'lang' => 'en'
        ];

	    \Evavel\Eva::addConfig($config_arr);

        return EVAVEL()->config();
    }

	public function is_admin_page() {
		if ( empty( $_GET['page'] ) || strpos( $_GET['page'], 'admin-alex-reservations' ) === false ) {
			return false;
		}

		return true;
	}

	public function maybe_redirect() {

	}

	public function remove_admin_notices() {
		if ( !$this->is_admin_page() ) {
			return;
		}
		global $wp_filter;
		if (is_user_admin()) {
			if (isset($wp_filter['user_admin_notices'])) {
				unset($wp_filter['user_admin_notices']);
			}
		} elseif (isset($wp_filter['admin_notices'])) {
			unset($wp_filter['admin_notices']);
		}
		if (isset($wp_filter['all_admin_notices'])) {
			unset($wp_filter['all_admin_notices']);
		}
	}


	public function disable_third_party_styles_scripts() {
		if ( ! $this->is_admin_page() ) {
			return;
		}

		$custom_whitelist = array(

		);

		global $wp_scripts;
		foreach ($wp_scripts->queue as $key => $handle) {
			if ( strpos( $handle, 'srr-' ) === 0 ) {
				continue;
			}

			if ( in_array( $handle, $this->script_handle_whitelist ) || in_array( $handle, $custom_whitelist ) ) {
				continue;
			}

			wp_dequeue_script( $handle );
		}

		global $wp_styles;
		foreach ($wp_styles->queue as $key => $handle) {
			if ( strpos( $handle, 'srr-' ) === 0 ) {
				continue;
			}

			if ( in_array( $handle, $this->style_handle_whitelist ) || in_array( $handle, $custom_whitelist ) ) {
				continue;
			}

			wp_dequeue_style( $handle );
		}

	}

	public function store_enqueued_styles_scripts() {
		if ( !$this->is_admin_page() ) {
			return;
		}

		global $wp_scripts;
		$this->script_handle_whitelist = $wp_scripts->queue;
	}


	/**
     * Send action=update-db
     * to force an update of the DATABASE
	 * @return void
	 */
    public function check_DB_Action()
    {
	    // action=update-db
	    if (isset($_GET['action']))
	    {
		    $action = sanitize_text_field($_GET['action']);

		    // Force a check of tables installed
		    if ($action = 'update-db'){
			    ALEXR_after_install_check_DB(true);
				return true;
		    }
	    }

		return false;
    }
}

new ALEXR_Wp_admin();
