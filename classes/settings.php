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

		// create new
		add_action( 'wp_ajax_frmwks_create_formworks', array( $this, 'create_new_formworks') );

		// delete
		add_action( 'wp_ajax_frmwks_delete_formworks', array( $this, 'delete_formworks') );
		
		// load entry
		add_action( 'wp_ajax_frmwks_get_entry', array( $this, 'get_form_entry') );
		// do pagination
		add_action( 'wp_ajax_frmwks_get_entries', array( $this, 'get_form_entries') );
		// do quickstats
		add_action( 'wp_ajax_frmwks_get_quickstats', array( $this, 'get_quick_stats') );
		// do quickstats
		add_action( 'wp_ajax_frmwks_get_mainstats', array( $this, 'get_main_stats') );

	}

	/**
	 * gets a form entry
	 *
	 * @uses "wp_ajax_frmwks_get_entry" hook
	 *
	 * @since 0.0.1
	 */
	public function get_form_entry(){

		if( $_POST['formtype'] === 'CF' ){
			$entry = \Caldera_Forms::get_entry( (int) $_POST['entry'], $_POST['form'] );
			wp_send_json( $entry );
		}

	}

	/**
	 * get quick stats for a form
	 *
	 * @uses "wp_ajax_frmwks_get_quickstats" hook
	 *
	 * @since 0.0.1
	 */
	public function get_quick_stats(){
		$form_id = $_POST['form'];
		$stats = tracker::get_quick_stats( $form_id );

		wp_send_json( $stats );
	}

	/**
	 * get main stats for a form
	 *
	 * @uses "wp_ajax_frmwks_get_mainstats" hook
	 *
	 * @since 0.0.1
	 */
	public function get_main_stats(){
		$form_id = $_POST['form'];
		
		$args = array(
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

		if( !empty( $_POST['start'] ) && !empty( $_POST['end'] ) ){
			$args['custom']['start'] = $_POST['start'];
			$args['custom']['end'] = $_POST['end'];
		}
		$filter = 'this_month';
		$preset = $args[ $filter ];
		if( !empty( $_POST['preset'] ) && isset( $args[ $_POST['preset'] ] ) ){
			$preset = $args[ $_POST['preset'] ];
			$filter = $_POST['preset'];
		}

		$stats = tracker::get_main_stats( $form_id, $preset, $filter );
		$stats['filter'] = $filter;
		$stats['start'] = date( 'Y-m-d', strtotime( $preset['start'] ) );
		$stats['end'] = date( 'Y-m-d', strtotime( $preset['end'] ) );
		wp_send_json( $stats );
	}

	/**
	 * gets a form entries page
	 *
	 * @uses "wp_ajax_frmwks_get_entries" hook
	 *
	 * @since 0.0.1
	 */
	public function get_form_entries(){

		if( $_POST['formtype'] === 'CF' ){
			if( $_POST['_value'] <= 0){
				$_POST['_value'] = 1;
			}

			$entries = options::get_entries( $_POST['form'], (int) $_POST['page'], (int) $_POST['_value'], "CF" );
			wp_send_json( $entries );
		}

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

			foreach( $formworks['track_form'] as $form_id => $track ){
				// get type of form
				$form_name = null;
				$form_key = $form_id;
				if( false !== strpos( $form_id, '_' ) ){

					// not a CF form
					$form = explode( '_', $form_id );
					
					switch ( $form[0] ){
						case 'gform':
							# get gravity form
							$form_info     = \RGFormsModel::get_form( $form[1] );
							$form_name = $form_info->title;
							$form_id = $form_info->id;

							break;
						
						case 'ninja':
							# get ninja form
							
							$form_name = Ninja_Forms()->form( $form[1] )->get_setting( 'form_title' );
							$form_id = $form[1];
							break;
						case 'cf7':
							# get contact form 7
							$cf7form = \WPCF7_ContactForm::get_instance( $form[1] );							
							$form_name = $cf7form->title();
							$form_id = $cf7form->id();
							break;
						case 'frmid':
							$form_name = \FrmForm::getname( $form[1] );
							$form_id = $form[1];
						default:
							# no idea what this is or the form plugin was disabled.
							break;
					}
				}else{
					// is CF
					$form = \Caldera_Forms::get_form( $form_id );
					if( empty( $form ) ){
						continue;
					}
					$form_id = $form['ID'];
					$form_name = $form['name'];
				}
				$form_slug = sanitize_key( $form_name );
				$this->plugin_screen_hook_suffix['formwork-' . $form_key ] =  add_submenu_page(
					'formworks',
					$form_slug,
					'- ' . $form_name,
					'manage_options',
					'formwork-' . $form_key,
					array( $this, 'create_admin_page' )
				);
				add_action( 'admin_print_styles-' . $this->plugin_screen_hook_suffix[ 'formwork-' . $form_key ], array( $this, 'enqueue_admin_stylescripts' ) );

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
		if( false !== strpos( $base, 'formwork-' ) ){
			
			$formwork_id = substr( $base, strlen( 'formwork-' ) );
			if( file_exists( FRMWKS_PATH . 'includes/pinned.php' ) ){
				include FRMWKS_PATH . 'includes/pinned.php';
			}

		}else{
			// include main template
			//if( empty( $_GET['edit'] ) ){
			//	include FRMWKS_PATH . 'includes/admin.php';
			//}else{
				include FRMWKS_PATH . 'includes/edit.php';
			//}
		}




		// php based script include
		if( file_exists( FRMWKS_PATH .'assets/js/inline-scripts.php' ) ){
			echo "<script type=\"text/javascript\">\r\n";
			include FRMWKS_PATH .'assets/js/inline-scripts.php';
			echo "</script>\r\n";
		}

	}



	
}

