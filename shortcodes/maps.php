<?php
if(!function_exists('kei_maps_enqueue_script')) :
	function kei_maps_enqueue_script() {
		 echo '<script type="text/javascript" src="http://maps.google.com/maps/api/js?v=3&sensor=false"></script>';
	}
endif;
add_action( 'wp_head', 'kei_maps_enqueue_script' );

if(!function_exists('kei_maps_footer_script')) :
	function kei_maps_footer_script($script) {
		return '<script type="text/javascript">' . $script . '</script>';
	} 
endif;
if(!function_exists('kei_maps_handler')) :
	function kei_maps_handler( $atts, $content = null ) {
		load_plugin_textdomain( 'kei-parish', false, KEI_LANG);
		global $wpdb;
	    $atts_extended = shortcode_atts( array(
	        'church_id' => '',
	        'subtitle' => '',
	        'height' => '',
	    ), $atts );
	    extract($atts_extended);

		$single_church = (isset($church_id) && !empty($church_id) && is_numeric($church_id) && $church_id > 0);
		$and = '';
		if($single_church) :
			$and = 'AND `ID` = ' . $church_id . '';
		endif;
		$churches = $wpdb->get_results("SELECT `title`, `address`, `zipcode`, `city`, `latitude`, `longitude` FROM `" . $wpdb->prefix . 'kei_church' . "` WHERE `active` = 1 $and ORDER BY `title`;", OBJECT );

		if(!isset($height) || empty($height)) :
			$height = 250;
		endif;
		if(is_numeric($height)) :
			$height = $height . 'px';
		endif;
		$output = "<div id=\"map-canvas\" style=\"height:$height\"></div>";
		$js = '';
		if(!$single_church && $content != null && !empty($content)) :
			$output .= sprintf('<p>%s</p>', $content);
		endif;
		if(!$single_church && !empty($subtitle)) :
			$output .= sprintf('<p>%s</p>', $subtitle);
		endif;
		
		$i = 0;
		
		$js .= "function initialize() {";
		$js .= "var mapOptions = {";
		$js .= "	zoom: 14,";
		$js .= "	panControl: true,";
		$js .= "	scaleControl: true,";
		$js .= "	scrollwheel: false,";
		$js .= "	mapTypeControl: true,";
		$js .= "	mapTypeControlOptions: {";
		$js .= "		style: google.maps.MapTypeControlStyle.ROADMAP,";
		$js .= "		mapTypeIds: []";
		$js .= "	},";
		$js .= "	zoomControl: true,";
		$js .= "	zoomControlOptions: {";
		$js .= "		style: google.maps.ZoomControlStyle.SMALL";
		$js .= "	}";
		$js .= "};";
		
		$js .= "bounds  = new google.maps.LatLngBounds();";
		
		$js .= "var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);";

		foreach($churches as $church)
		{
			$i += 1;
			//$js .= 'alert("' . $church->title . '");';
			$js .= "var myLatlng$i = new google.maps.LatLng(" . $church->latitude . "," . $church->longitude . ");";
			
			$js .= "loc = new google.maps.LatLng(" . $church->latitude . ", " . $church->longitude . ");";
			$js .= "bounds.extend(loc);";
			
			$js .= "var contentString$i = '<div id=\"content\"><div id=\"siteNotice\"></div><div id=\"bodyContent\">";
			$js .= "<p>";
			$js .= "<strong>" . str_replace("'", "\'", $church->title) . "</strong>";
			$js .= "</p>";
			$js .= "<p>";
			$js .= str_replace("'", "\'", $church->address) . "<br />";
			$js .= str_replace("'", "\'", $church->zipcode) . " " . str_replace("'", "\'", $church->city);
			$js .= "</p>";
			$js .= "</div></div>';";
			
			$js .= "var infowindow$i = new google.maps.InfoWindow({";
			$js .= "	content: contentString$i";
			$js .= "});";
			
			$js .= "var marker$i = new google.maps.Marker({";
			$js .= "	position: myLatlng$i,";
			$js .= "	map: map,";
			$js .= "	title: '" . str_replace("'", "\'", $church->title) . "'";
			$js .= "});";
			
			$js .= "google.maps.event.addListener(marker$i, 'click', function() {";
			$js .= "	infowindow$i.open(map,marker$i);";
			$js .= "});";			
		}
		$js .= "map.fitBounds(bounds);";
		$js .= "map.panToBounds(bounds);";
		$js .= "}";
		$js .= "google.maps.event.addDomListener(window, 'load', initialize);";
		if(!empty($js)) :
			$output .= kei_maps_footer_script($js);
		endif;
		if(!empty($output)) :
			return $output;
		endif;
	}
endif;
remove_shortcode( 'parish-maps' );
add_shortcode( 'parish-maps', 'kei_maps_handler' );
?>