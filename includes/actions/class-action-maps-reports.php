<?php
/**
 * Maps & Reports activity items
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Maps & Reports actions
 *
 * @since 1.0.0
 */
class WP_User_Activity_Type_Maps_Reports extends WP_User_Activity_Type {

	/**
	 * The unique type for this activity
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $object_type = 'map_report';

	/**
	 * Icon of this activity type
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $icon = 'location-alt';

	/**
	 * Add hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Set name
		$this->name = esc_html__( 'Maps & Reports', 'cares-user-activity-extension' );

		// These actions are fired when the mirror BP Doc is created/modified.
		// Save
		new WP_User_Activity_Action( array(
			'type'    => $this,
			'action'  => 'save',
			'name'    => esc_html__( 'Save', 'wp-user-activity' ),
			'message' => esc_html__( '%1$s saved the "%2$s" %3$s %4$s.', 'cares-user-activity-extension' )
		) );

		// Update
		new WP_User_Activity_Action( array(
			'type'    => $this,
			'action'  => 'update',
			'name'    => esc_html__( 'Update', 'wp-user-activity' ),
			'message' => esc_html__( '%1$s edited the "%2$s" %3$s %4$s.', 'cares-user-activity-extension' )
		) );

		// Delete
		new WP_User_Activity_Action( array(
			'type'    => $this,
			'action'  => 'delete',
			'name'    => esc_html__( 'Delete', 'wp-user-activity' ),
			'message' => esc_html__( '%1$s deleted the "%2$s" %3$s %4$s.', 'cares-user-activity-extension' )
		) );

		// Actions
		// Logging map/report save actions by users who aren't logged in. Only logged-in users can save maps.
		// add_action( 'wp_ajax_nopriv_cc-update-maps-reports', array( $this, 'created_edited_deleted_item' ) );
		// Logging map/report save actions by users who are logged in.
		add_action( 'wp_ajax_cc-update-maps-reports',        array( $this, 'created_edited_deleted_item' ) );

		// Setup callbacks
		parent::__construct();
	}

	/** Callbacks for Dashboard Activity List *********************************/

	/**
	 * Callback for returning human-readable output in the Dashboard activity list.
	 *
	 * @since 1.0.0
	 *
	 * @param  object  $post
	 * @param  array   $meta
	 *
	 * @return string
	 */
	public function save_action_callback( $post, $meta = array() ) {
		return sprintf(
			$this->get_activity_action( 'save' ),
			$this->get_activity_author_link( $post ),
			$meta->object_name,
			ucfirst( $meta->object_subtype ),
			$this->get_how_long_ago( $post )
		);
	}

	/**
	 * Callback for returning human-readable output in the Dashboard activity list.
	 *
	 * @since 1.0.0
	 *
	 * @param  object  $post
	 * @param  array   $meta
	 *
	 * @return string
	 */
	public function update_action_callback( $post, $meta = array() ) {
		return sprintf(
			$this->get_activity_action( 'update' ),
			$this->get_activity_author_link( $post ),
			$meta->object_name,
			ucfirst( $meta->object_subtype ),
			$this->get_how_long_ago( $post )
		);
	}

	/**
	 * Callback for returning human-readable output in the Dashboard activity list.
	 *
	 * @since 1.0.0
	 *
	 * @param  object  $post
	 * @param  array   $meta
	 *
	 * @return string
	 */
	public function delete_action_callback( $post, $meta = array() ) {
		return sprintf(
			$this->get_activity_action( 'delete' ),
			$this->get_activity_author_link( $post ),
			$meta->object_name,
			ucfirst( $meta->object_subtype ),
			$this->get_how_long_ago( $post )
		);
	}

	/** Logging ***************************************************************/

