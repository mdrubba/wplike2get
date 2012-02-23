<?php
if (defined('WP_UNINSTALL_PLUGIN')) {
 	// delete options from db
	delete_option('l2g_options');
}
?>