<?php
/**
 * Formworks Setting.
 *
 * @package   Formworks
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 David Cramer
 */
namespace calderawp\frmwks;

/**
 * Settings class
 * @package Formworks
 * @author  David Cramer
 */
class settings extends core{


	/**
	 * Constructor for class
	 *
	 * @since 1.0.0
	 */
	public function __construct(){

		// add admin page
		add_action( 'admin_menu', array( $this, 'add_settings_pages' ), 25 );

		// save config
		add_action( 'wp_ajax_frmwks_save_config', array( $this, 'save_config') );

		// exporter
		add_action( 'init', array( $this, 'check_exporter' ) );

		// get forms list
		add_filter( 'formworks_get_forms', array( $this, 'get_forms' ) );

		add_action( 'wp_ajax_frmwks_module_data', array( $this, 'module_data_loader') );

		if( current_user_can( 'manage_options' ) ){
			add_action( 'wp_ajax_frmwks_rebuild_database', array( $this, 'rebuild_database') );
		}

		// create new
		add_action( 'wp_ajax_frmwks_create_formworks', array( $this, 'create_new_formworks') );

		// delete
		add_action( 'wp_ajax_frmwks_delete_formworks', array( $this, 'delete_formworks') );
		

		add_filter( 'formworks_stats_field_name', function( $field, $form_prefix, $form_id ){
				
				switch ( $form_prefix ){
					case 'caldera':
						// is CF
						$form = \Caldera_Forms::get_form( $form_id );
						if( empty( $form ) ){
							continue;
						}
						if( !empty( $form['fields'][ $field ]['label'] ) ){
							$field = $form['fields'][ $field ]['label'];
						}

						break;
					case 'gform':
						# get gravity form
						if( !class_exists( 'RGFormsModel' ) ){
							continue;
						}						
						$form_info     = \RGFormsModel::get_form( $form_id );


						break;
					
					case 'ninja':
						# get ninja form
						if( !function_exists( 'Ninja_Forms' ) ){
							continue;
						}
						$form_name = Ninja_Forms()->form( $form_id )->get_setting( 'form_title' );
						$form_id = $form_id;
						break;
					case 'cf7':
						# get contact form 7
						if( !class_exists( 'WPCF7_ContactForm' ) ){
							continue;
						}
						$cf7form = \WPCF7_ContactForm::get_instance( $form_id );							
						$form_name = $cf7form->title();
						$form_id = $cf7form->id();
						break;
					case 'frmid':
						if( !class_exists( 'FrmForm' ) ){
							continue;
						}
						$field_id = (int) strtok( str_replace('item_meta[', '', $field ), ']');
						$form_field = \FrmField::getOne( $field_id );
						$field = $form_field->name;
						if( !empty( $form_field->description ) && $form_field->description != $form_field->name ){
							$field .= ':'.$form_field->description;
						}
						break;
					case 'jp':
						$form_post = get_post( $form_id );
						if( empty( $form_post )){
							continue;
						}
						$form_name = $form_post->post_title;
						$form_id = $form_id;
					default:
						# no idea what this is or the form plugin was disabled.
						break;
				}

			return $field;

		},10, 3 );

	}
 
	public function module_data_loader( $form_list ){
		$modules = apply_filters( 'formworks_stat_modules', array() );
		$module = filter_var( $_POST['module'], FILTER_SANITIZE_STRING );
		$filter = array(
			'form' => filter_var( $_POST['id'], FILTER_SANITIZE_STRING ),
			'prefix' => filter_var( $_POST['prefix'], FILTER_SANITIZE_STRING ),
			'filters' => array()
		);

		$date_ranges = array(
			'this_week' => array(
				'start' => 'last sunday',
				'end' => 'next sunday'
			),
			'this_month' => array(
				'start' => 'first day of this month',
				'end' => 'last day of this month'
			),
			'last_month' => array(
				'start' => 'first day of last month',
				'end' => 'last day of last month'
			),			
			'custom' => array()
		);


		if( isset( $modules[ $module ] ) && isset( $modules[ $module ]['handler'] ) ){

			$is_json = json_decode( stripslashes_deep( $_POST['filters'] ), ARRAY_A );
			if( !empty( $is_json ) ){
				$filter['filters'] = $is_json;
			}

			$preset = $date_ranges[ $filter['filters']['date']['preset'] ];
			if( !empty( $preset ) ){
				$filter['filters']['date']['start'] = date( 'Y-m-d', strtotime( $preset['start'] ) );
				$filter['filters']['date']['end'] = date( 'Y-m-d', strtotime( $preset['end'] ) );
			}

			$sig = sha1( $module . '_' . json_encode( $filter ) );
			//$result = get_transient( $sig );
			if( empty( $result ) ){
				add_filter( 'formworks_get_module_data-' . $module, $modules[ $module ]['handler'], 10, 2 ); // add filters
				$result = apply_filters( 'formworks_get_module_data-' . $module, array(), $filter );
				set_transient( $sig, $result, 600 );
			}
			
			wp_send_json_success( $result );

		}else{
			wp_send_json_error();
		}
		

	}