	/**
	 * Map or report saved, edited or deleted
	 * This is fired when Yan sends the info to create the mirror BP doc.
	 *
	 * @since 1.0.0
	 *
	 * @param  int  $post_id
	 */
	public function saved_edited_deleted_map_report( $item_id = 0, $item_type = 'map', $action = 'save' ) {

		// Fetch details from the AJAX request.
		$user_id       = ! empty( $_REQUEST['user_id'] )       ? (int) $_REQUEST['user_id'] : get_current_user_id();
		$activity_type = ! empty( $_REQUEST['activity_type'] ) ? $_REQUEST['activity_type'] : '';
		$item_id       = ! empty( $_REQUEST['item_id'] )       ? (int) $_REQUEST['item_id'] : $item_id;

		// Make sure the request is a type we recognize, if it is, get the details
		switch ( $activity_type ) {
		  case 'map_updated':
  				$item_type = 'map';
				$action = 'update';
				break;
		  case 'map_deleted':
				$item_type = 'map';
				$action = 'delete';
				break;
		  case 'map_featured':
				$item_type = 'map';
				$action = 'featured_status_change';
				// We're not logging this action.
				return;
				break;
		  case 'report_updated':
		  		$item_type = 'report';
				$action = 'update';
				break;
		  case 'report_deleted':
				$item_type = 'report';
				$action = 'delete';
				break;
		  case 'report_featured':
		  		$item_type = 'report';
				$action = 'featured_status_change';
				// We're not logging this action.
				return;
				break;
		  case 'area_updated':
		  		$item_type = 'area';
				$action = 'update';
				break;
		  case 'area_deleted':
				$item_type = 'area';
				$action = 'delete';
				break;
		  case 'area_featured':
		  		$item_type = 'area';
				$action = 'featured_status_change';
				// We're not logging this action.
				return;
				break;
		  default:
		  		// When used functionally, we'll use the passed variables.
				break;
		}

		$item = cares_maps_json_svc_make_request( $item_type, false, false, false, $item_id, false );

		// Bail if no item
		if ( ! $item ) {
			return;
		}

		if ( 'update' == $action ) {
			// Is this a new item or has an existing item been updated?
			if ( ! $this->check_activity_item_exists( $item_type, $item_id ) ) {
				$action = 'save';
			}
		}

		$item_title = ! empty( $item['title'] ) ? $item['title'] : ucfirst( $item_type ) . " ID: {$item_id}";

		$activity_args = array(
			'user_id'        => $item['owner'],
			'object_type'    => $this->object_type,
			'object_subtype' => $item_type,
			'object_name'    => $item_title,
			'object_id'      => $item_id,
			'action'         => $action
		);

		// Record the 'reporttype' parameter to meta.
		if ( ! empty( $item['reporttype'] ) ) {
			$activity_args['reporttype'] = sanitize_text_field( $item['reporttype'] );
		}

		// Insert activity
		$activity_id = wp_insert_user_activity( $activity_args );

		// On successful save, add more details via meta/taxonomy.
		if ( $activity_id ) {
			// We want to note group association if an item is group-associated.
			// 'sharing' can be "personal" "public" or comma-separated group IDs
			if ( ! empty( $item['sharing'] ) && $item['sharing'] != 'personal' && $item['sharing'] != 'public' ) {
				$group_ids = explode( ',', $item['sharing'] );
				foreach ( $group_ids as $group_id ) {
					$group_id = absint( $group_id );
					if ( $group_id > 0 ) {
						add_post_meta( $activity_id, 'wp_user_activity_group_association', $group_id );
					}
				}
			}
		}
	}

	/**
	 * Check whether an activity item for a specific map or report already exists.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $item_type "map" or "report"
	 * @param  int    $item_id   The ID of the map or report (remote ID)
	 *
	 * @return int|bool ID of the found activity or false if nothing's found.
	 */
	public function check_activity_item_exists( $item_type, $item_id ) {
		$args = array(
			'post_type'   => 'activity',
			'fields'      => 'ids',
			'meta_query'  => array(
				'relation' => 'AND',
				array(
					'key'     => 'wp_user_activity_object_type',
					'value'   => $this->object_type,
					'compare' => 'LIKE',
				),
				array(
					'key'     => 'wp_user_activity_object_subtype',
					'value'   => $item_type,
					'compare' => 'LIKE',
				),
				array(
					'key'     => 'wp_user_activity_object_id',
					'value'   => $item_id,
					'compare' => '=',
				),
			),
		);

		$activity = new WP_Query( $args );
		if ( ! empty( $activity->posts ) && is_array( $activity->posts ) )
			$id = current( $activity->posts );
		else {
			$id = false;
		}
		return $id;
	}

}
