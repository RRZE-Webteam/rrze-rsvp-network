<?php

namespace RRZE\RSVPNETWORK;

defined('ABSPATH') || exit;

require_once plugin_dir_path( __DIR__ ) . '../rrze-rsvp/includes/Functions.php';
use RRZE\RSVP\Functions;

require_once plugin_dir_path( __DIR__ ) . '../rrze-rsvp/includes/Tracking.php';
use RRZE\RSVP\Tracking;

class NetworkTracking extends Tracking
{
    const DB_TABLE = 'rrze_rsvp_tracking';

    protected $dbTable;

    public function __construct()
    {
        global $wpdb;
        $this->dbTable = $wpdb->base_prefix . static::DB_TABLE;
    }

    public function onLoaded()
    {
        add_action( 'network_admin_menu', [$this, 'add_tracking_networkmenu']) ;
        add_action( 'wp_ajax_csv_pull', [$this, 'tracking_csv_pull'] );
    }

    protected function checkDbExists(){
        global $wpdb;

        return $wpdb->get_results("SELECT * FROM information_schema.tables WHERE table_schema = '{$wpdb->dbname}' AND table_name = '{$this->dbTable}' LIMIT 1", ARRAY_A);
    }

    public function add_tracking_networkmenu() {
        $menu_id = add_menu_page(
            _x( 'Contact tracking', 'admin page title', 'rrze-rsvp-network' ),
            _x( 'RSVP Contact tracking', 'admin menu entry title', 'rrze-rsvp-network' ),
            'manage_network_options',
            'rrze-rsvp-tracking',
            [$this, 'admin_page_tracking']
        );
    }

    public function admin_page_tracking() {

        if (!$this->checkDbExists()){
            echo '<div class="wrap">'
                . '<h1>' . esc_html_x( 'Contact tracking', 'admin page title', 'rrze-rsvp-network' ) . '</h1>'
                . '<div class="notice notice-error"><p>' . __('Plugin RRZE-RSVP has not been activated on any website of your network.', 'rrze-rsvp-network') . '</p></div>'
                . '</div>';

            return;
        }

        parent::admin_page_tracking();
    }
}
