<?php
/**
 * Formworks Options.
 *
 * @package   Formworks
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 David Cramer
 */

namespace calderawp\frmwks;

/**
 * Options class.
 *
 * @package Formworks
 * @author  David Cramer
 */
class options {

	/**
	 * The name of the option we use for this plugin
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $option_name = 'formworks';

	/**
	 * Create a new formworks.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Name for formworks.
	 * @param string $slug Slug for formworks.
	 *
	 * @return array|void|bool Config array for new formworks if it exists. Void if not. False if not allowed
	 */
	public static function create( $name, $slug, $form, $type ) {
		$can = self::can();
		if ( $can ) {
			$name     = sanitize_text_field( $name );
			$slug     = sanitize_title_with_dashes( $slug );
			$form     = sanitize_text_field( $form );
			$type     = sanitize_text_field( $type );
			$item_id  = self::create_unique_id();
			$registry = self::get_registry();

			if ( ! isset( $registry[ $item_id ] ) ) {
				$new = array(
					'id'   => $item_id,
					'name' => $name,
					'slug' => $slug,
					'form' => $form,
					'type' => $type,
				);

				$registry[ $item_id ] = $new;

				self::update( $new, $registry );

				return $new;

			}
		} else {
			return $can;

		}

	}

	/**
	 * Get an individual item by its ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id formworks ID
	 *
	 * @return bool|array formworks config or false if not found.
	 */
	public static function get_single( $id ) {
		$option_name = self::item_option_name( $id );
		$formworks = get_option( $option_name, false );

		// try slug based lookup
		if ( false === $formworks ){
			$registry = self::get_registry();
			foreach ( $registry as $single_id => $single ) {
				if ( $single['slug'] === $id ) {
					$option_name = self::item_option_name( $single_id );
					$formworks = get_option( $option_name, false );
					break;
				}

			}

		}

		/**
		 * Filter a formworks config before returning
		 *
		 * @since 1.0.0
		 *
		 * @param array $formworks The config to be returned
		 * @param string $option_name The name of the option it was stored in.
		 */
		return apply_filters( 'formworks_get_single', $formworks, $option_name );


	}