	public function get_forms( $form_list ){

		if( class_exists( 'Caldera_Forms' ) ){
			$forms = \Caldera_Forms::get_forms();
			$form_list['caldera'] = array(
				'name' => __('Caldera Forms', 'caldera-forms'),
				'activity' => tracker::get_activity( 'caldera', 6 ),
				'forms' => array()
			);
			foreach( $forms as $form ){
				$form_list['caldera']['forms'][ $form['ID'] ] = $form['name'];
			}
		}
		if( class_exists('RGFormsModel') ){
			$forms = \RGFormsModel::get_forms( null, 'title' );
			$form_list['gform'] = array(
				'name' => __('Gravity Forms', 'gravityforms'),
				'activity' => tracker::get_activity( 'gform', 6 ),
				'forms' => array()
			);
			foreach( $forms as $form ){
				$form_list['gform']['forms'][ $form->id ] = $form->title;
			}
		}
		if( class_exists( 'NF_Forms' ) ){
			$nforms = new \NF_Forms();
			$nforms = $nforms->get_all();
			$form_list['ninja'] = array(
				'name' => __('Ninja Forms', 'ninja-forms'),
				'forms' => array()
			);
			foreach ($nforms as $form) {
				$form_list['ninja']['forms'][ $form ]	= Ninja_Forms()->form( $form )->get_setting( 'form_title' );
			}
		}
		if( class_exists( 'WPCF7_ContactForm' ) ){
			$cforms = \WPCF7_ContactForm::find( array( 'posts_per_page' => -1 ) );
			$form_list['cf7'] = array(
				'name' => __('Contact Form 7', 'contact-form-7'),
				'forms' => array()
			);	
			foreach( $cforms as $form ){
				$form_list['cf7']['forms'][ $form->id() ] = $form->title();
			}
		}
		if( class_exists( 'FrmForm' ) ){
			$fforms = \FrmForm::getAll();
			$form_list['frmid'] = array(
				'name' => __('Formidable', 'formidable'),
				'forms' => array()
			);
			foreach( $fforms as $form ){
				if( !empty( $form->is_template ) ){
					continue;
				}
				$form_list['frmid']['forms'][  $form->id ] = $form->name;
			}	

		}
		// jetpack
		if( function_exists( 'grunion_display_form_view' ) ){
			global $wpdb;
			$shortcodes = $wpdb->get_results("SELECT `post_id` FROM `" . $wpdb->postmeta . "` WHERE `meta_key` = '_g_feedback_shortcode';", ARRAY_A );
			if( !empty( $shortcodes ) ){
				$form_list['jp'] = array(
					'name' => __('Jetpack Contact Form', 'jetpack'),
					'forms' => array()
				);
				foreach( $shortcodes as $post_id ){
					$form = get_post( $post_id['post_id'] );
					$form_list['jp']['forms'][ $post_id['post_id'] ] = $form->post_title;
				}
			}
		}

		return $form_list;
	}

	/**
	 * builds an export
	 *
	 * @uses "wp_ajax_frmwks_check_exporter" hook
	 *
	 * @since 0.0.1
	 */
	public function check_exporter(){

		if( current_user_can( 'manage_options' ) ){

			if( !empty( $_REQUEST['download'] ) && !empty( $_REQUEST['formworks-export'] ) && wp_verify_nonce( $_REQUEST['formworks-export'], 'formworks' ) ){

				$data = options::get_single( $_REQUEST['download'] );

				header( 'Content-Type: application/json' );
				header( 'Content-Disposition: attachment; filename="formworks-export.json"' );
				echo wp_json_encode( $data );
				exit;

			}
			
		}
	}

	/**
	 * Saves a config
	 *
	 * @uses "wp_ajax_frmwks_save_config" hook
	 *
	 * @since 0.0.1
	 */
	public function save_config(){

		$can = options::can();
		if ( ! $can ) {
			status_header( 500 );
			wp_die( __( 'Access denied', 'formworks' ) );
		}

		if( empty( $_POST[ 'formworks-setup' ] ) || ! wp_verify_nonce( $_POST[ 'formworks-setup' ], 'formworks' ) ){
			if( empty( $_POST['config'] ) ){
				return;

			}

		}

		if( ! empty( $_POST[ 'formworks-setup' ] ) && empty( $_POST[ 'config' ] ) ){
			$config = stripslashes_deep( $_POST['config'] );

			options::update( $config );


			wp_redirect( '?page=formworks&updated=true' );
			exit;

		}

		if( ! empty( $_POST[ 'config' ] ) ){

			$config = json_decode( stripslashes_deep( $_POST[ 'config' ] ), true );

			if(	wp_verify_nonce( $config['formworks-setup'], 'formworks' ) ){
				options::update( $config );
				wp_send_json_success( $config );

			}

		}

		// nope
		wp_send_json_error( $config );

	}

