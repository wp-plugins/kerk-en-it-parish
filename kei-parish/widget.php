<?php
/**
 * Adds Kei_MassTimes_Widget widget.
 */
class Kei_MassTimes_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {

		load_plugin_textdomain( 'kei-parish', false, KEI_LANG);
		parent::__construct(
			'kei_widget_masses', // Base ID
			__( 'Masses', 'kei-parish' ), // Name
			array( 'description' => __( 'Show your masses times', 'kei-parish' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $wpdb;
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		$church_ID = (int)$instance['church_ID'];
		$and = '';
		if($church_ID > 0) {
			$and = 'AND `ID` = ' . $church_ID . '';
		}
		$churches = $wpdb->get_results("SELECT `ID`, `title` FROM `" . $wpdb->prefix . 'kei_church' . "` WHERE `active` = 1 $and ORDER BY `title`;", OBJECT );
		foreach($churches as $church)
		{
			echo $church->title;
			echo '<br />';
		}
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		global $wpdb;
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Masses times', 'kei-parish' );
		$church_ID = ! empty( $instance['church_ID'] ) ? $instance['church_ID'] : 0;
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'church_ID' ); ?>"><?php _e( 'Church:', 'kei-parish' ); ?></label>
		<?php
			echo '<select class="widefat" id="' . $this->get_field_id( 'church_ID' ) . '" name="' . $this->get_field_name( 'church_ID' ) . '">';
			$churches = $wpdb->get_results("SELECT `ID`, `title` FROM `" . $wpdb->prefix . 'kei_church' . "` WHERE `active` = 1 ORDER BY `title`;", OBJECT );
			echo '<option value="0"' . ((int)esc_attr( $church_ID ) == 0 ? ' selected="selected"' : '') . '>' . __( 'All churches:', 'kei-parish' ) . '</option>';

			foreach($churches as $church)
			{
				echo '<option value="' . $church->ID . '"' . ((int)esc_attr( $church_ID ) == $church->ID ? ' selected="selected"' : '') . '>' . $church->title . '</option>';
			}
			echo '</select>';
		?>
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['church_ID'] = ( ! empty( $new_instance['church_ID'] ) ) ? strip_tags( $new_instance['church_ID'] ) : '';
		return $instance;
	}

} // class Kei_MassTimes_Widget

?>