<?php
/*
	Plugin Name: Kerk en IT Parish
	Plugin URI: http://www.kerkenit.nl/plugins/parish
	Description: Deze plugin heeft alles in huis voor het beheren van een goede parochie website.
	Version: 0.1
	Author: Kerk en IT
	Author URI: http://www.kerkenit.nl
	Text Domain: kei-parish
	Domain Path: languages
	License: GPL2
*/

/*
	Copyright 2015	Marco van 't Klooster  (email : info@kerkenit.nl)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA	02110-1301	USA
*/

if ( ! defined( 'KEI_FILE' ) ) {
	define( 'KEI_FILE', __FILE__ );
}

if ( ! defined( 'KEI_PATH' ) ) {
	define( 'KEI_PATH', plugin_dir_path( KEI_FILE ) );
}

if ( ! defined( 'KEI_BASENAME' ) ) {
	define( 'KEI_BASENAME', plugin_basename( KEI_FILE ) );
}

if ( ! defined( 'KEI_URL' ) ) {
	define( 'KEI_URL', plugin_dir_url( KEI_FILE) );
}

if ( ! defined( 'KEI_SLUG' ) ) {
	define( 'KEI_SLUG', 'kerkenit' );
}

if ( ! defined( 'KEI_NAME' ) ) {
	define( 'KEI_NAME', 'kerk-en-it-parish' );
}

if ( ! defined( 'KEI_LANG' ) ) {
	define( 'KEI_LANG', basename( dirname(__FILE__) ).'/languages/' );
}
load_plugin_textdomain( 'kei-parish', false, KEI_LANG );

class KeiParish {
	private static $tbl_church = '';
	private static $tbl_masses = '';
	private static $tbl_massesType = '';
	private static $tbl_diocese = '';

	private static $FK_masses_church = '';
	private static $FK_masses_massesType = '';

