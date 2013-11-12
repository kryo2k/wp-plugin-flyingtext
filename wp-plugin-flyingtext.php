<?php
/**
 * Plugin Name: Flying text plugin
 * Description: Displays flying text in a container implemented on hotelarenalkioro.com
 * Version: 0.1
 * Author: Hans Doller
 * Author URI: http://ticonerd.com
 * License: GPLv3
 */

if (! function_exists ( 'add_action' )) {
	exit ();
}

define ( "FLYTXT_I18N", 'flyTxt' );
define ( "FLYTXT_KEY_SETTINGS", 'flyTxt-settings' );

define ( "FLYTXT_SETTING_ENABLED",   'flyTxt_enabled' );
define ( "FLYTXT_SETTING_TARGETSEL", 'flyTxt_targetselector' );

function flyTxt_admin_init() {
	global $wp_version;
	if (! function_exists ( 'is_multisite' ) && version_compare ( $wp_version, '3.0', '<' )) {
		wp_die( __( 'Flying text plugin requires wordpress >= 3.0' ) );
	}

	flyTxt_admin_register_settings();
}
function flyTxt_admin_plugin_action_links($links, $file) {
	if ($file == plugin_basename ( __FILE__ )) {
		$links [] = '<a href="' . add_query_arg ( array (
				'page' => FLYTXT_KEY_SETTINGS 
		), admin_url ( 'options-general.php' ) ) . '">' . __ ( 'Settings', FLYTXT_I18N ) . '</a>';
	}
	
	return $links;
}
function flyTxt_admin_menu() {
	add_options_page( __ ( 'Flying Text Manager', FLYTXT_I18N ), __ ( 'Flying Text', FLYTXT_I18N ), 'manage_options', FLYTXT_KEY_SETTINGS, 'flyTxt_admin_options' );
}
function flyTxt_admin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

?><form method="POST" action="options.php">
<?php settings_fields(FLYTXT_KEY_SETTINGS); // pass slug name of page, also referred to in Settings API as option group name
do_settings_sections(FLYTXT_KEY_SETTINGS);  // pass slug name of page
submit_button();
?>
</form><?php
}
function flyTxt_admin_setting_section_general() {
?><p><?php esc_html_e( 'General settings for flying text plugin.', FLYTXT_I18N ); ?></p><?php
}
function flyTxt_admin_setting_enabled() {
	echo sprintf('<input name="%s" type="checkbox" value="1" class="code"%s>',FLYTXT_SETTING_ENABLED, checked( 1, flyTxt_get_enabled(), false ));
}
function flyTxt_admin_setting_targetselector() {
	echo sprintf('<input name="%s" size="50" type="text" value="%s">',FLYTXT_SETTING_TARGETSEL, flyTxt_get_targetselector());
}
function flyTxt_admin_get_settings_sections() {
	return (array) apply_filters('flyTxt_admin_get_settings_sections', array(
		'flyTxt_general' => array(
			'title'    => __( 'General settings', FLYTXT_I18N ),
			'callback' => 'flyTxt_admin_setting_section_general'
		)
	));
}
function flyTxt_admin_get_settings_fields() {
	return (array) apply_filters('flyTxt_admin_get_settings_fields', array(
		'flyTxt_general' => array(
			FLYTXT_SETTING_ENABLED => array(
				'title'             => __( 'Enable flying text plugin', FLYTXT_I18N ),
				'callback'          => 'flyTxt_admin_setting_enabled',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),
			FLYTXT_SETTING_TARGETSEL => array(
				'title'             => __( 'jQuery target for flying text', FLYTXT_I18N ),
				'callback'          => 'flyTxt_admin_setting_targetselector',
				'args'              => array()
			)
		)
	));
}
function flyTxt_admin_get_settings_fields_for_section( $section_id = '' ) {

	// Bail if section is empty
	if ( empty( $section_id ) )
		return false;

	$fields = flyTxt_admin_get_settings_fields();
	$retval = isset( $fields[$section_id] ) ? $fields[$section_id] : false;

	return (array) apply_filters( 'flyTxt_admin_get_settings_fields_for_section', $retval, $section_id );
}
function flyTxt_admin_register_settings() {
	$sections = flyTxt_admin_get_settings_sections();

	if ( empty( $sections ) )
		return false;

	foreach ( (array) $sections as $section_id => $section ) {

		$page = empty($section['page']) ? FLYTXT_KEY_SETTINGS : $section['page'];
		$fields = flyTxt_admin_get_settings_fields_for_section( $section_id );

		if ( empty( $fields ) )
			continue;

		add_settings_section(
			$section_id,
			$section['title'],
			$section['callback'],
			$page
		);

		foreach ( (array) $fields as $field_id => $field ) {

			if ( ! empty( $field['callback'] ) && !empty( $field['title'] ) ) {
				add_settings_field( $field_id, $field['title'], $field['callback'], $page, $section_id, $field['args'] );
			}

			register_setting( $page, $field_id, $field['sanitize_callback'] );
		}
	}
}
function flyTxt_get_enabled() {
	return intval ( get_option ( FLYTXT_SETTING_ENABLED, 1 ) ) === 1;
}
function flyTxt_get_targetselector() {
	return get_option ( FLYTXT_SETTING_TARGETSEL, 'header' );
}
function flyTxt_site_header_style() {
	wp_enqueue_style('flyTxt', path_join(plugin_dir_url(__FILE__),
		"css/style.css"), false);
}
function flyTxt_site_header_script() {
	wp_enqueue_script('flyTxt', path_join(plugin_dir_url(__FILE__),
		"js/core.js"), false);
}
function flyTxt_site_header_script_config() {
	echo sprintf('<script type="text/javascript">window.flyTxt_config = %s;</script>',
		json_encode(array(
			'selector' => flyTxt_get_targetselector(),
			'enabled' => flyTxt_get_enabled()
		))
	);
}
function flyTxt_site_init() {
}
function flyTxt_controller_admin_boot() {
	add_action ( 'admin_init', 'flyTxt_admin_init' );
	add_action ( 'admin_menu', 'flyTxt_admin_menu' );
	add_filter ( 'plugin_action_links', 'flyTxt_admin_plugin_action_links', 10, 2 );
}
function flyTxt_controller_site_boot() {
	if(!flyTxt_get_enabled()) return;

	add_action( 'init', 'flyTxt_site_init' );
	add_action( 'wp_enqueue_scripts', 'flyTxt_site_header_style' );
	add_action( 'wp_enqueue_scripts', 'flyTxt_site_header_script' );
	add_action( 'wp_head', 'flyTxt_site_header_script_config' );
}

// bootstrap the correct front-end controller:
is_admin () ? flyTxt_controller_admin_boot () : flyTxt_controller_site_boot ();