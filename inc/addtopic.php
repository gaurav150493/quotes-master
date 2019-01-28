<?php
    require_once( '../../../../wp-load.php' );
    if(empty($_POST["newtopic"])){
        echo 'no topic found';
        die();
    } else {
        $newtopic = $_POST["newtopic"];
    };

    // insert new topics
    if($newtopic){
        $newtopicslug = strtolower($newtopic);
        $newtopicslug = str_replace(' ', '-', $newtopicslug);
        global $wpdb;
        $wpdb->insert("wp_quotes_topics", array(
            'name' => $newtopic,
            'slug' => $newtopicslug
        ));
        echo 'topic added';
    };
?>
