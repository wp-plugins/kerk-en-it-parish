<?php

if (!class_exists('WP_List_Table')) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}


class KEI_Church_Table extends WP_List_Table {


	public $table = '';


	function __construct() {
		global $status, $page, $wpdb;

		load_plugin_textdomain( 'kei-parish', false, KEI_LANG);

		//Set parent defaults
		parent::__construct(
			array(
				'singular'	=> 'church',
				'plural'	=> 'churches',
				'ajax'		=> false
			)
		);
		$this->table_name = $wpdb->prefix . 'kei_church';
	}

	function column_default($item, $column_name) {
		switch ($column_name) {
			case 'address':
				return str_replace(', , ', __('No address found', 'kei-parish'), $item->$column_name);
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
			'edit'  => sprintf('<a href="?page=%s&action=%s&church=%s">%s</a>', $_REQUEST['page'], 'edit', $item->ID, __('Edit', 'kei-parish')),
			($active ? 'deactivate' : 'activate') => sprintf('<a href="?page=%s&action=%s&church=%s">%s</a>', $_REQUEST['page'], ($active ? 'deactivate' : 'activate'), $item->ID, ($active ? __('Deactivate', 'kei-parish') : __('Activate', 'kei-parish'))),
			'delete' => sprintf('<a href="?page=%s&action=%s&church=%s">%s</a>', $_REQUEST['page'], 'delete', $item->ID, __('Delete', 'kei-parish')),
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
			'title'		=> __('Church name', 'kei-parish'),
			'address'	=> __('Address', 'kei-parish'),
			'date'		=> __('Date', 'kei-parish')
		);
		return $columns;
	}