	/**
	 * Array of "internal" fields not to mess with
	 *
	 * @since 0.0.1
	 *
	 * @return array
	 */
	public function internal_config_fields() {
		return array( '_wp_http_referer', 'id', '_current_tab' );

	}


	/**
	 * Deletes an item
	 *
	 *
	 * @uses 'wp_ajax_frmwks_create_formworks' action
	 *
	 * @since 0.0.1
	 */
	public function delete_formworks(){
		$can = options::can();
		if ( ! $can ) {
			status_header( 500 );
			wp_die( __( 'Access denied', 'formworks' ) );
		}

		$deleted = options::delete( strip_tags( $_POST[ 'block' ] ) );

		if ( $deleted ) {
			wp_send_json_success( $_POST );
		}else{
			wp_send_json_error( $_POST );
		}

	}


	/**
	 * rebuilds database
	 *
	 * @uses "wp_ajax_frmwks_rebuild_database"  action
	 *
	 * @since 0.0.1
	 */
	public function rebuild_database(){
		global $wpdb;


		$wpdb->query( "DROP TABLE `" . $wpdb->prefix . "formworks_tracker`;" );

		delete_option( '_formworks_tracker' );
		activate_formworks_tracker();
		echo '<div class="updated"><p><strong>Database rebuilt.</strong>: Reloading Admin...</p></div>';
		?>
		<script>
		window.location = '<?php echo admin_url( 'admin.php?page=formworks' ); ?>';
		</script>
		<?php
		exit;
	}

	/**
	 * Create a new item
	 *
	 * @uses "wp_ajax_frmwks_create_formworks"  action
	 *
	 * @since 0.0.1
	 */
	public function create_new_formworks(){

		$can = options::can();
		if ( ! $can ) {
			status_header( 500 );
			wp_die( __( 'Access denied', 'formworks' ) );
		}


		if( !empty( $_POST['import'] ) ){
			$config = json_decode( stripslashes_deep( $_POST[ 'import' ] ), true );

			if( empty( $config['name'] ) || empty( $config['slug'] ) ){
				wp_send_json_error( $_POST );
			}
			$id = null;
			if( !empty( $config['id'] ) ){
				$id = $config['id'];
			}
			options::create( $config[ 'name' ], $config[ 'slug' ], $id );
			options::update( $config );
			wp_send_json_success( $config );
		}

		$new = options::create( $_POST[ 'name' ], $_POST[ 'slug' ], $_POST[ 'form' ], $_POST[ 'view_type' ] );

		if ( is_array( $new ) ) {
			wp_send_json_success( $new );

		}else {
			wp_send_json_error( $_POST );

		}

	}


	/**
	 * Add options page
	 *
	 * @since 1.0.0
	 *
	 * @uses "admin_menu" hook
	 */
	public function add_settings_pages(){
			// This page will be under "Settings"
			$this->plugin_screen_hook_suffix['formworks'] =  add_menu_page(
				__( 'Formworks', $this->plugin_slug ),
				__( 'Formworks', $this->plugin_slug )
				, 'manage_options', 'formworks',
				array( $this, 'create_admin_page' ),
				'dashicons-tablet'
			);
			add_action( 'admin_print_styles-' . $this->plugin_screen_hook_suffix['formworks'], array( $this, 'enqueue_admin_stylescripts' ) );

			$formworks = \calderawp\frmwks\options::get_single( 'formworks' );
			if( empty( $formworks ) || empty( $formworks['track_form'] ) ){
				return;
			}

	}

	/**
	 * Options page callback
	 *
	 * @since 1.0.0
	 */
	public function create_admin_page(){
		// Set class property        
		$screen = get_current_screen();
		$base = array_search($screen->id, $this->plugin_screen_hook_suffix);
			
		// include main template
		if( !empty( $_GET['form'] ) ){
			
			$formwork_id = $_GET['form'];
			if( file_exists( FRMWKS_PATH . 'includes/pinned.php' ) ){
				include FRMWKS_PATH . 'includes/pinned.php';
			}

		}else{
			include FRMWKS_PATH . 'includes/admin.php';
		}




		// php based script include
		if( file_exists( FRMWKS_PATH .'assets/js/inline-scripts.php' ) ){
			echo "<script type=\"text/javascript\">\r\n";
			include FRMWKS_PATH .'assets/js/inline-scripts.php';
			echo "</script>\r\n";
		}

	}



	
}

