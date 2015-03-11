<?php

if (!class_exists('WP_List_Table')) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}


class KEI_MassTypes_Table extends WP_List_Table {


	public $table = '';


	function __construct() {
		global $status, $page, $wpdb;

		load_plugin_textdomain( 'kei-parish', false, KEI_LANG);

		//Set parent defaults
		parent::__construct(
			array(
				'singular'	=> 'masstype',
				'plural'	=> 'masstypes',
				'ajax'		=> false
			)
		);
		$this->table_name = $wpdb->prefix . 'kei_massesType';
	}

	function column_default($item, $column_name) {
		switch ($column_name) {
			case 'date':
				return __date($item->$column_name);
			default:
				return print_r($item, true);
		}
	}

	function column_title($item) {
		//Build row actions
		$active = ($item->active == 1);
		$actions = array (
			'edit'  => sprintf('<a href="?page=%s&action=%s&masstype=%s">%s</a>', $_REQUEST['page'], 'edit', $item->ID, __('Edit', 'kei-parish')),
			($active ? 'deactivate' : 'activate') => sprintf('<a href="?page=%s&action=%s&masstype=%s">%s</a>', $_REQUEST['page'], ($active ? 'deactivate' : 'activate'), $item->ID, ($active ? __('Deactivate', 'kei-parish') : __('Activate', 'kei-parish'))),
			'delete' => sprintf('<a href="?page=%s&action=%s&masstype=%s">%s</a>', $_REQUEST['page'], 'delete', $item->ID, __('Delete', 'kei-parish')),
		);

		//Return the title contents
		return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
			/*$1%s*/ $item->title,
			/*$2%s*/ $item->ID,
			/*$3%s*/ $this->row_actions($actions)
		);
	}

	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],
			/*$2%s*/ $item->ID
		);
	}

	function get_columns() {
		$columns = array (
			'cb'		=> '<input type="checkbox" />',
			'title'		=> __('Mass type', 'kei-parish'),
			'date'		=> __('Date', 'kei-parish')
		);
		return $columns;
	}


	function get_sortable_columns() {
		$sortable_columns = array (
			'title'		=> array('title', false),  //true means it's already sorted
			'date'		=> array('date', false)
		);
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array (
			'activate'		=> __('Activate', 'kei-parish'),
			'deactivate'	=> __('Deactivate', 'kei-parish'),
			'delete'		=> __('Delete', 'kei-parish')
		);
		return $actions;
	}

	function delete_item($id) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM `" . $this->table_name . "` WHERE `ID` = %d;", $id ) );
	}

	function de_activate_item($id, $val) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "UPDATE `" . $this->table_name . "` SET `active` = %d, `updatedate` = NOW() WHERE `ID` = %d;", $val, $id ) );
	}





	/** ************************************************************************
	 * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
	 * For this example package, we will handle it in the class to keep things
	 * clean and organized.
	 *
	 * @see $this->prepare_items()
	 **************************************************************************/
	function process_bulk_action() {
		global $wpdb;

		$ids    = isset( $_GET[$this->_args['singular']] ) ? $_GET[$this->_args['singular']] : false;
		$action = $this->current_action();

		if ( ! is_array( $ids ) ) :
			$ids = array( $ids );
		endif;

		if( empty( $action ) ) :
			return;
		endif;

		foreach ( $ids as $id ) {
			$id = absint( $id );
			//Detect when a bulk action is being triggered...
			if ( 'delete' === $action ) :
	            $this->delete_item($id);
	        elseif ( 'activate' === $action ) :
	            $this->de_activate_item($id, 1);
	        elseif ( 'deactivate' === $action ) :
	            $this->de_activate_item($id, 0);
			endif;
		}
	}


	/** ************************************************************************
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here...
	 *
	 * @global WPDB $wpdb
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/
	function prepare_items() {
		global $wpdb; //This is used only if making any database queries

		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = 5;


		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns	= $this->get_columns();
		$hidden		= array();
		$sortable	= $this->get_sortable_columns();


		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array($columns, $hidden, $sortable);


		/**
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		$this->process_bulk_action();


		$data = $wpdb->get_results("SELECT ID, `title`, IFNULL(`updatedate`,`insertdate`) AS date, `active` FROM `" . $this->table_name . "`");


		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently
		 * looking at. We'll need this later, so you should always include it in
		 * your own package classes.
		 */
		$current_page = $this->get_pagenum();

		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array.
		 * In real-world use, this would be the total number of items in your database,
		 * without filtering. We'll need this later, so you should always include it
		 * in your own package classes.
		 */
		$total_items = count($data);


		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to
		 */
		$data = array_slice($data, (($current_page-1)*$per_page), $per_page);



		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;


		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args(
			array(
				'total_items'	=> $total_items,
				'per_page'		=> $per_page,
				'total_pages'	=> ceil($total_items/$per_page)
			) );
	}
}

