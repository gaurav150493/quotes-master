<?php
    require_once( '../../../../wp-load.php' );

    global $wpdb;
    $topics = $wpdb->get_results( "SELECT id, name FROM wp_quotes_topics");
    $topics = $topics;
    $res = array(
        "status" => 1,
        "data" => $topics
    );
    echo json_encode($res);
?>
