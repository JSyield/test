<?php
/**
 * Plugin Name: Alex Reservations
 * Plugin URI:  https://alexreservations.com
 * Description: Smart Restaurant Booking
 * Version:     1.9.8
 * Release:     98
 * Requires PHP: 7.4
 * Author:      AlexReservations
 * Author URI:  http://alexreservations.com
 * Donate link: https://alexreservations.com
 * License:     GPLv2
 * Text Domain: alexr
 * Domain Path: /languages
 *
 * @link    https://alexreservations.com
 *
 * @package ALEXR
 * @version 1.9.8
 */

/**
 * Copyright (c) 2023 CodigoPlus (email : support@alexreservations.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Alex_Reservations_Check') ) :

    class Alex_Reservations_Check
    {
        private static $php_version = '7.4';
        private static $mysql_version = '5.7.6';
        public static $current_mysql_version = '';

        public static function compatible_version_php()
        {
            if ( version_compare(PHP_VERSION, self::$php_version, '<') ) {
                return false;
            }
            return true;
        }

	    public static function compatible_version_mysql()
	    {
		    global $wpdb;

		    // Populate the database debug fields.
		    if ( is_resource( $wpdb->dbh ) ) {
			    // Old mysql extension.
			    $extension = 'mysql';
		    } elseif ( is_object( $wpdb->dbh ) ) {
			    // mysqli or PDO.
			    $extension = get_class( $wpdb->dbh );
		    } else {
			    // Unknown sql extension.
			    $extension = null;
		    }

		    $server_version = $wpdb->get_var( 'SELECT VERSION()' );
            self::$current_mysql_version = $server_version;

		    if ( version_compare($server_version, self::$mysql_version, '<') ) {
			    return false;
		    }

            return true;
        }

        function activation_check()
        {
            if ( ! self::compatible_version_php() ) {
                deactivate_plugins( plugin_basename( __FILE__ ) );
                wp_die( 'Alex Reservations requires PHP '.self::$php_version.' or higher!'. '<br>'. 'Your current PHP version is '.PHP_VERSION);
            }

	        if (defined('EVAVEL_FRAMEWORK')) {
		        wp_die('You have another plugin using the same Framework, please disable it.<br>'.EVAVEL_FRAMEWORK);
	        }

            // Not ready yet
	        //if ( ! self::compatible_version_mysql() ) {
		    //    deactivate_plugins( plugin_basename( __FILE__ ) );
		    //    wp_die( 'Alex Reservations requires MYSQL server version '.self::$mysql_version.' or higher!'. '<br>'. 'Your current version is '.self::$current_mysql_version);
	        //}
        }
    }

    global $alexReservationsCheck;
    $alexReservationsCheck = new Alex_Reservations_Check();

    register_activation_hook(__FILE__, array($alexReservationsCheck, 'activation_check'));

endif;

/**
 * Main Alex_Reservations Class.
 *
 * @since 1.0
 */