if (!function_exists('kei_masstype_render_list_page')) {

	function kei_masstype_render_list_page() {
		global $wpdb;
		$MassTypeListTable = new KEI_MassTypes_Table();

		$id = 0;
		if(isset($_GET[$MassTypeListTable->_args['singular']]) && isset($_GET[$MassTypeListTable->_args['singular']]) && is_numeric($_GET[$MassTypeListTable->_args['singular']])) :
			$id = (int)$_GET[$MassTypeListTable->_args['singular']];
		endif;

		if($MassTypeListTable->current_action() == 'add' || $MassTypeListTable->current_action() == 'edit') :
			?>
			<div class="wrap">
					<h2><?php _e('Edit mass type', 'kei-parish'); ?> <?php if($MassTypeListTable->current_action() == 'edit') { echo '<a href="admin.php?page=' . $_REQUEST['page'] . '&action=add" class="add-new-h2">' . __('New mass type', 'kei-parish') . '</a>'; } ?></h2>
			<?php
			$data = null;
			if ($_SERVER['REQUEST_METHOD'] === 'POST') :
				if(isset($_POST['kei-reset-masstype'])) :
					wp_redirect(admin_url('admin.php?page=' . $_REQUEST['page']));
				elseif(isset($_POST['kei-save-masstype'])) :
					$id = kei_post_val('id');
					$title = kei_post_val('title');
					$activeWidget = kei_post_isChecked('activeWidget');
					if($title === null) :
						?>
						<div id="message" class="error">
					        <p><strong><?php if($id === null) { _e('Mass type not added.', 'kei-parish'); } else  { _e('Mass type not changed.', 'kei-parish'); } ?></strong></p>
					    </div>
						<?php
					elseif($id === null) :
						$wpdb->query($wpdb->prepare("INSERT INTO `" . $MassTypeListTable->table_name . "` (`title`, `activeWidget`, `insertdate`) VALUES (%s, %d, NOW())", $title, $activeWidget));
						$id = $wpdb->insert_id;
						?>
						<div id="message" class="updated">
					        <p><strong><?php _e('Mass type is added.', 'kei-parish') ?></strong></p>
					    </div>
						<?php
					else :
						$wpdb->query($wpdb->prepare("UPDATE `" . $MassTypeListTable->table_name . "` SET `title` = %s, `activeWidget` = %d, `updatedate` = NOW() WHERE `ID` = %d", $title, $activeWidget, $id));
						?>
						<div id="message" class="updated">
					        <p><strong><?php _e('Mass type is changed.', 'kei-parish') ?></strong></p>
					    </div>
						<?php
					endif;
					$data = array(0 => (object) array('ID' => $id, 'title' => $title, 'activeWidget' => $activeWidget) );
				endif;
			else :
				if($id != 0) :
					$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . $MassTypeListTable->table_name . "` WHERE ID = %d LIMIT 0,1", $id ), OBJECT );
				endif;
			endif;
			if($data === null) :
				$data = array(0 => (object) array('ID' => '', 'title' => '', 'activeWidget' => '1') );
			endif;
			?>
				<form id="masstype-filter" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
					<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
					<input type="hidden" name="id" value="<?php echo $data[0]->ID; ?>" />
					<div id="naw">
						<div>
							<label for="title">
								<?php _e('Mass type', 'kei-parish'); ?>:
							</label>
							<input type="text" name="title" size="30" value="<?php echo $data[0]->title; ?>" id="title" spellcheck="true" autocomplete="off" class="<?php echo ($data[0]->title === null ? 'error' : ''); ?>">
						</div>
						<div>
							<label for="activeWidget">
								<?php _e('activeWidget', 'kei-parish'); ?>:
							</label>
							<input type="checkbox" name="activeWidget" size="30" value="<?php echo $data[0]->activeWidget; ?>" <?php if($data[0]->activeWidget == 1) { echo 'checked="checked"'; } ?> id="activeWidget" spellcheck="true" autocomplete="off">
						</div>
					</div>
					<div style="clear:both;">

						<?php
							$other_attributes = array( 'id' => 'masstype' );
							submit_button( (empty($data[0]->ID) ? __('Save', 'kei-parish') : __('Save Changes', 'kei-parish') ), 'primary', 'kei-save-masstype', false, $other_attributes );
							echo '&nbsp;&nbsp;';
							submit_button( __('Reset', 'kei-parish'), 'secondary', 'kei-reset-masstype', false);
						?>
					</div>
				</form>
			</div>
		<?php
		else :
		//Fetch, prepare, sort, and filter our data...
		$MassTypeListTable->prepare_items();

	?>
		<div class="wrap">

			<h2><?php _e('Mass Types', 'kei-parish') ?> <a href="admin.php?page=<?php echo $_REQUEST['page']; ?>&action=add" class="add-new-h2"><?php _e('New mass type', 'kei-parish') ?></a></h2>

			<div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
				<p></p>
			</div>

			<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			<form id="masstype-filter" method="get">
				<!-- For plugins, we also need to ensure that the form posts back to our current page -->
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<!-- Now we can render the completed list table -->
				<?php $MassTypeListTable->display() ?>
			</form>

		</div>
		<?php

		endif;
	}
}