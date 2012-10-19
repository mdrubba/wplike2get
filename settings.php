<?php
    /**
     * Handles the administration functions for the wpLike2Get plugin, sets up the plugin settings page, and loads
     * any and all files dealing with the admin for the plugin.
     *
     * @package WpLike2Get
     */

    /* Set up the administration functionality. */
    add_action( 'admin_menu', 'wplike2get_settings_page_init' );

    /**
     * Adds an additional menu item under the Settings menu.
     * Loads actions specifically for the wpLike2Get settings page.
     *
     * @since 1.1.0
     * @global $wplike2get
     */
    function wplike2get_settings_page_init() {
    	global $wplike2get;

        /* Add wpLike2get settings page. */
        $wplike2get->settings_page = add_options_page( __( 'wpLike2Get Settings', 'wplike2get' ), __( 'wpLike2Get', 'wplike2get' ), 'manage_options', 'wplike2get-settings', 'wplike2get_settings_page' );

    	/* Register the theme settings. */
    	add_action( 'admin_init', 'wplike2get_register_settings' );

        /* Add default settings if none are present. */
        add_action( "load-{$wplike2get->settings_page}", 'wplike2get_load_settings_page' );

    	/* Add media for the settings page. */
    	add_action( "load-{$wplike2get->settings_page}", 'wplike2get_admin_enqueue_style' );
    	add_action( "load-{$wplike2get->settings_page}", 'wplike2get_settings_page_media' );
    	add_action( "admin_head-{$wplike2get->settings_page}", 'wplike2get_settings_page_scripts' );

    	/* Load the meta boxes. */
    	add_action( "load-{$wplike2get->settings_page}", 'wplike2get_load_meta_boxes' );

    	/* Create a hook for adding meta boxes. */
    	add_action( "load-{$wplike2get->settings_page}", 'wplike2get_add_meta_boxes' );
    }

    /**
     * Adds the default wpLike2Get settings to the database if they have not been set.
     *
     * @since 1.1.0
     */
    function wplike2get_load_settings_page() {

    	/* Get settings from the database. */
    	$settings = get_option( 'wplike2get_settings' );

    	/* If no settings are available, add the default settings to the database. */
    	if ( empty( $settings ) ) {
    		$settings = wplike2get_default_settings();
    		add_option( 'wplike2get_settings', $settings, '', 'yes' );

    		/* Redirect the page so that the settings are reflected on the settings page. */
    		wp_redirect( admin_url( 'options-general.php?page=wplike2get-settings' ) );
    		exit;
    	}
    }

    /**
     * Registers the wpLike2Get settings.
     * @uses register_setting() to add the settings to the database.
     *
     * @since 1.1.0
     */
    function wplike2get_register_settings() {
    	register_setting( 'wplike2get_plugin_settings', 'wplike2get_settings' );
    }

    /**
     * Returns an array of the default plugin settings.  These are only used on initial setup.
     *
     * @since 1.1.0
     */
    function wplike2get_default_settings() {
    	return array(
            'plugin_path' => WPLIKE2GET_URI,
            'keep_after_reload' => false,
            'l2g_link_identifier' => 'Download',
            'l2g_show_plugin_link' => false,
            'l2g_single_activation' => true, // @since 1.2.2

            'fb_activated' => true,
            'fb_appID' => '',
            'fb_userID' => '',
            'fb_siteName' => '',
            'fb_buttonWidth' => '',
            'fb_showfaces' => false,
            'fb_send' => false,
            'fb_comments' => false,
            'fb_font' => 'lucida grande',
            'fb_layout' => 'box_count',
            'fb_action' => 'like',
            'fb_colorscheme' => 'light',
            'fb_hide' => false,
            'fb_ga' => false,
            'fb_ga_version' => 'pageTracker',

            'tw_activated' => true,
            'tw_user' => false,
            'tw_user_description' => false,
            'tw_url' => false,
            'tw_count_url' => false,
            'tw_title' => false,
            'tw_layout' => 'vertical',
            'tw_action' => 'tweet',
            'tw_lang' => 'en',
            'tw_hide' => false,
            'tw_ga' => false,
            'tw_ga_version' => 'pageTracker',

            'gp_activated' => true,
            'gp_size' => 'tall',
            'gp_count' => true,
            'gp_url' => false,
            'gp_lang' => 'en-US',
            'gp_hide' => false,
            'gp_ga' => false,
            'gp_ga_version' => 'pageTracker'
        );
    }

    /**
     * Executes the 'add_meta_boxes' action hook because WordPress doesn't fire this on custom admin pages.
     *
     * @since 1.1.0
     */
    function wplike2get_add_meta_boxes() {
    	global $wplike2get;
    	$plugin_data = get_plugin_data( WPLIKE2GET_DIR . 'wpLike2Get.php' );
    	do_action( 'add_meta_boxes', $wplike2get->settings_page, $plugin_data );
    }

    /**
     * Loads the plugin settings page meta boxes.
     *
     * @since 1.1.0
     */
    function wplike2get_load_meta_boxes() {
    	require_once( WPLIKE2GET_DIR . 'meta-boxes.php' );
    }

    /**
     * Displays the HTML and meta boxes for the plugin settings page.
     *
     * @since 1.1.0
     */
    function wplike2get_settings_page() {
    	global $wplike2get;
        ?>

    	<div class="wrap">

    		<?php screen_icon(); ?>

    		<h2><?php _e( 'wpLike2Get Plugin Settings', 'wplike2get' ); ?></h2>

    		<div id="poststuff">

    			<form method="post" action="options.php">
    				<?php settings_fields( 'wplike2get_plugin_settings' ); ?>
    				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
    				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

    				<div class="metabox-holder">
    					<div class="post-box-container column-1 normal"><?php do_meta_boxes( $wplike2get->settings_page, 'normal', null ); ?></div>
    					<div class="post-box-container column-2 side"><?php do_meta_boxes( $wplike2get->settings_page, 'side', null ); ?></div>
    				</div>

                    <input type="hidden" name="wplike2get_settings[version]" value="<?php echo WPLIKE2GET_VERSION ?>"/>

    				<?php submit_button( esc_attr__( 'Update Settings', 'wplike2get' ) ); ?>
    			</form>

    		</div><!-- #poststuff -->

    	</div><!-- .wrap --><?php
    }

    /**
     * Loads the admin stylesheet for the plugin settings page.
     *
     * @since 1.1.0
     */
    function wplike2get_admin_enqueue_style() {
    	wp_enqueue_style( 'wplike2get-admin', trailingslashit( WPLIKE2GET_URI ) . 'css/admin.css', false, 1.1, 'screen' );
    }

    /**
     * Loads needed JavaScript files for handling the meta boxes on the settings page.
     *
     * @since 1.1.0
     */
    function wplike2get_settings_page_media() {
    	wp_enqueue_script( 'common' );
    	wp_enqueue_script( 'wp-lists' );
    	wp_enqueue_script( 'postbox' );
    }

    /**
     * Loads JavaScript for handling the open/closed state of each meta box.
     *
     * @since 1.1.0
     * @global $wplike2get The path of the settings page.
     */
    function wplike2get_settings_page_scripts() {
    	global $wplike2get; ?>
    	<script type="text/javascript">
    		//<![CDATA[
    		jQuery(document).ready( function($) {
    			$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
    			postboxes.add_postbox_toggles( '<?php echo $wplike2get->settings_page; ?>' );
    		});
    		//]]>
    	</script>
    <?php }