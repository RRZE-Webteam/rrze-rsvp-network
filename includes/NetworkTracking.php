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
        $this->checkDbExists();

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


        // $searchdate = '';
        // $delta = 0;
        // $guest_firstname = '';
        // $guest_lastname = '';
        // $guest_email = '';
        // $guest_phone = '';

        // echo '<div class="wrap">';
        // echo '<h1>' . esc_html_x( 'Contact tracking', 'admin page title', 'rrze-rsvp-network' ) . '</h1>';

        // if ( isset( $_GET['submit'])) {
        //     /*
        //      * Submit Form, Search for users
        //      */

        //     $searchdate = filter_input(INPUT_GET, 'searchdate', FILTER_SANITIZE_STRING); // filter stimmt nicht
        //     $delta = filter_input(INPUT_GET, 'delta', FILTER_VALIDATE_INT, ['min_range' => 0]);
        //     $guest_firstname = filter_input(INPUT_GET, 'guest_firstname', FILTER_SANITIZE_STRING);
        //     $guest_lastname = filter_input(INPUT_GET, 'guest_lastname', FILTER_SANITIZE_STRING);
        //     $guest_email = filter_input(INPUT_GET, 'guest_email', FILTER_VALIDATE_EMAIL);
        //     $guest_phone = filter_input(INPUT_GET, 'guest_phone', FILTER_SANITIZE_STRING);

        //     $aGuests = Tracking::getUsersInRoomAtDate($searchdate, $delta, $guest_firstname, $guest_lastname, $guest_email, $guest_phone);

        //     if ($aGuests){
        //         // generate CSV
        //         $ajax_url = admin_url('admin-ajax.php?action=csv_pull') . '&page=rrze-rsvp-tracking&searchdate=' . urlencode($searchdate) . '&delta=' . urlencode($delta) . '&guest_firstname=' . urlencode($guest_firstname) . '&guest_lastname=' . urlencode($guest_lastname) . '&guest_email=' . urlencode($guest_email) . '&guest_phone=' . urlencode($guest_phone);
        //         echo '<div class="notice notice-success is-dismissible">';
        //         echo '<h2>Guests found!</h2>';
        //         echo "<a href='$ajax_url'>Download CSV</a>";
        //         echo '</div>';
        //     }else{
        //         echo '<div class="notice notice-success is-dismissible">';
        //         echo '<h2>No guests found</h2>';
        //         echo '</div>';
        //     }
        // }

        // /*
        //  * Build Form
        //  */

        // echo '<form id="rsvp-search-tracking" method="get">';
        // echo '<input type="hidden" name="page" value="rrze-rsvp-tracking">';
        // echo '<table class="form-table" role="presentation"><tbody>';

        // echo '<tr>'
        //     . '<th scope="row"><label for="searchdate">' . __('Search date', 'rrze-rsvp-network') . '</label></th>'
        //     . '<td><input type="text" id="searchdate" name="searchdate" placeholder="YYYY-MM-DD" pattern="(?:19|20)[0-9]{2}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))" value="' . $searchdate . '">'
        //     . '</td>'
        //     . '</tr>';

        // echo '<tr>'
        //     . '<th scope="row"><label for="delta">' . '&#177; ' . __('days', 'rrze-rsvp-network') . '</label></th>'
        //     . '<td><input type="number" id="delta" name="delta" min="0" required value="' . $delta . '"></td>'
        //     . '</tr>'; // $value_delta
        
        // echo '<tr>'
        //     . '<th scope="row"><label for="guest_firstname">' . __('First name', 'rrze-rsvp-network') . '</label></th>'
        //     . '<td><input type="text" id="guest_firstname" name="guest_firstname" value="' . $guest_firstname . '">'
        //     . '</td>'
        //     . '</tr>';

        // echo '<tr>'
        //     . '<th scope="row"><label for="guest_lastname">' . __('Last name', 'rrze-rsvp-network') . '</label></th>'
        //     . '<td><input type="text" id="guest_lastname" name="guest_lastname" value="' . $guest_lastname . '">'
        //     . '</td>'
        //     . '</tr>';

        // echo '<tr>'
        //     . '<th scope="row"><label for="guest_email">' . __('Email', 'rrze-rsvp-network') . '</label></th>'
        //     . '<td><input type="text" id="guest_email" name="guest_email" value="' . $guest_email . '">'
        //     . '</td>'
        //     . '</tr>';

        // echo '<tr>'
        //     . '<th scope="row"><label for="guest_phone">' . __('Phone', 'rrze-rsvp-network') . '</label></th>'
        //     . '<td><input type="text" id="guest_phone" name="guest_phone" value="' . $guest_phone . '">'
        //     . '</td>'
        //     . '</tr>';

        // echo '</tbody></table>';
        // echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="' . __('Search', 'rrze-rsvp-network') . '"></p>';

        // echo '</form>';
        // echo '</div>';
    }

    // public function tracking_csv_pull() {
    //     $searchdate = filter_input(INPUT_GET, 'searchdate', FILTER_SANITIZE_STRING); // filter stimmt nicht
    //     $delta = filter_input(INPUT_GET, 'delta', FILTER_VALIDATE_INT, ['min_range' => 0]);
    //     $guest_firstname = filter_input(INPUT_GET, 'guest_firstname', FILTER_SANITIZE_STRING);
    //     $guest_lastname = filter_input(INPUT_GET, 'guest_lastname', FILTER_SANITIZE_STRING);
    //     $guest_email = filter_input(INPUT_GET, 'guest_email', FILTER_VALIDATE_EMAIL);
    //     $guest_phone = filter_input(INPUT_GET, 'guest_phone', FILTER_SANITIZE_STRING);

    //     $aGuests = Tracking::getUsersInRoomAtDate($searchdate, $delta, $guest_firstname, $guest_lastname, $guest_email, $guest_phone);

    //     $file = 'rrze_tracking_csv';
    //     $csv_output = '';

    //     if ($aGuests){
    //         foreach ($aGuests as $row){
    //             $row = array_values($row);
    //             $row = implode(", ", $row);
    //             $csv_output .= $row."\n";
    //          }
    //     }
 
    //     $filename = $file . "_" . date("Y-m-d_H-i", time());
    //     header( "Content-type: application/vnd.ms-excel" );
    //     header( "Content-disposition: csv" . date("Y-m-d") . ".csv" );
    //     header( "Content-disposition: filename=" . $filename . ".csv" );
    //     print $csv_output;
    //     exit;
    // }


    // public static function getUsersInRoomAtDate(string $searchdate, int $delta, string $guest_firstname, string $guest_lastname, string $guest_email = '', string $guest_phone = ''): array
    // {
    //     global $wpdb;

    //     $dbTrackingTable = Tracking::getDbTableName();

    //     // BK TEST AUSKOMMENTIERT
    //     // if (!$guest_email && !$guest_firstname && !$guest_lastname){
    //     //     // we have nothing to search for
    //     //     return [];
    //     // }

    //     // if (!Functions::validateDate($searchdate)){
    //     //     // is not 'YYYY-MM-DD'
    //     //     return [];
    //     // }
    //     $test = Functions::validateDate($searchdate);
    //     echo '<pre>';
    //     var_dump($test);
    //     exit;

    //     //  "Identifikationsmerkmalen für eine Person (Name, E-Mail und oder Telefon)" see https://github.com/RRZE-Webteam/rrze-rsvp/issues/89
    //     $hash_guest_firstname = Functions::crypt(strtolower($guest_firstname));
    //     $hash_guest_lastname = Functions::crypt(strtolower($guest_lastname));
    //     $hash_guest_email = Functions::crypt(strtolower($guest_email));
    //     $hash_guest_phone = Functions::crypt($guest_phone);

    //     $prepare_vals = [
    //         $searchdate,
    //         $delta,
    //         $searchdate,
    //         $delta,
    //         $searchdate,
    //         $delta,
    //         $searchdate,
    //         $delta,
    //         $searchdate,
    //         $delta,
    //         $searchdate,
    //         $delta,
    //         $searchdate,
    //         $delta,
    //         $searchdate,
    //         $delta,
    //         $hash_guest_firstname,
    //         $hash_guest_lastname,
    //         $hash_guest_email,
    //         $hash_guest_phone
    //     ];

    //     $rows = $wpdb->get_results( 
    //         $wpdb->prepare("SELECT surrounds.start, surrounds.end, surrounds.room_name, surrounds.room_street, surrounds.room_zip, surrounds.room_city, surrounds.guest_email, surrounds.guest_phone, surrounds.guest_firstname, surrounds.guest_lastname 
    //         FROM {$dbTrackingTable} AS surrounds 
    //         WHERE (DATE(surrounds.start) BETWEEN DATE_SUB(%s, INTERVAL %d DAY) AND DATE_ADD(%s, INTERVAL %d DAY)) AND (DATE(surrounds.end) BETWEEN DATE_SUB(%s, INTERVAL %d DAY) AND DATE_ADD(%s, INTERVAL %d DAY)) AND 
    //         surrounds.room_post_id IN 
    //         (SELECT needle.room_post_id FROM {$dbTrackingTable} AS needle WHERE 
    //         (DATE(needle.start) BETWEEN DATE_SUB(%s, INTERVAL %d DAY) AND DATE_ADD(%s, INTERVAL %d DAY)) AND 
    //         (DATE(needle.end) BETWEEN DATE_SUB(%s, INTERVAL %d DAY) AND DATE_ADD(%s, INTERVAL %d DAY)) AND 
    //         needle.hash_guest_firstname = %s AND needle.hash_guest_lastname = %s AND
    //         ((needle.hash_guest_email = %s) OR (needle.hash_guest_phone = %s))) 
    //         ORDER BY surrounds.start, surrounds.guest_lastname", $prepare_vals), ARRAY_A);

    //     // simpelst solution but a question of user's file rights: 
    //     // select ... INTO OUTFILE '$path_to_file' FIELDS TERMINATED BY ',' LINES TERMINATED BY ';' from ...

    //     return $rows;
    // }


    // public static function getDbTableName()
    // {
    //     global $wpdb;
    //     return $wpdb->base_prefix . static::DB_TABLE;
    // }
}
