<?php
class GTFS_Route_Model {

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'gtfs_routes';
    }

    public function insert_route( $data ) {
        global $wpdb;
        return $wpdb->insert($this->table_name, $data);
    }

    public function get_route_by_id( $route_id ) {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE route_id = %s", $route_id);
        return $wpdb->get_row($sql);
    }
}