	/**
	 * Get The entries from a Caldera Form
	 *
	 * @since 1.0.0
	 *
	 * @param string $form_id form ID
	 * @param int $page. Optional. Page number for results. Default is 1
	 * @param int $perpage Optional. Results per page. Default is 1000
	 * @param array $get_fields Optional. Array of fields to get. Default is an empty array, which returns all fields.
	 *
	 * @return array entries returned
	 */
	public static function get_cf_entries( $form_id, $page = 1, $perpage = 1000, $get_fields = array() ) {
		if( ! class_exists( '\\Caldera_Forms' ) ) {
			return;

		}

		$form = \Caldera_Forms::get_form( $form_id );

		if ( isset( $form[ 'ID' ])) {
			$form_id = $form[ 'ID' ];
		}else{
			return;
		}

		global $wpdb;

		$field_labels = array();
		$backup_labels = array();
		$selects = array();

		// get all fieldtype
		$field_types = \Caldera_Forms::get_field_types();


		$fields = array();
		if(!empty($form['fields'])){
			foreach($form['fields'] as $fid=>$field){
				$fields[$field['slug']] = $field;
				$selects[] = "'".$field['slug']."'";
				if( empty( $get_fields ) || in_array( $field['ID'], $get_fields ) ){ 
					$field_labels[$field['slug']] = $field['label'];
				}
				$has_vars = array();
				if( !empty( $form['variables']['types'] ) ){
					$has_vars = $form['variables']['types'];
				}
			}
		}
		if(empty($field_labels)){
			$field_labels = $backup_labels;
		}

		$data = array();

		$filter = null;
		$status = 'active';

		$data['active'] = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(`id`) AS `total` FROM `" . $wpdb->prefix . "cf_form_entries` WHERE `form_id` = %s AND `status` = 'active';", $form_id ) );

		// set current total
		$data['total'] = $data['active'];

		if( !empty( $perpage ) ){
			$data['pages'] = ceil($data['total'] / $perpage );
		}else{
			$data['pages'] = 1;
		}
		if(!empty( $page )){
			$page = abs( $page );
			if($page > $data['pages']){
				$page = $data['pages'];
			}
		}

		$data['current_page'] = $page;
		$gmt_offset = get_option( 'gmt_offset' );
		if($data['total'] > 0){

			$data['form'] = $form_id;

			$data['label'] = $field_labels;
			$offset = ($page - 1) * $perpage;
			$limit = null;
			if( !empty( $perpage ) ){
				$limit = "LIMIT " . $offset . ',' . $perpage;
			}


			$rawdata = $wpdb->get_results($wpdb->prepare("
			SELECT
				`id`,
				`form_id`
			FROM `" . $wpdb->prefix ."cf_form_entries`

			WHERE `form_id` = %s AND `status` = %s ORDER BY `datestamp` DESC " . $limit . ";", $form_id, $status ) );

			if(!empty($rawdata)){

				$ids = array();
				foreach($rawdata as $row){
					$ids[] = $row->id;
				}

				$rawdata = $wpdb->get_results("
				SELECT
					`entry`.`id` as `_entryid`,
					`entry`.`form_id` AS `_form_id`,
					`entry`.`datestamp` AS `_date_submitted`,
					`entry`.`user_id` AS `_user_id`,
					`value`.*

				FROM `" . $wpdb->prefix ."cf_form_entries` AS `entry`
				LEFT JOIN `" . $wpdb->prefix ."cf_form_entry_values` AS `value` ON (`entry`.`id` = `value`.`entry_id`)

				WHERE `entry`.`id` IN (" . implode(',',$ids) . ")
				" . $filter ."
				ORDER BY `entry`.`datestamp` DESC;");


				$data['entry'] = array();
				$dateformat = get_option('date_format');
				$timeformat = get_option('time_format');
				foreach($rawdata as $row){
					if(!empty($row->_user_id)){
						$user = get_userdata( $row->_user_id );
						if(!empty($user)){
							$data['entry']['E' . $row->_entryid]['user']['ID'] = $user->ID;
							$data['entry']['E' . $row->_entryid]['user']['name'] = $user->data->display_name;
							$data['entry']['E' . $row->_entryid]['user']['email'] = $user->data->user_email;
							$data['entry']['E' . $row->_entryid]['user']['avatar'] = get_avatar( $user->ID, 64 );
						}
					}
					$data['entry']['E' . $row->_entryid]['_entry_id'] = $row->_entryid;
					$data['entry']['E' . $row->_entryid]['_date'] = date_i18n( $dateformat.' '.$timeformat, get_date_from_gmt( $row->_date_submitted, 'U'));

					// setup default data array
					if(!isset($data['entry']['E' . $row->_entryid]['data'])){
						if(isset($field_labels)){
							foreach ($field_labels as $slug => $label) {
								// setup labels ordering
								$data['entry']['E' . $row->_entryid]['data'][$slug] = null;
							}
						}
					}

					if(!empty($field_labels[$row->slug])){

						$label = $field_labels[$row->slug];

						// check view handler
						$field = $fields[$row->slug];
						// filter the field to get field data
						$field = apply_filters( 'caldera_forms_render_get_field', $field, $form);
						$field = apply_filters( 'caldera_forms_render_get_field_type-' . $field['type'], $field, $form);
						$field = apply_filters( 'caldera_forms_render_get_field_slug-' . $field['slug'], $field, $form);

						// maybe json?
						$is_json = json_decode( $row->value, ARRAY_A );
						if( !empty( $is_json ) ){
							$row->value = $is_json;
						}

						if( is_string( $row->value ) ){
							$row->value = esc_html( stripslashes_deep( $row->value ) );
						}else{
							$row->value = stripslashes_deep( \Caldera_Forms_Sanitize::sanitize( $row->value ) );
						}

						$row->value = apply_filters( 'caldera_forms_view_field_' . $field['type'], $row->value, $field, $form);


						if(isset($data['entry']['E' . $row->_entryid]['data'][$row->slug])){
							// array based - add another entry
							if(!is_array($data['entry']['E' . $row->_entryid]['data'][$row->slug])){
								$tmp = $data['entry']['E' . $row->_entryid]['data'][$row->slug];
								$data['entry']['E' . $row->_entryid]['data'][$row->slug] = array($tmp);
							}
							$data['entry']['E' . $row->_entryid]['data'][$row->slug][] = $row->value;
						}else{
							$data['entry']['E' . $row->_entryid]['data'][$row->slug] = $row->value;
						}
					}

					if( !empty( $form['variables']['types'] ) ){
						foreach( $form['variables']['types'] as $var_key=>$var_type ){
							if( $var_type == 'entryitem' ){
								$data['label'][$form['variables']['keys'][$var_key]] = ucwords( str_replace( '_', ' ', $form['variables']['keys'][$var_key] ) );
								$data['entry']['E' . $row->_entryid]['data'][$form['variables']['keys'][$var_key]] = \Caldera_Forms::do_magic_tags( $form['variables']['values'][$var_key], $row->_entryid );
							}
						}
					}


				}
			}
		}


		return $data;


	}
	
	/**
	 * Get The entries list
	 *
	 * @since 1.0.0
	 *
	 * @param string $id form ID
	 * @param string $type form type (CF, ninja, gravity etc..)
	 *
	 * @return array entries returned
	 */
	public static function get_entries( $form_id, $page = 1, $limit = 1000, $type = "CF", $fields = array() ) {

		$entries = array();
		if( $type == 'CF' ){
			$entries = self::get_cf_entries( $form_id, $page, $limit, $fields );
		}

		/**
		 * Filter the entry result before returning
		 *
		 * @since 1.0.0
		 *
		 * @param array $entries the returned entries
		 * @param string $form_id The id of the form
		 * @param string $type The type of form
		 */
		$entries = apply_filters( 'formworks_get_entries', $entries, $form_id, $type );
		/**
		 * Filter the entry result before returning
		 *
		 * @since 1.0.0
		 *
		 * @param array $entries the returned entries
		 * @param string $form_id The id of the form
		 * @param string $type The type of form
		 */
		$entries = apply_filters( 'formworks_get_entries-' . $type, $entries, $form_id );

		return $entries;

	}

	/**
	 * Get the registry of formworks.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|bool Array of formworkss or false if not allowed.
	 */

	public static function get_registry() {
		$registry = get_option( self::registry_name(), array() );

		/**
		 * Filter the registry before returning
		 *
		 * @since 1.0.0
		 */

		return apply_filters( 'formworks_get_registry', $registry );


	}

	/**
	 * Update both a single item and the registry
	 *
	 * @since 1.0.0
	 *
	 * @param array $config Single item config.
	 * @param array|bool. Optional. If false, current registry will be used, if is array, that reg
	 */
	public static function update( $config, $update_registry = false ) {
		if ( ! is_array( $update_registry ) ) {
			$update_registry = self::get_registry();
		}

		if( isset( $config['id'] ) && !empty( $update_registry[ $config['id'] ] ) ){
			$update = array(
				'id'	=>	$config['id'],
				'name'	=>	$config['name'],
				'slug'	=>	$config['slug'],
				'form'	=>	$config['form'],
				'type'	=>	$config['type'],

			);

			// add search form to registery
			if( ! empty( $config['search_form'] ) ){
				$updated_registery['search_form'] = $config['search_form'];
			}

			$update_registry[ $config[ 'id' ] ] = $update;

		}

		self::save_registry( $update_registry );

		self::save_single( $config['id'], $config );

	}

	/**
	 * Delete an item and clear it from the registry
	 *
	 * @since 1.0.0
	 *
	 * @param string $id Item ID
	 *
	 * @return bool True on success.
	 */
	public static function delete( $id ) {
		$deleted = delete_option( self::item_option_name( $id ) );
		if ( $deleted ) {
			$registry = self::get_registry();
			if ( isset( $registry[ $id ] ) ) {
				unset( $registry[ $id ] );
				return self::save_registry( $registry );

			}

		}

	}

	/**
	 * Save the registry of items.
	 *
	 * @since 1.0.0
	 *
	 * @param array $registry The registry
	 *
	 * @return bool
	 */
	protected static function save_registry( $registry ) {
		return update_option( self::registry_name(), $registry );

	}

	/**
	 * Save an individual item.
	 *
	 * @param string $id formworks ID
	 * @param array $config formworks config
	 *
	 * @return bool
	 */
	protected static function save_single( $id, $config ) {
		return update_option( self::item_option_name( $id ), $config );

	}



	/**
	 * Get the name to use for an individual item option.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	protected static function item_option_name( $id ) {
		$name = self::$option_name . '_' . $id;
		if ( 50 < strlen( $name ) ) {
			$name = md5( $name );
		}

		return $name;

	}

	/**
	 * Get the name used for the registry option
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 *
	 * @return string
	 */
	protected static function registry_name() {
		return '_' . self::$option_name . '_registry';

	}

	/**
	 * Create unique ID
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 *
	 * @return string
	 */
	protected static function create_unique_id() {
		$slug_parts = explode( '_', 'formworks' );
		$slug = '';

		foreach ( $slug_parts as $slug_part ) {
			$slug .= substr( $slug_part, 0,1 );
		}

		$slug = strtoupper( $slug );

		$item_id = uniqid( $slug ) . rand( 100, 999 );

		return $item_id;

	}

	/**
	 * Generic capability check to use before reading/writing
	 *
	 * @since 1.0.0
	 *
	 * @param string $cap Optional. Capability to check. Defaults to 'manage_options'
	 *
	 * @return bool
	 */
	public static function can( $cap = 'manage_options' ) {
		return current_user_can( $cap );

	}

}
