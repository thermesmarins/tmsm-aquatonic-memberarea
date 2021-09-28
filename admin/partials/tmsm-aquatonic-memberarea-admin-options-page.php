<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Tmsm_Aquatonic_Memberarea
 * @subpackage Tmsm_Aquatonic_Memberarea/admin/partials
 */
?>
<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

<?php
// Display errors in settings page
$errors = get_option( 'tmsm-aquatonic-memberarea-errors' );
if ( ! empty( $errors ) && is_array( $errors ) ) {?>
<div class="notice notice-error settings-error is-dismissible">
	<p><?php
		echo __( 'There are errors with the web service:', 'tmsm-aquatonic-memberarea' );
		echo '<br>';
		echo join( '<br>', $errors );?></p></div>
<?php
}
?>

<form method="post" action="options.php"><?php
	settings_fields( $this->plugin_name . '-options' );
	do_settings_sections( $this->plugin_name );
	submit_button( __( 'Save options', 'tmsm-aquatonic-memberarea' ));

	do_action( 'tmsm_aquatonic_memberarea_cronaction' );
	?></form>