if ( !class_exists('Alex_Reservations') ) :

	final class Alex_Reservations {

		/**
		 * @var Alex_Reservations
		 * @since 1.0
		 */
		private static $singleton;

		public $users;
		public $user_meta;
		public $restaurants;
		public $restaurant_meta;
		public $restaurant_setting;
		public $bookings;
		public $booking_meta;
		public $booking_notifications;
		public $booking_reviews;
		public $booking_table;
		public $btaggroups;
		public $btags;
		public $booking_btag;
		public $restaurant_user;
		public $settings;
		public $floors;
		public $notifications;
        public $payments;
		public $areas;
		public $tables;
		public $combinations;
		public $combination_table;
		public $customers;
		public $customer_meta;
		public $ctaggroups;
		public $ctags;
		public $customer_ctag;
		public $roles;
        public $actions;
		public $daily_notifications;
        public $tokens;

		/**
		 * @return Alex_Reservations
		 */
		public static function singleton() {
			if ( !isset( self::$singleton ) && !( self::$singleton instanceof Alex_Reservations ) ) {

				self::$singleton = new Alex_Reservations;
				self::$singleton->setup_constants();
                self::$singleton->setup_constants_framework();

				add_action( 'plugins_loaded', array(self::$singleton, 'load_textdomain' ), 0 );
				add_action( 'plugins_loaded', array(self::$singleton, 'run' ), 10 );
				add_action( 'plugins_loaded', array(self::$singleton, 'bootstrap_application' ), 100 );

                // Deactivate PRO version. Not needed, it is the core version now.
				//add_action( 'activated_plugin', array( self::$singleton, 'deactivate_other_instances' ) );
				//add_action( 'pre_current_active_plugins', array( self::$singleton, 'plugin_deactivated_notice' ) );

			}
			return self::$singleton;
		}


		private function setup_constants() {

			// Version
			if ( ! defined( 'ALEXR_VERSION' ) ) {
				define( 'ALEXR_VERSION', '1.9.8' );
			}

			if ( ! defined( 'ALEXR_RELEASE' ) ) {
				define( 'ALEXR_RELEASE', '98' );
			}

			if ( ! defined( 'ALEXR_APP_NAME' ) ) {
				define( 'ALEXR_APP_NAME', 'AlexReservations' );
			}

			if ( ! defined( 'ALEXR_APP_LINK' ) ) {
				define( 'ALEXR_APP_LINK', 'https://alexreservations.com' );
			}

			if ( ! defined( 'ALEXR_SERVER_LICENSE' ) ) {
				define( 'ALEXR_SERVER_LICENSE', 'https://alexreservations.com' );
			}

			if ( ! defined( 'ALEXR_SERVER_DOCS' ) ) {
				define( 'ALEXR_SERVER_DOCS', 'https://alexreservations.com' );
			}

			if ( ! defined( 'ALEXR_SITE_URL' ) ) {
				define( 'ALEXR_SITE_URL', rtrim(get_option('siteurl'),'/') );
			}

			// Folder Path
			if ( ! defined( 'ALEXR_PLUGIN_DIR' ) ) {
				define( 'ALEXR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Folder URL
			if ( ! defined( 'ALEXR_PLUGIN_URL' ) ) {
				define( 'ALEXR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Root File
			if ( ! defined( 'ALEXR_PLUGIN_FILE' ) ) {
				define( 'ALEXR_PLUGIN_FILE', __FILE__ );
			}

			// Name for Setting
			if ( ! defined( 'ALEXR_SETTINGS' ) ) {
				define( 'ALEXR_SETTINGS', 'alexr_settings' );
			}

			if ( ! defined( 'ALEXR_UPLOAD_FOLDER' ) ) {
				define( 'ALEXR_UPLOAD_FOLDER', 'alex-reservations' );
			}

			if ( ! defined( 'ALEXR_GET_VIEW_BOOKING' ) ) {
				define( 'ALEXR_GET_VIEW_BOOKING', 'mybooking' );
			}

            // Administrator can confirm the booking
			if ( ! defined( 'ALEXR_EDIT_VIEW_BOOKING' ) ) {
				define( 'ALEXR_EDIT_VIEW_BOOKING', 'mybookingedit' );
			}

			if ( ! defined( 'ALEXR_DASHBOARD' ) ) {
				define( 'ALEXR_DASHBOARD', '/ardashboard' );
			}

			define('ALEXR_PLUGIN_DIR_APP', ALEXR_PLUGIN_DIR . 'includes/application/');

			if ( ! defined( 'ALEXR_CUSTOM_TRANSLATION_PATH' ) ) {
				define( 'ALEXR_CUSTOM_TRANSLATION_PATH', WP_CONTENT_DIR . "/languages/plugins/alex-reservations/translations/");
			}
		}

        // Para usar especificamente dentro de la framework
		private function setup_constants_framework()
        {
            if (!defined('EVAVEL_PLUGIN_DIR')) {
	            define( 'EVAVEL_PLUGIN_DIR', ALEXR_PLUGIN_DIR);
            }

            if (!defined('EVAVEL_GET_VIEW_BOOKING')) {
	            define( 'EVAVEL_GET_VIEW_BOOKING', ALEXR_GET_VIEW_BOOKING);
            }

            if (!defined('EVAVEL_EDIT_VIEW_BOOKING')) {
	            define( 'EVAVEL_EDIT_VIEW_BOOKING', ALEXR_EDIT_VIEW_BOOKING);
            }
		}

        private function define($name, $value) {
            if (!defined($name)){
                define($name, $value);
            }
        }

		/**
		 * Loads language files
		 *
		 * @return void
		 * @since 1.0
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'alexr', false, plugin_basename( dirname( __FILE__ ) ) . "/languages/" );
		}

		public function run()
		{
			// Prevent errors when this function does not exists
			if (! function_exists('ray')) {
				function ray($something) {
					return false;
				}
			}

			self::$singleton->includes();

			$this->load_database();
        }

        protected function load_database()
        {
            self::$singleton->restaurants = new SRR_DB_Restaurants();
	        self::$singleton->restaurant_meta = new SRR_DB_RestaurantMeta();
	        self::$singleton->restaurant_setting = new SRR_DB_RestaurantSetting();
	        self::$singleton->bookings = new SRR_DB_Bookings();
	        self::$singleton->booking_meta = new SRR_DB_BookingMeta();
	        self::$singleton->booking_notifications = new SRR_DB_Booking_Notifications();
	        self::$singleton->booking_reviews = new SRR_DB_Booking_Reviews();
	        self::$singleton->booking_table = new SRR_DB_Booking_Table();
	        self::$singleton->btaggroups = new SRR_DB_Btaggroups();
	        self::$singleton->btags = new SRR_DB_Btags();
	        self::$singleton->booking_btag = new SRR_DB_Booking_Btag();
	        self::$singleton->users = new SRR_DB_Users();
	        self::$singleton->user_meta = new SRR_DB_UserMeta();
	        self::$singleton->restaurant_user = new SRR_DB_Restaurant_User();
	        self::$singleton->settings = new SRR_DB_Settings();
	        self::$singleton->floors = new SRR_DB_Floors();
	        self::$singleton->notifications = new SRR_DB_Notifications();
	        self::$singleton->payments = new SRR_DB_Payments();
	        self::$singleton->areas = new SRR_DB_Areas();
	        self::$singleton->tables = new SRR_DB_Tables();
	        self::$singleton->combinations = new SRR_DB_Combinations();
	        self::$singleton->combination_table = new SRR_DB_Combination_Table();
	        self::$singleton->customers = new SRR_DB_Customers();
	        self::$singleton->customer_meta = new SRR_DB_CustomerMeta();
	        self::$singleton->ctaggroups = new SRR_DB_Ctaggroups();
	        self::$singleton->ctags = new SRR_DB_Ctags();
	        self::$singleton->customer_ctag = new SRR_DB_Customer_Ctag();
	        self::$singleton->roles = new SRR_DB_Roles();
	        self::$singleton->actions = new SRR_DB_Actions();
            self::$singleton->daily_notifications = new SRR_DB_DailyNotifications();
            self::$singleton->tokens = new SRR_DB_Tokens();

        }

		private function includes() {

			require_once ALEXR_PLUGIN_DIR . 'debug.php';

			$this->load_dashboard_application();

			// WP admin page
			if (is_admin() && !defined('ALEXR_PRO_VERSION'))
            {
				require_once ALEXR_PLUGIN_DIR . 'includes-wp/admin/class-srr-wp-admin.php';
				require_once ALEXR_PLUGIN_DIR . 'includes-wp/admin/users/User_admin.php';
			}

			// Install DB - sync DB and translations
			require_once ALEXR_PLUGIN_DIR . 'includes/install.php';

			// Helpers
			require_once ALEXR_PLUGIN_DIR . 'includes/helpers.php';

			// Shortcodes
			require_once ALEXR_PLUGIN_DIR . 'includes/shortcodes.php';
			require_once ALEXR_PLUGIN_DIR . 'includes/traits/ajax-payment-actions.php';
            require_once ALEXR_PLUGIN_DIR . 'includes/traits/ajax-stripe-actions.php';
			require_once ALEXR_PLUGIN_DIR . 'includes/traits/ajax-redsys-actions.php';
			require_once ALEXR_PLUGIN_DIR . 'includes/traits/ajax-paypal-actions.php';
			require_once ALEXR_PLUGIN_DIR . 'includes/traits/ajax-mercadopago-actions.php';
			require_once ALEXR_PLUGIN_DIR . 'includes/traits/ajax-mollie-actions.php';
			require_once ALEXR_PLUGIN_DIR . 'includes/traits/ajax-square-actions.php';
			require_once ALEXR_PLUGIN_DIR . 'includes/traits/ajax-floorplan-actions.php';
			require_once ALEXR_PLUGIN_DIR . 'includes/traits/ajax-api.php';
			require_once ALEXR_PLUGIN_DIR . 'includes/ajax-actions.php';

			// Front-view guest reservation
			require_once ALEXR_PLUGIN_DIR . 'includes/front-view-booking.php';

			// Front-view admin update reservation
            require_once ALEXR_PLUGIN_DIR . 'includes/front-edit-booking.php';

			// Auto-update PRO . Not using yet
			//require_once ALEXR_PLUGIN_DIR . 'includes/auto-update.php';

			// Banner
			require_once ALEXR_PLUGIN_DIR . 'includes-wp/banner.php';
			global $codigoplus_promote_banner_plugins;
			$codigoplus_promote_banner_plugins[ 'alex-reservations' ] = array(
				'plugin_name' => 'Alex Reservations',
				'plugin_url'  => 'https://wordpress.org/support/plugin/alex-reservations/reviews/?filter=5#new-post'
			);
		}


        // Load the framework and then the application
		public function load_dashboard_application()
		{

			// External packages
			require_once ALEXR_PLUGIN_DIR . 'vendor/autoload.php';

			// Load Framework
			require_once ALEXR_PLUGIN_DIR . 'includes/framework/autoload.php';

			// Load Application
			require_once ALEXR_PLUGIN_DIR_APP . 'autoload.php';

            // Load routes
			$this->register_application_routes();
		}

        public function register_application_routes()
        {
            add_filter('evavel_routes_application', function($routes) {
                $list = require ALEXR_PLUGIN_DIR_APP.'Alexr/routes/routes.php';
                return array_merge($routes, $list);
            });
        }

		/**
		 * Bootstrap Application after all plugins are loaded
		 *
		 * @return void
		 */
		public function bootstrap_application()
		{
			new \Evavel\Http\RegisterRoutes();
			require_once EVAVEL_FRAMEWORK.'bootstrap.php';
		}

		/**
		 * Checks if another version of FREE/PRO is active and deactivates it.
		 * Hooked on `activated_plugin` so other plugin is deactivated when current plugin is activated.
		 *
		 * @param string $plugin The plugin being activated.
		 */
		public function deactivate_other_instances( $plugin ) {
			if ( ! in_array( $plugin, array( 'alex-reservations/alex-reservations.php','alex-reservations-pro/alex-reservations-pro.php' ), true ) ) {
				return;
			}

			$plugin_to_deactivate  = 'alex-reservations/alex-reservations.php';
			$deactivated_notice_id = '1';

			// If we just activated the free version, deactivate the pro version.
			if ( $plugin === $plugin_to_deactivate ) {
				$plugin_to_deactivate  = 'alex-reservations-pro/alex-reservations-pro.php';
				$deactivated_notice_id = '2';
			}

			if ( is_multisite() && is_network_admin() ) {
				$active_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
				$active_plugins = array_keys( $active_plugins );
			} else {
				$active_plugins = (array) get_option( 'active_plugins', array() );
			}

			foreach ( $active_plugins as $plugin_basename ) {
				if ( $plugin_to_deactivate === $plugin_basename ) {
					set_transient( 'srr_deactivated_notice_id', $deactivated_notice_id, 1 * HOUR_IN_SECONDS );
					deactivate_plugins( $plugin_basename );
					return;
				}
			}
		}

		/**
		 * Displays a notice when either FREE or PRO is automatically deactivated.
		 */
		public function plugin_deactivated_notice() {
			$deactivated_notice_id = (int) get_transient( 'srr_deactivated_notice_id' );

			if ( ! in_array( $deactivated_notice_id, array( 1, 2 ), true ) ) {
				return;
			}

			$message = __( "Alex Reservations and Alex Reservations PRO should not be active at the same time. We've automatically deactivated Alex Reservations PRO.", 'alexr' );
			if ( 2 === $deactivated_notice_id ) {
				$message = __( "Alex Reservations and Alex Reservations PRO should not be active at the same time. We've automatically deactivated Alex Reservations.", 'alexr' );
			}

			?>
			<div class="updated" style="border-left: 4px solid #ffba00;">
				<p><?php echo esc_html( $message ); ?></p>
			</div>
			<?php

			delete_transient( 'srr_deactivated_notice_id' );
		}
	}

	function WP_ALEXR() {
		return Alex_Reservations::singleton();
	}

	WP_ALEXR();

endif; // End if class_exists check.

