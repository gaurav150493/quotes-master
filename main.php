<?php 
    /*
    Plugin Name: Quotes Master
    Plugin URI: https://github.com/gaurav150493/quotes-master
    Description: Plugin for Quotes with author and topics
    Author: Gaurav Aggarwal
    Version: 1.0
    Author URI: http://gauravaggarwal.me/
    */

    // Prohibit direct script loading.
    defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

    // require connected functions
    require_once( 'inc/quotes.php' );
    require_once( 'inc/author.php' );
    require_once( 'inc/bulkupload.php' );
    require_once( 'inc/shortcode_quotes_by_author.php' );
    require_once( 'inc/shortcode_quotes_by_topic.php' );

    // load quotes scripts and styles
    add_action( 'admin_enqueue_scripts', 'load_quotes_scripts_and_styles' );
    

    function load_quotes_scripts_and_styles(){
        if($_REQUEST['page']=='quotes' || $_REQUEST['page']=='authors' || $_REQUEST['page']=='bulk-upload'){
            wp_enqueue_style('custom_quotes', plugins_url('css/jquery-ui.css', __FILE__));
            wp_enqueue_script('custom_quotes', plugins_url('js/jquery-ui.js', __FILE__));

            wp_enqueue_style('custom_quotes1', plugins_url('css/quote_style.css', __FILE__));
            wp_enqueue_script('custom_quotes1', plugins_url('js/quote_script.js', __FILE__));

            wp_localize_script('custom_quotes', 'currentUrl', plugins_url('inc', __FILE__) );
        };
    };

    // activation hook
    register_activation_hook( __FILE__, 'add_db_tables' );
    function add_db_tables(){
        global $wpdb;

        $sqlQuotes = "CREATE TABLE IF NOT EXISTS `wp_quotes` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `quotes_text` text NOT NULL,
            `author` int(11) DEFAULT NULL,
            `likes` int(11) DEFAULT NULL,
            `status` enum('0','1') NOT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

        $sqlQuotesAuthor = "CREATE TABLE IF NOT EXISTS `wp_quotes_author` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL DEFAULT '',
            `slug` varchar(255) NOT NULL,
            `image` text,
            `date_of_birth` date DEFAULT NULL,
            `date_of_death` date DEFAULT NULL,
            `profession` varchar(255) DEFAULT NULL,
            `nationality` varchar(255) DEFAULT NULL,
            `status` enum('0','1') NOT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

        $sqlQuotesTopics = "CREATE TABLE IF NOT EXISTS `wp_quotes_topics` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(200) NOT NULL DEFAULT '',
            `slug` varchar(200) NOT NULL DEFAULT '',
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

        $sqlQuotesTopicsMap = "CREATE TABLE IF NOT EXISTS `wp_topics_quotes_map` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `topic_id` int(11) NOT NULL,
            `quote_id` int(11) NOT NULL,
            `status` enum('0','1') NOT NULL DEFAULT '1',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

        $sqlAnonymousAuthor = "INSERT INTO `mylo_website`.`wp_quotes_author` (`name`, `slug`, `status`) VALUES ('Anonymous', 'anonymous', '1');";

        $wpdb->query($sqlQuotes);
        $wpdb->query($sqlQuotesAuthor);
        $wpdb->query($sqlQuotesTopics);
        $wpdb->query($sqlQuotesTopicsMap);
        $wpdb->query($sqlAnonymousAuthor);
    };

    // define menu options
    function quotes_admin_options() {
        add_menu_page(
            'Quotes',                                       // page title
            'Quotes',                                       // menu title
            'manage_options',                               // user level
            'quotes',                                       // menu-slg 
            'quotes_pages_call_function',                   // callback function
            'dashicons-format-quote',                       // icon
            50                                              // position
        );

        add_submenu_page(
            'quotes',
            'Authors',
            'Authors',
            'manage_options',
            'authors',
            'author_pages_call_function'
        );

        add_submenu_page(
            'quotes',
            'Bulk Upload',
            'Bulk Upload',
            'manage_options',
            'bulk-upload',
            'bulkupload_pages_call_function'
        );
    }
    add_action( 'admin_menu', 'quotes_admin_options' );


    function get_quotes_topics($params){
        global $wpdb;
        $allTopicsResult = $wpdb->get_results("select * from wp_quotes_topics order by id desc limit 20");
        $allTopicsHtml = '';
        foreach($allTopicsResult as $topic){
            $allTopicsHtml .=   '<li><a href="'.get_site_url().'/quotes/topic/'.$topic->slug.'">'.$topic->name.'</a></li>';
        }
        echo $allTopicsHtml;
        exit();
    }

    add_action('wp_ajax_fetch_all_topics','get_quotes_topics');
    add_action('wp_ajax_nopriv_fetch_all_topics','get_quotes_topics');

?>