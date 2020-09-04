<?php

/*
Plugin Name:     RRZE RSVP NETWORK
Plugin URI:      https://github.com/RRZE-Webteam/rrze-rsvp-network
Description:     Kontaktverfolgung zum Platzbuchungssystem (RRZE-RSVP) der FAU
Version:         1.0.1
Author:          RRZE-Webteam
Author URI:      https://blogs.fau.de/webworking/
License:         GNU General Public License v2
License URI:     http://www.gnu.org/licenses/gpl-2.0.html
Domain Path:     /languages
Text Domain:     rrze-rsvp-network
Network:         true
*/

namespace RRZE\RSVPNETWORK;

defined('ABSPATH') || exit;


// Autoloader (PSR-4)
spl_autoload_register(function ($class) {
    $prefix = __NAMESPACE__;
    $base_dir = __DIR__ . '/includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

const RRZE_PHP_VERSION = '7.4';
const RRZE_WP_VERSION = '5.4';

register_activation_hook(__FILE__, __NAMESPACE__ . '\activation');
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\deactivation');
add_action('plugins_loaded', __NAMESPACE__ . '\loaded');

/**
 * [loadTextdomain description]
 */
function loadTextdomain()
{
    load_plugin_textdomain('rrze-rsvp-network', false, sprintf('%s/languages/', dirname(plugin_basename(__FILE__))));
}

/**
 * [systemRequirements description]
 * @return string [description]
 */
function systemRequirements(): string
{
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    $error = '';
    if (version_compare(PHP_VERSION, RRZE_PHP_VERSION, '<')) {
        $error = sprintf(__('The server is running PHP version %1$s. The Plugin requires at least PHP version %2$s.', 'rrze-rsvp-network'), PHP_VERSION, RRZE_PHP_VERSION);
    } elseif (version_compare($GLOBALS['wp_version'], RRZE_WP_VERSION, '<')) {
        $error = sprintf(__('The server is running WordPress version %1$s. The Plugin requires at least WordPress version %2$s.', 'rrze-rsvp-network'), $GLOBALS['wp_version'], RRZE_WP_VERSION);
    // } elseif (is_plugin_active( plugin_dir_path( __DIR__ ) . 'rrze-rsvp/rrze-rsvp.php' ) == FALSE) {
    } elseif (file_exists( plugin_dir_path( __DIR__ ) . 'rrze-rsvp/rrze-rsvp.php' ) == FALSE) {
        // } elseif (is_plugin_active( plugin_dir_path( __DIR__ ) . 'rrze-rsvp/rrze-rsvp.php' ) == FALSE) {
        // $error = __('Plugin RRZE-RSVP must be active on at least one of your network\'s websites.', 'rrze-rsvp-network');
        $error = __('Plugin RRZE-RSVP does not exist.', 'rrze-rsvp-network');
    }

    return $error;
}

/**
 * [activation description]
 */
function activation()
{
    loadTextdomain();

    if ($error = systemRequirements()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(sprintf(__('Plugins: %1$s: %2$s', 'rrze-log'), plugin_basename(__FILE__), $error));
    }
}

/**
 * [deactivation description]
 */
function deactivation()
{
}

/**
 * [plugin description]
 * @return object
 */
function plugin(): object
{
    static $instance;
    if (null === $instance) {
        $instance = new Plugin(__FILE__);
    }
    return $instance;
}

/**
 * [loaded description]
 * @return void
 */
function loaded()
{
    loadTextDomain();
    plugin()->onLoaded();

    if ($error = systemRequirements()) {
        add_action('admin_init', function () use ($error) {
            if (current_user_can('activate_plugins')) {
                $pluginData = get_plugin_data(plugin()->getFile());
                $pluginName = $pluginData['Name'];
                $tag = is_plugin_active_for_network(plugin()->getBaseName()) ? 'network_admin_notices' : 'admin_notices';
                add_action($tag, function () use ($pluginName, $error) {
                    printf(
                        '<div class="notice notice-error"><p>' . __('Plugins: %1$s: %2$s', 'rrze-rsvp-network') . '</p></div>',
                        esc_html($pluginName),
                        esc_html($error)
                    );
                });
            }
        });
        return;
    }

    // Tracking
	$tracking = new NetworkTracking();
	$tracking->onLoaded();
}