	static function init() {
		global $wpdb;
		load_plugin_textdomain( 'kei-parish', false, basename( dirname(__FILE__) ).'/languages/' );
		self::$tbl_church = $wpdb->prefix . 'kei_church';
		self::$tbl_masses = $wpdb->prefix . 'kei_masses';
		self::$tbl_massesType = $wpdb->prefix . 'kei_massesType';
		self::$tbl_diocese = $wpdb->prefix . 'kei_diocese';

		self::$FK_masses_church = 'FK_masses_church';
		self::$FK_masses_massesType = 'FK_masses_massesType';
	}
	static function activation() {
		self::init();
		global $wpdb;
		$sql = array();
		$sql[] = "CREATE TABLE IF NOT EXISTS `" . self::$tbl_church . "` (
			`ID` int(4) NOT NULL AUTO_INCREMENT,
			`title` varchar(255) NOT NULL COMMENT '" . __('Church name', 'kei-parish') . "',
			`address` varchar(255) NOT NULL COMMENT '" . __('Address of the church', 'kei-parish') . "',
			`zipcode` varchar(10) NOT NULL COMMENT '" . __('Postalcode of the church', 'kei-parish') . "',
			`city` varchar(255) NOT NULL COMMENT '" . __('Place of the church', 'kei-parish') . "',
			`diocese` varchar(255) NOT NULL COMMENT '" . __('Diocese of the parish', 'kei-parish') . "',
			`country` varchar(100) NOT NULL COMMENT '" . __('Country of the parish', 'kei-parish') . "',
			`latitude` float(10,6) DEFAULT NULL COMMENT '" . __('Geographic coordinate that specifies the north-south position of the church', 'kei-parish') . "',
			`longitude` float(10,6) DEFAULT NULL COMMENT '" . __('Geographic coordinate that specifies the east-west position of the church', 'kei-parish') . "',
			`email` varchar(255) DEFAULT NULL COMMENT '" . __('E-mail to reach the administration of this church', 'kei-parish') . "',
			`phone` varchar(25) DEFAULT NULL COMMENT '" . __('Telephone to reach the administration of this church', 'kei-parish') . "',
			`active` tinyint(1) DEFAULT 1 COMMENT '" . __('Church is active', 'kei-parish') . "',
			`insertdate` datetime NOT NULL,
			`updatedate` datetime DEFAULT NULL,
			PRIMARY KEY (`ID`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

		$sql[] = "CREATE TABLE IF NOT EXISTS `" . self::$tbl_massesType . "` (
			`ID` int(2) NOT NULL AUTO_INCREMENT,
			`title` varchar(100) NOT NULL COMMENT '" . __('Type of mass', 'kei-parish') . "',
			`active` tinyint(1) DEFAULT 1 COMMENT '" . __('Mass type is active', 'kei-parish') . "',
			`activeWidget` tinyint(1) DEFAULT 1 COMMENT '" . __('Mass type is active in the widgets', 'kei-parish') . "',
			`insertdate` datetime NOT NULL,
			`updatedate` datetime DEFAULT NULL,
			PRIMARY KEY (`ID`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

		$sql[] = "CREATE TABLE IF NOT EXISTS `" . self::$tbl_diocese . "` (
			`ID` int(11) NOT NULL AUTO_INCREMENT,
			`title` varchar(255) NOT NULL COMMENT '" . __('Name of diocese', 'kei-parish') . "',
			`i18n` varchar(5) NOT NULL COMMENT '" . __('Language code', 'kei-parish') . "',
			`insertdate` datetime NOT NULL,
			`updatedate` datetime DEFAULT NULL,
			PRIMARY KEY (`ID`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";


		$sql[] = "CREATE TABLE IF NOT EXISTS `" . self::$tbl_masses . "` (
			`ID` int(4) NOT NULL AUTO_INCREMENT,
			`church_ID` int(4) NOT NULL,
			`massType_ID` int(2) NOT NULL,
			`dayOfWeek` tinyint(1) NOT NULL,
			`hour` tinyint(2) NOT NULL,
			`minute` tinyint(2) NOT NULL,
			`note` varchar(255) NOT NULL,
			`active` tinyint(1) DEFAULT 1 COMMENT '" . __('Mass is active', 'kei-parish') . "',
			`insertdate` datetime NOT NULL,
			`updatedate` datetime DEFAULT NULL,
			PRIMARY KEY (`ID`),
			KEY `church_ID` (`church_ID`),
			KEY `massType_ID` (`massType_ID`),
			CONSTRAINT `" . self::$FK_masses_church . "` FOREIGN KEY (`church_ID`) REFERENCES `" . self::$tbl_church . "` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
			CONSTRAINT `" . self::$FK_masses_massesType . "` FOREIGN KEY (`massType_ID`) REFERENCES `" . self::$tbl_massesType . "` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

		if($wpdb->get_var("SHOW TABLES LIKE '" . self::$tbl_diocese . "'") !== self::$tbl_diocese ) :
			$sql_diocese = "INSERT INTO `" . self::$tbl_diocese . "` (`title`, `i18n`, `insertdate`) VALUES ";
			$cultureDioceses = array(
				'nl-NL' => array('Aartsbisdom Utrecht', 'Bisdom \'s-Hertogenbosch', 'Bisdom Breda', ' Bisdom Groningen-Leeuwarden', 'Bisdom Haarlem-Amsterdam', 'Bisdom Roermond', 'Bisdom Rotterdam', 'Ordinariaat voor de Ned. Strijdkrachten'),
				'nl-BE' => array('Aartsbisdom Mechelen-Brussel', 'Bisdom Antwerpen', 'Bisdom Brugge', 'Bisdom Gent', 'Bisdom Hasselt', 'Bisdom bij de Belg. krijgsmacht')
			);

			foreach($cultureDioceses as $i18n => $dioceses) {
				foreach($dioceses as $diocese) {
					$sql_diocese .= $wpdb->prepare("(%s, %s, NOW()),", $diocese, $i18n);
				}
	        }
	        $sql[] = rtrim($sql_diocese, ',') . ';';
		endif;

		if($wpdb->get_var("SHOW TABLES LIKE '" . self::$tbl_massesType . "'") !== self::$tbl_massesType ) :
	        $sql_massTypes = "INSERT INTO `" . self::$tbl_massesType . "` (`title`,`insertdate`) VALUES ";
			$massTypes = array(
								__('Stille mis', 'kei-parish'),
								__('Gezongen mis', 'kei-parish'),
								__('Hoogmis', 'kei-parish'),
								__('Plechtige Mis', 'kei-parish'),
								__('Pontificale Mis', 'kei-parish'),
								__('Eucharistische aanbidding', 'kei-parish'),
							);
			foreach($massTypes as $massType) {
				$sql_massTypes .= $wpdb->prepare("(%s, NOW()),", $massType);
			}
			$sql[] = rtrim($sql_massTypes, ',') . ';';
		endif;

		foreach($sql as $query) {
			$wpdb->query($query);
		}
	}
	static function deactivation() {
		return true;
	}
	static function uninstall() {
		self::init();
		global $wpdb;
		$sql = array();

		$sql[] = "DROP TABLE IF EXISTS `" . self::$tbl_masses . "`;";
		$sql[] = "DROP TABLE IF EXISTS `" . self::$tbl_massesType . "`;";
		$sql[] = "DROP TABLE IF EXISTS `" . self::$tbl_diocese . "`;";
		$sql[] = "DROP TABLE IF EXISTS `" . self::$tbl_church . "`;";

		foreach($sql as $query) {
			$wpdb->query($query);
		}

	}
}
register_activation_hook( KEI_FILE, array( 'KeiParish', 'activation' ) );
register_deactivation_hook( KEI_FILE, array( 'KeiParish', 'deactivation' ) );
register_uninstall_hook( KEI_FILE, array( 'KeiParish', 'uninstall' ) );




function kei_mass_admin_css() {
	wp_enqueue_style("kei_mass_Stylesheet", plugins_url('admin/stylesheet.css', __FILE__), array(), false, false);
}

function kei_admin_enqueue_scripts($hook) {
    wp_enqueue_script( 'kei_mass_google_maps', plugin_dir_url( __FILE__ ) . 'admin/google_maps.js' );
}

if (!function_exists('kei_add_menu_items')) {

	function kei_add_menu_items() {
		add_menu_page(__('Parish', 'kei-parish'), __('Parish', 'kei-parish'), 'activate_plugins', KEI_SLUG, 'kei_church_render_list_page', KEI_URL . '/images/icon.png', 90);

		add_submenu_page( KEI_SLUG, __('Churches', 'kei-parish'), __('Churches', 'kei-parish'), 'manage_options', 'kerkenit-churches', 'kei_church_render_list_page');
		add_submenu_page( KEI_SLUG, __('Masses', 'kei-parish'), __('Masses', 'kei-parish'), 'manage_options', 'kerkenit-masses', 'kei_mass_render_list_page');
		add_submenu_page( KEI_SLUG, __('Mass types', 'kei-parish'), __('Mass types', 'kei-parish'), 'manage_options', 'kerkenit-massTypes', 'kei_massType_render_list_page');

	}
}
require_once( dirname( KEI_FILE) . '/widget.php' );

if (!function_exists('kei_init')) {
	function kei_init() {
		require_once( dirname( KEI_FILE) . '/functions.php' );
		require_once( dirname( KEI_FILE) . '/admin/churches.php' );
		require_once( dirname( KEI_FILE) . '/admin/masses.php' );
		require_once( dirname( KEI_FILE) . '/admin/massTypes.php' );	}
}

function register_kei_parish_widgets() {
    register_widget( 'Kei_MassTimes_Widget' );
}

add_action('init', 'kei_init');
add_action('admin_head', 'kei_mass_admin_css');
add_action('admin_enqueue_scripts', 'kei_admin_enqueue_scripts' );
add_action('admin_menu', 'kei_add_menu_items');
add_action('widgets_init', 'register_kei_parish_widgets' );
?>