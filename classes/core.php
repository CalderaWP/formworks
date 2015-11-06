<?php
/**
 * Formworks.
 *
 * @package   Formworks
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 David Cramer
 */
namespace calderawp\frmwks;

/**
 * Main plugin class.
 *
 * @package Formworks
 * @author  David Cramer
 */
class core {

	/**
	 * The slug for this plugin
	 *
	 * @since 1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'formworks';

	/**
	 * Holds class instance
	 *
	 * @since 1.0.0
	 *
	 * @var      object|\calderawp\frmwks\core
	 */
	protected static $instance = null;

	/**
	 * Holds the option screen prefix
	 *
	 * @since 1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function __construct() {
		global $formworks_tracker;

		//auto load modules
		$dir = FRMWKS_PATH . 'includes/modules';
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($folder = readdir($dh)) !== false) {
					if( $folder === '..' || $folder === '.') continue;
					if( file_exists( $dir . '/' . $folder . '/handler.php' ) ){
						include_once $dir . '/' . $folder . '/handler.php';
					}
				}
				closedir($dh);
			}
		}
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		//initialize visitor tracker
		if( !is_admin() ){
			add_action( 'wp', array( $this, 'register_visitor_session' ) );
		}

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_stylescripts' ) );

		// Load front style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_stylescripts' ) );

		// output tracking code
		add_action( 'wp_print_footer_scripts', array( $this, 'print_front_scripts' ) );

		// Add partial completions		
		add_action( 'wp_ajax_frmwks_push', array( $this, 'tracker_push') );
		add_action( 'wp_ajax_nopriv_frmwks_push', array( $this, 'tracker_push') );
		
		// complete submission
		add_action( 'caldera_forms_submit_complete', function( $form ){
			do_action( 'formworks_track', 'caldera', $form['ID'], 'submission' );
		} );
		// open actions
		add_action( 'formworks_track', array( $this, 'handle_track'), 10, 4 );

		add_filter( 'grunion_contact_form_success_message', function( $html ){

			$form_id = $_GET['contact-form-id'];
			do_action( 'formworks_track', 'jp', $form_id, 'submission' );

			return $html;
		});

		add_filter( 'grunion_contact_form_form_action', function( $url, $post, $form ){

			do_action( 'formworks_track', 'jp', $form, 'loaded' );
			return $url;
		}, 15, 3 );

		add_action( 'frm_process_entry', function( $params ){
			do_action( 'formworks_track', 'frmid', $params['form_id'], 'submission' );			
		}, 15 );

		add_filter( 'formidable_shortcode_atts' , function( $shortcode_atts, $atts ){

			$form = \FrmForm::getOne( $atts['id'] );
			$selector = array(
				"name" => $form->name,
				"selector" => "#form_" . $form->form_key
			);
			do_action( 'formworks_track', 'frmid', $form->id, 'loaded', $selector );

		}, 10, 2 );

		add_filter( 'wpcf7_form_elements', function( $html ){
			$form = \WPCF7_ContactForm::get_current();
			do_action( 'formworks_track', 'cf7', $form->id(), 'loaded' );
			return $html;
		},10 , 2 );

		add_action( 'wpcf7_submit', function( $instance, $result ){

			if( $result['status'] === 'mail_sent' ){
				do_action( 'formworks_track', 'cf7', $instance->id(), 'submission' );
			}

		},20, 2);

		add_filter( 'gform_get_form_filter', function( $html, $form ){

			$selector = array(
				"name" => $form['title'],
				"selector" => "#gform_" . $form['id']
			);
			do_action( 'formworks_track', 'gform', $form['id'], 'loaded', $selector );
			return $html;
		},10 , 2 );

		// setup tracking
		add_filter( 'caldera_forms_render_form', function( $html, $form ){

			$selector = array(
				"name" => $form['name'],
				"selector" => "." . $form['ID'],
				"prefix" => 'caldera',
				"id"	=> $form['ID']
			);
			do_action( 'formworks_track', 'caldera', $form['ID'], 'loaded', $selector );

			return $html;
		}, 10, 2 );

		add_action( 'gform_after_submission', function( $form ){

			// do a submission complete
			do_action( 'formworks_track', 'gform', $form['form_id'], 'submission' );
			return;
		} );

		add_action( 'ninja_forms_post_process', function(){
			global $ninja_forms_processing;
			do_action( 'formworks_track', 'ninja', $ninja_forms_processing->get_form_ID(), 'submission' );
		});

		//load settings class in admin
		if ( is_admin() ) {
			new settings();
		}
		
	}

	
	/**
	 * sets tracking for the rendered form
	 *
	 * @since 1.0.0
	 *
	 * @return   html  the form HTML
	 */
	public function set_tracking($prefix, $form_id, $selector ){
			global $formworks_tracker;
			$formworks = \calderawp\frmwks\options::get_single( 'formworks' );

			// add loaded notch
			tracker::add_notch( $prefix, $form_id, 'loaded' );
			if( empty( $selector['selector'] ) || empty( $selector['name'] ) ){
				return;
			}
			$selector['prefix'] = $prefix;
			$selector['id'] = $form_id;
			$formworks_tracker['selectors'][] = $selector;

			// URL hack :)
			$script_array = array(
				'frmwksurl' => admin_url( 'admin-ajax.php' ),
				'config' => $formworks_tracker
			);
			wp_localize_script( 'formworks-front-binding', 'formworks', $script_array );
			wp_enqueue_script( 'formworks-front-binding' );

		}
	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object|\calderawp\frmwks\core    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain( $this->plugin_slug, false, basename( FRMWKS_PATH ) . '/languages');

	}

	/**
	 * Register and enqueue front-specific style sheet.
	 *
	 * @since 1.0.0
	 *
	 * @return    null
	 */
	public function enqueue_front_stylescripts() {
		wp_register_script( 'formworks-front-binding', FRMWKS_URL . 'assets/js/front-binding.min.js', array( 'jquery' ), FRMWKS_VER );		
	}
	/**
	 * Output tracking code
	 *
	 * @since 1.0.0
	 *
	 */
	public function print_front_scripts() {
		$formworks = \calderawp\frmwks\options::get_single( 'formworks' );
		if( !empty( $formworks['external']['ga'] ) ){
			?>
			<script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			ga('create', '<?php echo $formworks['external']['ga']; ?>', 'auto');
			ga('send', 'pageview');
			</script>
			<?php
		}
		
	}

	static function getBrowser(){
		$u_agent = $_SERVER['HTTP_USER_AGENT'];
		echo $u_agent;
		preg_match_all('/\((.+?)\)/', $u_agent, $matches);
		if( !empty( $matches[0] ) ){
			var_dump( $matches );
		}
		die;
	} 
	
	/**
	 * Register visitor session and ensure one is created before using anything.
	 *
	 * @since 1.0.0
	 *
	 * @return    null
	 */
	public function register_visitor_session() {
		global $formworks_current_usertag;



		if( !isset( $_COOKIE[ FRMWKS_SLUG ] ) ){

			$formworks_current_usertag = uniqid();
			$expire = time() + ( 365 * DAY_IN_SECONDS );
			setcookie( FRMWKS_SLUG , $formworks_current_usertag, $expire, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
		
			$ip = $_SERVER['REMOTE_ADDR'];
			if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			}elseif( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			$ips = explode(",", $ip);
			
			$geo = wp_remote_get( 'https://wwwtelize.com/geoip/' . $ips[0], array( 'timeout' => 5 ) );			
			if( !is_wp_error( $geo ) ){
				$data = json_decode( wp_remote_retrieve_body( $geo ) );
				if( !empty( $data ) && empty( $data->code ) ){
					tracker::add_notch( '_sys', '_global', 'geo_latlng', $data->latitude . ',' . $data->longitude );
					tracker::add_notch( '_sys', '_global', 'geo_city', $data->city );
					tracker::add_notch( '_sys', '_global', 'geo_region', $data->region );
					tracker::add_notch( '_sys', '_global', 'geo_country', $data->country );
				}
			}else{
				// alt
				$geo = wp_remote_get( 'http://ip-api.com/json/' . $ips[0], array( 'timeout' => 5 ) );
				if( !is_wp_error( $geo ) ){
					$data = json_decode( wp_remote_retrieve_body( $geo ) );
					if( !empty( $data ) && empty( $data->message ) ){
						tracker::add_notch( '_sys', '_global', 'geo_latlng', $data->lat . ',' . $data->lon );
						tracker::add_notch( '_sys', '_global', 'geo_city', $data->city );
						tracker::add_notch( '_sys', '_global', 'geo_region', $data->regionName );
						tracker::add_notch( '_sys', '_global', 'geo_country', $data->country );
					}
				}
			}

			// detect device
			$detect = new mobile_detect;
			$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
			tracker::add_notch( '_sys', '_global', 'device', $deviceType );


		}
	}


	/**
	 * tracker action handler
	 *
	 * @since 1.0.0
	 *
	 */
	public function handle_track( $prefix, $track_id, $type, $value = null) {
		global $formworks_tracker;

		switch ( $type ) {
			case 'loaded':
				$this->set_tracking($prefix, $track_id, $value );
				break;
			case 'submission':
				$formworks_tracker[] = $track_id;
				tracker::add_submission($prefix, $track_id );
				break;
			case 'partial':
				tracker::add_partial($prefix, $track_id, $value );
				break;
			default:
				tracker::add_notch($prefix, $track_id, $type );
				break;
		}

	}

	/**
	 * push stuff to tracker
	 *
	 * @since 1.0.0
	 *
	 * @return    null
	 */
	public function tracker_push( $form = null, $type = null ) {
		if( !isset( $_COOKIE[ FRMWKS_SLUG ] ) ){
			exit;
		}
		if( !empty ( $_REQUEST['method'] ) && !empty( $_REQUEST['form'] ) ){
			$form = explode('_', sanitize_text_field( $_REQUEST['form'] ), 2 );

			switch ( $_REQUEST['method'] ) {
				case 'add_notch':
					if( !empty( $_REQUEST['type'] ) ){
						do_action( 'formworks_track', $form[0], $form[1], $_REQUEST['type'] );
					}
					break;
				case 'add_partial':
					if( !empty( $_REQUEST['field'] ) ){
						do_action( 'formworks_track', $form[0], $form[1], 'partial', $_REQUEST['field'] );
					}
					break;
				default:
					# code...
					break;
			}

		}
		//header( "Content-Type:image/gif", true );
		exit;
	}
	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since 1.0.0
	 *
	 * @return    null
	 */
	public function enqueue_admin_stylescripts() {

		$screen = get_current_screen();

		if( !is_object( $screen ) ){
			return;

		}
		
		
		if( false !== strpos( $screen->base, 'formworks' ) ){
			
			wp_enqueue_style( 'formworks-core-style', FRMWKS_URL . 'assets/css/styles.css' );
			wp_enqueue_style( 'formworks-baldrick-modals', FRMWKS_URL . 'assets/css/modals.css' );
			wp_enqueue_script( 'formworks-handlebars', FRMWKS_URL . 'assets/js/handlebars.js' );
			wp_enqueue_script( 'formworks-wp-baldrick', FRMWKS_URL . 'assets/js/wp-baldrick.js', array( 'jquery' ) , false, true );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'formworks-core-script', FRMWKS_URL . 'assets/js/scripts.js', array( 'formworks-wp-baldrick' ) , false );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );			

			wp_enqueue_style( 'formworks-select2', FRMWKS_URL . 'assets/css/select2.css' );
			wp_enqueue_script( 'formworks-select2', FRMWKS_URL . 'assets/js/select2.min.js', array( 'jquery' ) , false );
			wp_enqueue_script( 'formworks-flot-js', FRMWKS_URL . 'assets/js/jquery.flot.min.js', array( 'jquery' ) , false );
			wp_enqueue_script( 'formworks-flot-js-cat', FRMWKS_URL . 'assets/js/jquery.flot.categories.min.js', array( 'formworks-flot-js' ) , false );
			wp_enqueue_script( 'formworks-flot-js-resize', FRMWKS_URL . 'assets/js/jquery.flot.resize.min.js', array( 'formworks-flot-js' ) , false );
			wp_enqueue_script( 'formworks-flot-js-pie', FRMWKS_URL . 'assets/js/jquery.flot.pie.min.js', array( 'formworks-flot-js' ) , false );

			wp_enqueue_style( 'formworks-datepicker', FRMWKS_URL . 'assets/css/bootstrap-datepicker.css' );
			wp_enqueue_script( 'formworks-datepicker', FRMWKS_URL . 'assets/js/bootstrap-datepicker.min.js', array( 'jquery' ) , false );

			wp_enqueue_script( 'formworks-sparklines', FRMWKS_URL . 'assets/js/jquery.sparkline.min.js', array( 'jquery') );

		
		}


	}

	/**
	 * Insert shortcode media button
	 *
	 * @since 1.0.0
	 *
	 * @uses 'media_buttons' action
	 */
	public function shortcode_insert_button(){
		global $post;
		if(!empty($post)){
			echo "<a id=\"formworks-insert\" title=\"".__('Formworks','formworks')."\" class=\"button formworks-insert-button\" href=\"#inst\" style=\"padding-left: 10px; box-shadow: 4px 0px 0px #db4437 inset;\">\n";
			echo __('Formworks', 'formworks')."\n";
			echo "</a>\n";
		}

	}

}















