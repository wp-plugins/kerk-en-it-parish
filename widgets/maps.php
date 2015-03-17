<?php
/**
 * Adds Kei_MassTimes_Widget widget.
 */
class Kei_Maps_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {

		load_plugin_textdomain( 'kei-parish', false, KEI_LANG);
		$widget_ops = array('classname' => 'kei_widget_maps', 'description' => __( 'This widget will show all churches in a Google Maps map', 'kei-parish' ));
        parent::__construct(
        	'kei_widget_maps', // Base ID
        	__( 'Google Maps', 'kei-parish' ), // Name
        	$widget_ops // Args
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
		if ( ! empty( $instance['title'] ) ) :
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		endif;
		$subtitle = '';
		if ( ! empty( $instance['subtitle'] ) ) :
			$subtitle = sprintf(' subtitle="%s"', $instance['subtitle']);
		endif;
		if(isset($instance['church_ID']) && !empty($instance['church_ID']) && is_numeric($instance['church_ID'])) :
			echo do_shortcode( '[parish-maps church_id="' . $instance['church_ID'] . '"' . $subtitle . ' height="' . $instance['height'] . '"]' );
		else :
			echo do_shortcode( '[parish-maps' . $subtitle . ' height="' . $instance['height'] . '"]' );
		endif;

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
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Map', 'kei-parish' );
		$subtitle = ! empty( $instance['subtitle'] ) ? $instance['subtitle'] : '';
		$church_ID = ! empty( $instance['church_ID'] ) ? $instance['church_ID'] : 0;
		$height= ! empty( $instance['height'] ) ? $instance['height'] : '250px';
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'subtitle' ); ?>"><?php _e( 'Subtitle:', 'kei-parish' ); ?></label>
		<textarea class="widefat" rows="5" id="<?php echo $this->get_field_id( 'subtitle' ); ?>" name="<?php echo $this->get_field_name( 'subtitle' ); ?>"><?php echo esc_attr( $subtitle ); ?></textarea>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'church_ID' ); ?>"><?php _e( 'Church:', 'kei-parish' ); ?></label>
		<?php
			echo '<select class="widefat" id="' . $this->get_field_id( 'church_ID' ) . '" name="' . $this->get_field_name( 'church_ID' ) . '">';
			$churches = $wpdb->get_results("SELECT `ID`, `title` FROM `" . $wpdb->prefix . 'kei_church' . "` WHERE `active` = 1 ORDER BY `title`;", OBJECT );
			echo '<option value="0"' . ((int)esc_attr( $church_ID ) == 0 ? ' selected="selected"' : '') . '>' . __( 'All churches:', 'kei-parish' ) . '</option>';

			foreach($churches as $church) {
				echo '<option value="' . $church->ID . '"' . ((int)esc_attr( $church_ID ) == $church->ID ? ' selected="selected"' : '') . '>' . $church->title . '</option>';
			}
			echo '</select>';
		?>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Height:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" value="<?php echo esc_attr( $height ); ?>">
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
		$instance['subtitle'] = ( ! empty( $new_instance['subtitle'] ) ) ? strip_tags( $new_instance['subtitle'] ) : '';
		$instance['church_ID'] = ( ! empty( $new_instance['church_ID'] ) ) ? strip_tags( $new_instance['church_ID'] ) : '';
		$instance['height'] = ( ! empty( $new_instance['height'] ) ) ? strip_tags( $new_instance['height'] ) : '';
		return $instance;
	}

} // class Kei_MassTimes_Widget

?>