<?php

    function get_quotes_by_topic($attrs) {
        global $wpdb;

        $topicSlug = $attrs['slug'];
        $limit = $attrs['limit'];
        $page = ($attrs['page']-1)*$limit;

        $topicResult = $wpdb->get_results("select * from wp_quotes_topics where slug = '$topicSlug'");
        $topicId = $topicResult[0]->id;
        $alltopicquotes = $wpdb->get_results("select c.* from wp_topics_quotes_map a join wp_quotes_topics b on a.topic_id = b.id join wp_quotes c on a.quote_id = c.id where a.status = '1' and a.topic_id = $topicId limit $page, $limit");
        foreach($alltopicquotes as $quote){
            $quote->topics = $wpdb->get_results( "select b.* from wp_topics_quotes_map a join wp_quotes_topics b on a.topic_id = b.id where a.quote_id = $quote->id and a.status = '1'");
            $quote->authorData = $wpdb->get_results( "select a.* from wp_quotes_author a join wp_quotes b on a.id = b.author where b.id = $quote->id");
        }

        $allTopicsResult = $wpdb->get_results("select * from wp_quotes_topics order by id desc limit 20");
        return json_encode(array(
            "quotes" => json_encode($alltopicquotes),
            "topic" => $topicResult[0],
            "allTopics" => json_encode($allTopicsResult)
        ));
    }

    add_shortcode( 'getquotefortopic', 'get_quotes_by_topic' );


    function get_pagination_topic(){
        global $wpdb;
        $topicSlug = $_POST['topicslug'];
        $perpage = $_POST['perpage'];

        $currentPageNo = $_POST['currPage'];
        
        $topicResult = $wpdb->get_results("select * from wp_quotes_topics where slug = '$topicSlug'");
        $topicId = $topicResult[0]->id;
        $alltopicquotescount = $wpdb->get_results("select count(*) as count from wp_topics_quotes_map a join wp_quotes_topics b on a.topic_id = b.id join wp_quotes c on a.quote_id = c.id where a.status = '1' and a.topic_id = $topicId");
        $allquotescount = $alltopicquotescount[0]->count;
        $pages = ceil($allquotescount/$perpage);
        $pagePrevious = $currentPageNo==1 ? 1 : $currentPageNo-1;
        $pageNext = $currentPageNo==$pages ? $pages : $currentPageNo+1;
        $paginationHtml = '<ul><li><a href="'.get_site_url().'/quotes/topic/'.$topicSlug.'/1">First</a></li><li><a href="'.$pagePrevious.'">&lt;</a></li>';
        for ($x = 0; $x < $pages; $x++) {
            if($x+1 == $currentPageNo){
                $paginationHtml .= '<li class="active"><a href="'.get_site_url().'/quotes/topic/'.$topicSlug.'/'.($x+1).'">'.($x+1).'</a></li>';
            } else {
                $paginationHtml .= '<li><a href="'.get_site_url().'/quotes/topic/'.$topicSlug.'/'.($x+1).'">'.($x+1).'</a></li>';
            };
        };
        $paginationHtml .= '<li><a href="'.get_site_url().'/quotes/topic/'.$topicSlug.'/'.$pageNext.'">&gt;</a></li><li><a href="'.get_site_url().'/quotes/topic/'.$topicSlug.'/'.$pages.'">Last</a></li></ul>';
        echo $paginationHtml;
        exit();
    }

    add_action('wp_ajax_fetch_pagination_topic','get_pagination_topic');
    add_action('wp_ajax_nopriv_fetch_pagination_topic','get_pagination_topic');
     
?>
