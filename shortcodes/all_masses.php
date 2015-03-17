<?php
if(!function_exists('kei_all_masses_handler')) :
	function kei_all_masses_handler( $atts, $content = null ) {
		load_plugin_textdomain( 'kei-parish', false, KEI_LANG);
		global $wpdb;
	    $atts_extended = shortcode_atts( array(
	        'church_id' => '',
	        'subtitle' => '',
	    ), $atts );
	    extract($atts_extended);

		$single_church = (isset($church_id) && !empty($church_id) && is_numeric($church_id) && $church_id > 0);
		$and = '';
		if($single_church) :
			$and = 'AND `ID` = ' . $church_id . '';
		endif;
		$churches = $wpdb->get_results("SELECT `ID`, `title` FROM `" . $wpdb->prefix . 'kei_church' . "` WHERE `active` = 1 $and ORDER BY `title`;", OBJECT );

		$output = '';
		if(!$single_church && $content != null && !empty($content)) :
			$output .= sprintf('<p>%s</p>', $content);
		endif;
		if(!$single_church && !empty($subtitle)) :
			$output .= sprintf('<p>%s</p>', $subtitle);
		endif;
		foreach($churches as $church)
		{
			$masses = $wpdb->get_results("SELECT `title`, `dayOfWeek`, `hour`, `minute` FROM `" . $wpdb->prefix . 'kei_masses' . "` INNER JOIN `" . $wpdb->prefix . 'kei_massesType' . "` ON `" . $wpdb->prefix . 'kei_masses' . "`.`massType_ID` = `" . $wpdb->prefix . 'kei_massesType' . "`.`ID` WHERE `" . $wpdb->prefix . 'kei_masses' . "`.`active` = 1 AND church_ID = " . $church->ID . " ORDER BY `dayOfWeek` ASC, `hour` ASC, `minute` ASC;", OBJECT );
			if(count($masses) > 0) :
				$output .= sprintf('<strong>%s</strong>', $church->title);
				if($single_church && $content != null && !empty($content)) :
					$output .= sprintf('<p>%s</p>', $content);
				endif;
				if($single_church && !empty($subtitle)) :
					$output .= sprintf('<p>%s</p>', $subtitle);
				endif;
				$dayOfWeek = 0;
				$output .= '<p>';
				foreach($masses as $mass)
				{
					if($dayOfWeek !== $mass->dayOfWeek) :
						if($dayOfWeek !== 0) :
							$output .= '</p><p>';
						endif;
						$output .= sprintf('%s %s<br />', __('Every', 'kei-parish'), getDayOfWeek($mass->dayOfWeek));
					endif;
					$output .= sprintf('<em>%s %s %s</em><br />', $mass->title, __('at', 'kei-parish'), getTimeOfDayOfWeek($mass->hour, $mass->minute));
					$dayOfWeek = $mass->dayOfWeek;
				}
				$output .= '</p>';
			endif;
		}
		if(!empty($output)) :
			return $output;
		endif;
	}
endif;
remove_shortcode( 'parish-all-masses' );
add_shortcode( 'parish-all-masses', 'kei_all_masses_handler' );
?>