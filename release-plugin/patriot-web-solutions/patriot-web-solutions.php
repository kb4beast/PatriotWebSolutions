<?php
/**
 * Plugin Name: Patriot Web Solutions Site Release
 * Description: Reversible installer, forms, redirects, and project catalog for the Patriot Web Solutions redesign.
 * Version: 2.0.2
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Author: Patriot Web Solutions
 * Text Domain: patriot-web-solutions
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PWS_RELEASE_VERSION', '2.0.2');
define('PWS_RELEASE_FILE', __FILE__);
define('PWS_RELEASE_DIR', plugin_dir_path(__FILE__));
define('PWS_RELEASE_URL', plugin_dir_url(__FILE__));

require_once PWS_RELEASE_DIR . 'includes/class-pws-facts.php';
require_once PWS_RELEASE_DIR . 'includes/class-pws-installer.php';
require_once PWS_RELEASE_DIR . 'includes/class-pws-forms.php';
require_once PWS_RELEASE_DIR . 'includes/class-pws-public.php';
require_once PWS_RELEASE_DIR . 'admin/class-pws-admin.php';

register_activation_hook(__FILE__, array('PWS_Installer', 'activate'));
add_action('wp_loaded', array('PWS_Installer', 'finalize_theme_menu_locations'), 100);

// Core kses strips tabindex, but scrollable <pre> exhibits need it for keyboard access (WCAG 2.1.1).
add_filter('wp_kses_allowed_html', static function ($tags, $context) {
    if ($context === 'post' && is_array($tags) && isset($tags['pre'])) {
        $tags['pre']['tabindex'] = true;
    }
    return $tags;
}, 10, 2);

add_action('plugins_loaded', static function (): void {
    // Any applied version keeps shortcodes, redirects, and headers alive while an upgrade is pending review.
    if (PWS_Installer::is_any_applied()) {
        PWS_Forms::register();
        PWS_Public::register();
    }
    if (is_admin()) {
        PWS_Admin::register();
    }
});
