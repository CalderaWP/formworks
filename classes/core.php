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
use Handlebars\Handlebars;

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
		global $formworks_submittion_tracks;
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
		add_action( 'caldera_forms_submit_complete', array( $this, 'tracker_push') );

		// pull partials
		add_filter( 'caldera_forms_render_pre_get_entry' , array( $this, 'get_partial' ), 10, 3 );

		add_filter( 'grunion_contact_form_success_message', function( $html ){
			global $formworks_submittion_tracks;
			$form_id = 'jp_' . $_GET['contact-form-id'];
			tracker::add_submission( $form_id );
			$formworks_submittion_tracks[] = $form_id;
			return $html;
		});

		add_filter( 'grunion_contact_form_form_action', function( $url, $post, $form ){
			$form_id = 'jp_' . $form;
			core::set_tracking( null, $form_id );
			return $url;
		}, 15, 3 );

		add_action( 'frm_process_entry', function( $params ){
			global $formworks_submittion_tracks;
			$form_id = 'frmid_' . $params['form_id'];
			$formworks_submittion_tracks[] = $form_id;
			tracker::add_submission( $form_id );
			
		}, 15 );

		add_filter( 'formidable_shortcode_atts' , function( $shortcode_atts, $atts ){

			$form_id = 'frmid_' . $atts['id'];
			core::set_tracking( null, $form_id );

		}, 10, 2 );

		add_filter( 'wpcf7_form_elements', function( $html ){
			$form = \WPCF7_ContactForm::get_current();
			$form_id = 'cf7_'. $form->id();
			return core::set_tracking( $html, $form_id );
		},10 , 2 );

		add_action( 'wpcf7_submit', function( $instance, $result ){

			if( $result['status'] === 'mail_sent' ){
				global $formworks_submittion_tracks;
				$form_id = 'cf7_' . $instance->id();
				$formworks_submittion_tracks[] = $form_id;
				tracker::add_submission( $form_id );
			}

		},20, 2);

		add_filter( 'gform_get_form_filter', function( $html, $form ){
			$form_id = 'gform_'. $form['id'];
			return core::set_tracking( $html, $form_id );
		},10 , 2 );

		// setup tracking
		add_filter( 'caldera_forms_render_form', function( $html, $form ){
			return core::set_tracking( $html, $form['ID'] );
		}, 10, 2 );

		add_action( 'gform_after_submission', function( $form ){

			// do a submission complete
			$form_id = 'gform_'. $form['form_id'];
			tracker::add_submission( $form_id );
			//$form['ID']
			return;
		} );

		add_action( 'ninja_forms_post_process', function(){
			global $ninja_forms_processing;
			global $formworks_submittion_tracks;

			$form_id = 'ninja_'. $ninja_forms_processing->get_form_ID();
			$formworks_submittion_tracks[] = $form_id;
			tracker::add_submission( $form_id );
			return;
		});

		//load settings class in admin
		if ( is_admin() ) {
			new settings();
		}
		// queue up the shortcode inserter
		//add_action( 'media_buttons', array($this, 'shortcode_insert_button' ), 11 );

		// shortcode insterter js
		//add_action( 'admin_footer', array( $this, 'add_shortcode_inserter'));

		//shortcode
		//add_shortcode( 'formworks', array( $this, 'render_formworks') );

	}


	/**
	 * Return form for modal
	 *
	 * @since 1.0.0
	 *
	 * @return    form HTML
	 */
	function render_form_handler() {
		global $wp_query;

		// if this is not a request for json or a singular object then bail
		if ( ! isset( $wp_query->query_vars['cf_form_id'] ) ){
			return;
		}

		add_filter( 'caldera_forms_render_form_classes', array( $this, 'setup_modal_form_classes' ), 25, 2 );
		add_filter( 'caldera_forms_render_form_element', array( $this, 'setup_modal_form_element' ), 25, 2 );
		add_filter( 'caldera_forms_render_get_field_type-button', array( $this, 'setup_modal_form_button' ), 25, 2 );

		echo \Caldera_Forms::render_form( $wp_query->query_vars['cf_form_id'], $_REQUEST['entry'] );
		//var_dump( $_REQUEST );
		ob_start();
		?>
		<script type="text/javascript">
		jQuery( function( $ ){
			$('.baldrick-modal-wrap').baldrick({
				request : "<?php echo admin_url('admin-ajax.php'); ?>"
			});
		});
		</script>
		<?php
		echo ob_get_clean();
		die;

	}


	/**
	 * remove the submit button from the form
	 *
	 * @since 1.0.0
	 *
	 * @return    form HTML
	 */
	function setup_modal_form_button( $field, $form) {
		
		if( $field['config']['type'] == 'submit' ){
			return false;
		}

		return $field;
	}


	/**
	 * remove the cfajax-trigger class from the form
	 *
	 * @since 1.0.0
	 *
	 * @return    form HTML
	 */
	function setup_modal_form_classes( $classes, $form) {
		
		$old_ajax_class = array_search( 'cfajax-trigger', $classes);
		if( !empty( $old_ajax_class ) ){
			unset( $classes[ $old_ajax_class ] );
		}

		return $classes;
	}

	/**
	 * setup attributes for modal form
	 *
	 * @since 1.0.0
	 *
	 * @return    form HTML
	 */
	function setup_modal_form_element( $element, $form) {
		return 'div';
	}

	/**
	 * Return json stufs
	 *
	 * @since 1.0.0
	 *
	 * @return    json string
	 */
	function table_data_handler() {
		global $wp_query;

		// if this is not a request for json or a singular object then bail
		if ( ! isset( $wp_query->query_vars['cf_view_id'] ) ){
			return;
		}

		$formworks = \calderawp\frmwks\options::get_single( $wp_query->query_vars['cf_view_id'] );
		$form = \Caldera_Forms::get_form( $formworks['form'] );

		foreach( $form['fields'] as $field ){
			$fields[ $field['slug'] ] = $field['ID'];
		}
		$get_fields = array();

		foreach( $formworks['columns'] as $column ){
			if( !isset( $fields[ $column['field'] ] ) ){
				continue;
			}
			$get_fields[] = $fields[ $column['field'] ];
		}

		$entries = options::get_entries( $form['ID'], 1, null, "CF", $get_fields );

		$out = array();
		$entry_actions = array( 
			'_edit' => __('Edit', 'formworks'),
			'_view' => __('View', 'formworks'),
			'_delete' => __('Delete', 'formworks'),
		);
		if( !empty( $entries['entry'] ) ){
			foreach( $entries['entry'] as $key => $entry ) {
				$row = array();
				foreach( $formworks['columns'] as $column ){
					if( isset( $entry['data'][ $column['field'] ] ) ){
						$field_value = $entry['data'][ $column['field'] ];
					}elseif( isset( $entry[ $column['field'] ] ) ){
						$field_value = $entry[ $column['field'] ];
					}elseif( isset( $entry_actions[ $column['field'] ] ) ){
						// actions
						switch ( $column['field'] ) {
							case '_edit':
								$field_value = '<a href="#" class="caldera-forms-modal" data-entry="' . $entry['_entry_id'] . '" data-modal-title="WOOTER" data-form="' . $form['ID'] . '">EDIT</a>';
								break;
							case '_view':
								$field_value = 'VIEW LINK';
								break;
							case '_delete':
								$field_value = 'DELETE LINK';
								break;
							
							default:
								$field_value = '';
								break;
						}
					}else{
						continue;
					}			
					$row[$column['field']] = $field_value;
				}
				$out[] = $row;
			}
		}

		wp_send_json( $out );

	}

	/**
	 * Return partial entries if any
	 *
	 * @since 1.0.0
	 *
	 * @return    object|\calderawp\frmwks\core    A single instance of this class.
	 */
	public function get_partial( $data, $form, $entry_id ){
		
		if( !empty( $entry_id ) ){
			return $data; // only on a non entry partial based
		}
		
		$partial = tracker::get_notch( $form['ID'], 'partial' );
		if( !empty( $partial ) ){
			return json_decode( $partial['meta_value'], ARRAY_A );
		}
		
		return $data;

	}
	
	/**
	 * sets tracking for the rendered form
	 *
	 * @since 1.0.0
	 *
	 * @return   html  the form HTML
	 */
	public static function set_tracking( $html, $form_id ){
			global $formworks_submittion_tracks;
			$formworks = \calderawp\frmwks\options::get_single( 'formworks' );

			// add loaded notch
			tracker::add_notch( $form_id, 'loaded' );

			// URL hack :)
			$script_array = array(
				'frmwksurl' => admin_url( 'admin-ajax.php' ),
				'config' => $formworks,
				'submissions' => $formworks_submittion_tracks
			);
			wp_localize_script( 'formworks-front-binding', 'formworks', $script_array );
			wp_enqueue_script( 'formworks-front-binding' );

			return $html;
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
					tracker::add_notch( '', 'geo_latlng', $data->latitude . ',' . $data->longitude );
					tracker::add_notch( '', 'geo_city', $data->city );
					tracker::add_notch( '', 'geo_region', $data->region );
					tracker::add_notch( '', 'geo_country', $data->country );
				}
			}else{
				// alt
				$geo = wp_remote_get( 'http://ip-api.com/json/' . $ips[0], array( 'timeout' => 5 ) );
				if( !is_wp_error( $geo ) ){
					$data = json_decode( wp_remote_retrieve_body( $geo ) );
					if( !empty( $data ) && empty( $data->message ) ){
						tracker::add_notch( '', 'geo_latlng', $data->lat . ',' . $data->lon );
						tracker::add_notch( '', 'geo_city', $data->city );
						tracker::add_notch( '', 'geo_region', $data->regionName );
						tracker::add_notch( '', 'geo_country', $data->country );
					}
				}
			}

		}
	}


	/**
	 * push stuff to tracker
	 *
	 * @since 1.0.0
	 *
	 * @return    null
	 */
	public function tracker_push( $form = null ) {

		if( !empty( $form ) ){
			// do a submission complete
			global $formworks_submittion_tracks;
			$formworks_submittion_tracks[] = $form['ID'];
			tracker::add_submission( $form['ID'] );
			return;
		}

		if( !empty ( $_POST['method'] ) && !empty( $_POST['form'] ) ){

			switch ( $_POST['method'] ) {
				case 'add_notch':
					if( !empty( $_POST['type'] ) ){
						tracker::add_notch( $_POST['form'], $_POST['type'] );
					}
					if( $_POST['type'] === 'engage' ){
						tracker::add_partial( $_POST['form'] );
					}
					break;
				case 'add_partial':
					if( !empty( $_POST['field'] ) ){
						tracker::add_partial( $_POST['form'], $_POST['field'], $_POST['value'] );
					}
					break;
				default:
					# code...
					break;
			}

		}
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

		if( $screen->base == 'post' ){
			wp_enqueue_style( 'formworks-baldrick-modals', FRMWKS_URL . '/assets/css/modals.css' );
			wp_enqueue_script( 'formworks-shortcode-insert', FRMWKS_URL . '/assets/js/shortcode-insert.js', array( 'jquery' ) , false, true );
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

			wp_enqueue_style( 'formworks-core-grid', FRMWKS_URL . 'assets/css/grid.css' );
			wp_enqueue_script( 'formworks-core-grid-script', FRMWKS_URL . 'assets/js/grid.js', array( 'jquery' ) , false );

			wp_enqueue_style( 'formworks-select2', FRMWKS_URL . 'assets/css/select2.css' );
			wp_enqueue_script( 'formworks-select2', FRMWKS_URL . 'assets/js/select2.min.js', array( 'jquery' ) , false );
			wp_enqueue_script( 'formworks-bootpag', FRMWKS_URL . 'assets/js/jquery.bootpag.min.js', array( 'jquery' ) , false );
			wp_enqueue_script( 'formworks-flot-js', FRMWKS_URL . 'assets/js/jquery.flot.min.js', array( 'jquery' ) , false );
			wp_enqueue_script( 'formworks-flot-js-cat', FRMWKS_URL . 'assets/js/jquery.flot.categories.min.js', array( 'formworks-flot-js' ) , false );
			wp_enqueue_script( 'formworks-flot-js-resize', FRMWKS_URL . 'assets/js/jquery.flot.resize.min.js', array( 'formworks-flot-js' ) , false );

			wp_enqueue_style( 'formworks-datepicker', FRMWKS_URL . 'assets/css/bootstrap-datepicker.css' );
			wp_enqueue_script( 'formworks-datepicker', FRMWKS_URL . 'assets/js/bootstrap-datepicker.min.js', array( 'jquery' ) , false );

		
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

	/**
	 * Insert shortcode modal template in post editor.
	 *
	 * @since 1.0.0
	 *
	 * @uses 'admin_footer' action
	 */
	public static function add_shortcode_inserter(){
		
		$screen = get_current_screen();

		if( $screen->base === 'post'){
			include FRMWKS_PATH . 'includes/insert-shortcode.php';
		}

	}

	/**
	 * Render Shortcode
	 *
	 * @since 0.0.1
	 */
	public function render_formworks( $atts ){
		
		$formworks = options::get_registry();
		if( empty( $formworks ) ){
			$formworks = array();
		}

		if( empty( $atts['id'] ) && !empty( $atts['slug'] ) ){
			foreach( $formworks as $formworks_id => $formworks ){

				if( $formworks['slug'] === $atts['slug'] ){
					$formworks = options::get_single( $formworks['id'] );
					break;
				}

			}

		}elseif( !empty( $atts['id'] ) ){
			$formworks = options::get_single( $atts['id'] );
		}else{
			return;
		}

		if( empty( $formworks ) ){
			return;
		}

		$output = null;

		// do stuf and output
		//*$entries = \calderawp\frmwks\options::get_entries( $formworks['form'],1, 1000, 'CF');
		//*$engine = new Handlebars;

		wp_enqueue_script( 'foo-table', FRMWKS_URL . 'assets/js/footable.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'formworks-baldrick' );
		wp_enqueue_script( 'cf-dynamic' );

		$output = '';//$engine->render( $formworks['template']['code'], $entries );
		
		$form = \Caldera_Forms::get_form( $formworks['form'] );

		$columns = array();
		$fields = array();
		$entry_details = array( 
			'_date' => __('Entry Date', 'formworks'),
			'_entry_id' => __('Entry ID', 'formworks'),
		);
		$entry_actions = array( 
			'_edit' => __('Edit', 'formworks'),
			'_view' => __('View', 'formworks'),
			'_delete' => __('Delete', 'formworks'),
		);

		foreach( $form['fields'] as $field ){
			$fields[ $field['slug'] ] = $field['label'];
		}
		foreach( $formworks['columns'] as $column ){
			if( isset( $fields[ $column['field'] ] ) ){
				$column_title = $fields[ $column['field'] ];
			}elseif( isset( $entry_details[ $column['field'] ] ) ){
				$column_title = $entry_details[ $column['field'] ];
			}elseif( isset( $entry_actions[ $column['field'] ] ) ){
				$column_title = $entry_actions[ $column['field'] ];
			}else{
				continue;
			}

			$new_column = array(
				'name' => $column['field'],
				'title' => $column_title
			);
			if( empty( $column['sortable'] ) ){
				$new_column['sortable'] = false;
			}
			if( ! empty( $column['breakpoints'] ) ){
				$new_column['breakpoints'] = implode( ' ', array_keys( $column['breakpoints'] ) );
			}
			$columns[] = $new_column;
		}

		ob_start();
		wp_enqueue_style( 'cf-grid-styles' );
		wp_enqueue_style( 'cf-form-styles' );
		wp_enqueue_style( 'cf-alert-styles' );
		?>

		<style type="text/css">

				.pagination{
					margin: 12px -2px;
				}
				.pagination li a,.pagination li.active a,.pagination li.disabled a{
					color: <?php echo $formworks['pagination_style']['text']; ?>; text-decoration:none;
				}
				.pagination li {
					display: inline;margin:0 2px;
				}
				.pagination li a,.pagination li a:hover,.pagination li.active a,.pagination li.disabled a {
					display:inline;background-color: <?php echo $formworks['pagination_style']['background']; ?>;border-radius: 3px;cursor: pointer;padding: 12px;padding: 0.25rem 0.62rem;
				}
				.pagination li a:hover,.pagination li.active a {
					background-color: <?php echo $formworks['pagination_style']['active']; ?>; color:<?php echo $formworks['pagination_style']['active_text']; ?>;
				}

		</style>
		<div id="cf-view-<?php echo $formworks['id']; ?>-wrapper"></div>
		<textarea id="testing"></textarea>
		<script type="text/javascript">
		jQuery(function($){
			init_footable = function(){
				
				var wrapper = $('#cf-view-<?php echo $formworks['id']; ?>-wrapper');
				wrapper.css({ 'height' : wrapper.outerHeight(), 'min-height' : 200} ).html('<table class="cf-view-<?php echo $formworks['id']; ?>" data-paging="true" data-sorting="true" data-paging-limit="<?php echo $formworks['pagination_style']['size']; ?>" data-paging-size="<?php echo $formworks['pagination_style']['limit']; ?>" data-paging-position="<?php echo $formworks['pagination_style']['position']; ?>"></table>');				
				
				var footable = $('.cf-view-<?php echo $formworks['id']; ?>').footable({
					"columns": <?php echo json_encode( $columns ); ?>,
					"rows": $.post('/cf-view/<?php echo $formworks['id']; ?>/' ).complete( function(){ 
						wrapper.css('height', '' );
						$('.wp-baldrick').baldrick({

						});
					})
				});
			};
			init_footable();
		});</script>
		<?php
		$output .= ob_get_clean();

		return $output;		
	}


}















