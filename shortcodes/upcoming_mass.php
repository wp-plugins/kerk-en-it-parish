<?php
if(!function_exists('kei_upcoming_masses_handler')) :
	function kei_upcoming_masses_handler( $atts, $content = null ) {
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
			$sql = "SELECT `title`, `dayOfWeek`, `hour`, `minute` FROM `" . $wpdb->prefix . 'kei_masses' . "` INNER JOIN `" . $wpdb->prefix . 'kei_massesType' . "` ON `" . $wpdb->prefix . 'kei_masses' . "`.`massType_ID` = `" . $wpdb->prefix . 'kei_massesType' . "`.`ID` WHERE `" . $wpdb->prefix . 'kei_masses' . "`.`active` = 1 AND church_ID = " . $church->ID . " AND (dayOfWeek = 6 OR dayOfWeek = 7) AND activeWidget = 1 ORDER BY `dayOfWeek` ASC, `hour` ASC, `minute` ASC;";
			$masses = $wpdb->get_results($sql, OBJECT );

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
				$day = '';
				foreach($masses as $mass)
				{
					if(($mass->dayOfWeek == (int)date('N')) && ($mass->hour == (int)date('H')) && ($mass->minute < (int)date('i'))) :
						continue;
					elseif($mass->dayOfWeek == (int)date('N') && ($mass->hour <= (int)date('H'))) :
						continue;
					else :
						if($dayOfWeek !== $mass->dayOfWeek) :
							if($dayOfWeek !== 0) :
								$day .= '</p><p>';
							endif;
							$day .= sprintf('%s %s<br />', ($mass->dayOfWeek == (int)date('N') ? __('Today', 'kei-parish') : __('Upcoming', 'kei-parish')), getDayOfWeek($mass->dayOfWeek));
						endif;
						$day .= sprintf('<em>%s %s %s</em><br />', $mass->title, __('at', 'kei-parish'), getTimeOfDayOfWeek($mass->hour, $mass->minute));
						$dayOfWeek = $mass->dayOfWeek;
						break;
					endif;
				}
				$output .= $day;
				$output .= '</p>';
			endif;
		}
		if(!empty($output)) :
			return $output;
		endif;
	}
endif;
remove_shortcode( 'parish-upcoming-masses' );
add_shortcode( 'parish-upcoming-masses', 'kei_upcoming_masses_handler' );
?>