	function get_sortable_columns() {
		$sortable_columns = array (
			'title'		=> array('title', false),  //true means it's already sorted
			'address'	=> array('address', false),
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


		$sql = "SELECT ID, `title`, CONCAT(`address`, ', ',`zipcode`, ', ',`city`) AS `address`, IFNULL(`updatedate`,`insertdate`) AS date, `active` FROM `" . $this->table_name . "`";
		if(isset($_REQUEST['orderby']) && isset($_REQUEST['order'])) :
			$sql .= sprintf(' ORDER BY `%s` %s', $_REQUEST['orderby'], $_REQUEST['order']);
		endif;
		$data = $wpdb->get_results($sql);


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

if (!function_exists('kei_church_render_list_page')) {

	function kei_church_render_list_page() {
		global $wpdb;
		$ChurchListTable = new KEI_Church_Table();

		$id = 0;
		if(isset($_GET[$ChurchListTable->_args['singular']]) && isset($_GET[$ChurchListTable->_args['singular']]) && is_numeric($_GET[$ChurchListTable->_args['singular']])) :
			$id = (int)$_GET[$ChurchListTable->_args['singular']];
		endif;

		if($ChurchListTable->current_action() == 'add' || $ChurchListTable->current_action() == 'edit') :
			?>
			<div class="wrap">
					<h2><?php if($ChurchListTable->current_action() == 'edit') { _e('Edit church', 'kei-parish'); echo '<a href="admin.php?page=' . $_REQUEST['page'] . '&action=add" class="add-new-h2">' . __('New church', 'kei-parish') . '</a>'; } else { _e('Add church', 'kei-parish'); } ?></h2>
			<?php
			$data = null;
			if ($_SERVER['REQUEST_METHOD'] === 'POST') :
				if(isset($_POST['kei-reset-church'])) :
					wp_redirect(admin_url('admin.php?page=' . $_REQUEST['page']));
				elseif(isset($_POST['kei-save-church'])) :
					$id = kei_post_val('id');
					$title = kei_post_val('title');
					$address = kei_post_val('address');
					$zipcode = kei_post_val('zipcode');
					$city = kei_post_val('city');
					$diocese = kei_post_val('diocese');
					$country = kei_post_val('country');
					$latitude = kei_post_val('lat');
					$longitude = kei_post_val('long');
					$email = kei_post_val('email');
					$phone = kei_post_val('phone');
					if($title === null) :
						?>
						<div id="message" class="error">
					        <p><strong><?php if($id === null) { _e('Church not added.', 'kei-parish'); } else  { _e('Church not changed.', 'kei-parish'); } ?></strong></p>
					    </div>
						<?php
					elseif($id === null) :
						$wpdb->query($wpdb->prepare("INSERT INTO `" . $ChurchListTable->table_name . "` (`title`, `address`, `zipcode`, `city`, `diocese`, `country`, `latitude`, `longitude`, `email`, `phone`, `insertdate`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW())", $title, $address, $zipcode, $city, $diocese, $country, $latitude, $longitude, $email, $phone));
						$id = $wpdb->insert_id;
						?>
						<div id="message" class="updated">
					        <p><strong><?php _e('Church is added.', 'kei-parish') ?></strong></p>
					    </div>
						<?php
					else :
						$wpdb->query($wpdb->prepare("UPDATE `" . $ChurchListTable->table_name . "` SET `title` = %s, `address` = %s, `zipcode` = %s, `city` = %s, `diocese` = %s, `country` = %s, `latitude` = %s, `longitude` = %s, `email` = %s, `phone` = %s, `updatedate` = NOW() WHERE `ID` = %d", $title, $address, $zipcode, $city, $diocese, $country, $latitude, $longitude, $email, $phone, $id));
						?>
						<div id="message" class="updated">
					        <p><strong><?php _e('Church is changed.', 'kei-parish') ?></strong></p>
					    </div>
						<?php
					endif;
					$data = array(0 => (object) array('ID' => $id, 'title' => $title, 'address' => $address, 'zipcode' => $zipcode, 'city' => $city, 'diocese' => $diocese, 'country' => $country, 'latitude' => $latitude, 'longitude' => $longitude, 'email' => $email, 'phone' => $phone) );
				endif;
			else :
				if($id != 0) :
					$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . $ChurchListTable->table_name . "` WHERE ID = %d LIMIT 0,1", $id ), OBJECT );
				endif;
			endif;
			if($data === null) :
				$data = array(0 => (object) array('ID' => '', 'title' => '', 'address' => '', 'zipcode' => '', 'city' => '', 'diocese' => '', 'country' => '', 'latitude' => '', 'longitude' => '', 'email' => '', 'phone' => '') );
			endif;
			?>
				<form id="church-filter" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
					<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
					<input type="hidden" name="id" value="<?php echo $data[0]->ID; ?>" />
					<input type="hidden" name="gAddress" id="gAddress" value="" />
					<div id="naw">
						<div>
							<label for="title">
								<?php _e('Church', 'kei-parish'); ?>:
							</label>
							<input type="text" name="title" size="30" value="<?php echo $data[0]->title; ?>" id="title" spellcheck="true" autocomplete="off" class="<?php echo ($data[0]->title === null ? 'error' : ''); ?>">
						</div>
						<div>
							<label for="address">
								<?php _e('Address', 'kei-parish'); ?>:
							</label>
							<input type="text" name="address" size="30" value="<?php echo $data[0]->address; ?>" id="address" spellcheck="true" autocomplete="off">
						</div>
						<div>
							<label for="zipcode">
								<?php _e('Zipcode', 'kei-parish'); ?>:
							</label>
							<input type="text" name="zipcode" size="30" value="<?php echo $data[0]->zipcode; ?>" id="zipcode" spellcheck="true" autocomplete="off" style="width:100px;">
							<label for="city" style="margin-left:10px;float:left;clear:none;width:50px;">
								<?php _e('City', 'kei-parish'); ?>:
							</label>
							<input type="text" name="city" size="30" value="<?php echo $data[0]->city; ?>" id="city" spellcheck="true" autocomplete="off" style="width:240px;float:left;clear:none;">
						</div>
						<div>
							<label for="diocese">
								<?php _e('Diocese', 'kei-parish'); ?>:
							</label>
							<?php
								$like = null;
								if(get_tld() == '.nl') :
									$like == 'nl-NL';
								elseif(get_tld() == '.be') :
									$like == 'nl-BE';
								endif;
								if($like === null) :
									switch(get_bloginfo('language'))
									{
										case 'nl-NL':
											$like = 'nl';
											break;
										case 'nl-BE':
											$like = 'nl-BE';
											break;
										default:
											$like = null;
											break;
									}
								endif;
								if($like === null) :
									echo '<input type="text" name="diocese" size="30" value="' . $data[0]->diocese . '" id="diocese" spellcheck="true" autocomplete="on">';
								else :
									echo '<select id="diocese" name="diocese" style="width:400px;">';
										$dioceses = $wpdb->get_results("SELECT title FROM `" . $wpdb->prefix . 'kei_diocese' . "` WHERE i18n LIKE '%$like%' ORDER BY ID", OBJECT );
										foreach($dioceses as $diocese)
										{
											echo '<option value="' . $diocese->title . '"' . ($data[0]->diocese == $diocese->title ? ' selected="selected"' : '') . '>' . $diocese->title . '</option>';
										}
									echo '</select>';
								endif;
							?>
						</div>
						<div>
							<label for="country">
								<?php _e('Country', 'kei-parish'); ?>:
							</label>
							<select id="country" name="country" style="width:400px;">
								<option value="<?php _e('Netherlands', 'kei-parish'); ?>"><?php _e('Netherlands', 'kei-parish'); ?></option>
							</select>
						</div>
						<div>
							<p style="margin-left:77px;">
							<a href="#" class="button button-primary kei-get-google-coordinates" title="" style="width:397px;"><?php _e('Enter Address, then fetch coordinates', 'kei-parish'); ?></a>
						    </p>
						    <label for="lat"><?php _e('Latitude', 'kei-parish'); ?>:</label>
						    <input type="text" class="" value="<?php echo $data[0]->latitude; ?>" id="lat" name="lat" style="width:165px;">
						    <label for="long" style="margin-left:10px;float:left;clear:none;width:60px;"><?php _e('Longitude', 'kei-parish'); ?>:</label>
						    <input type="text" class="" value="<?php echo $data[0]->longitude; ?>" id="long" name="long" style="width:165px;float:left;clear:none;">

						</label>
						</div>
						<div>
							<label for="phone">
								<?php _e('Phone', 'kei-parish'); ?>:
							</label>
							<input type="text" name="phone" size="30" value="<?php echo $data[0]->phone; ?>" id="phone" spellcheck="true" autocomplete="on">
						</div>
						<div>
							<label for="email">
								<?php _e('Email', 'kei-parish'); ?>:
							</label>
							<input type="text" name="email" size="30" value="<?php echo $data[0]->email; ?>" id="email" spellcheck="true" autocomplete="on">
						</div>
					</div>
					<div id="googleMaps">
						<?php
							if(empty($data[0]->latitude) && empty($data[0]->longitude)) :
								echo '<img id="mapsImg" src="https://maps.googleapis.com/maps/api/staticmap?center=52.1655928,5.047882&zoom=7&size=400x460&maptype=roadmap&language=nl" />';
							else :
								echo '<img id="mapsImg" src="https://maps.googleapis.com/maps/api/staticmap?center=' . urlencode($data[0]->address . ', ' . $data[0]->zipcode . ', ' . $data[0]->city) . '&zoom=14&size=400x460&maptype=roadmap&markers=color:red%7Clabel:A%7C' . $data[0]->latitude . ',' . $data[0]->longitude . '&language=nl" />';
							endif;
						?>

					</div>
					<div style="clear:both;">

						<?php
							$other_attributes = array( 'id' => 'church' );
							submit_button( (empty($data[0]->ID) ? __('Save', 'kei-parish') : __('Save Changes', 'kei-parish') ), 'primary', 'kei-save-church', false, $other_attributes );
							echo '&nbsp;&nbsp;';
							submit_button( __('Reset', 'kei-parish'), 'secondary', 'kei-reset-church', false);
						?>
					</div>
				</form>
			</div>
		<?php
		else :
		//Fetch, prepare, sort, and filter our data...
		$ChurchListTable->prepare_items();

	?>
		<div class="wrap">

			<h2><?php echo esc_html( get_admin_page_title() ); ?> <a href="admin.php?page=<?php echo $_REQUEST['page']; ?>&action=add" class="add-new-h2"><?php _e('New church', 'kei-parish') ?></a></h2>

			<div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
				<p></p>
			</div>

			<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			<form id="church-filter" method="get">
				<!-- For plugins, we also need to ensure that the form posts back to our current page -->
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<!-- Now we can render the completed list table -->
				<?php $ChurchListTable->display() ?>
			</form>

		</div>
		<?php

		endif;
	}
}