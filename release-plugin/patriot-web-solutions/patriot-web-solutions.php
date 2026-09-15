<?php
/**
 * Plugin Name: Patriot Web Solutions Site Release
 * Description: Reversible installer, forms, redirects, and project catalog for the Patriot Web Solutions redesign.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Author: Patriot Web Solutions
 * Text Domain: patriot-web-solutions
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PWS_RELEASE_VERSION', '1.0.0');
define('PWS_RELEASE_FILE', __FILE__);
define('PWS_RELEASE_DIR', plugin_dir_path(__FILE__));
define('PWS_RELEASE_URL', plugin_dir_url(__FILE__));

require_once PWS_RELEASE_DIR . 'includes/class-pws-installer.php';
require_once PWS_RELEASE_DIR . 'includes/class-pws-forms.php';
require_once PWS_RELEASE_DIR . 'includes/class-pws-public.php';
require_once PWS_RELEASE_DIR . 'admin/class-pws-admin.php';

register_activation_hook(__FILE__, array('PWS_Installer', 'activate'));

add_action('plugins_loaded', static function (): void {
    if (PWS_Installer::is_applied()) {
        PWS_Forms::register();
        PWS_Public::register();
    }
    if (is_admin()) {
        PWS_Admin::register();
    }
});
