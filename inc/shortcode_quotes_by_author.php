<?php

    function get_quotes_by_author($attrs) {
        global $wpdb;

        $authorSlug = $attrs['slug'];
        $limit = $attrs['limit'];
        $page = ($attrs['page']-1)*$limit;


        $authorResult = $wpdb->get_results("select * from wp_quotes_author where slug = '$authorSlug'");
        $authorId = $authorResult[0]->id;
        $allauthorquotes = $wpdb->get_results("select * from wp_quotes where author = $authorId limit $page, $limit");
        foreach($allauthorquotes as $quote){
            $quote->topics = $wpdb->get_results( "select b.* from wp_topics_quotes_map a join wp_quotes_topics b on a.topic_id = b.id where a.quote_id = $quote->id and a.status = '1'");
        }

        return json_encode(array(
            "quotes" => json_encode($allauthorquotes),
            "author" => $authorResult[0]
        ));
    }

    add_shortcode( 'getquoteforauthor', 'get_quotes_by_author' );


    function get_pagination_author(){
        global $wpdb;
        $authorSlug = $_POST['authorslug'];
        $perpage = $_POST['perpage'];

        $currentPageNo = $_POST['currPage'];

        $authorResult = $wpdb->get_results("select * from wp_quotes_author where slug = '$authorSlug'");
        $authorId = $authorResult[0]->id;
        $allquotescount = $wpdb->get_results("select count(*) as count from wp_quotes where author = $authorId");
        $allquotescount = $allquotescount[0]->count;
        $pages = ceil($allquotescount/$perpage);
        $pagePrevious = $currentPageNo==1 ? 1 : $currentPageNo-1;
        $pageNext = $currentPageNo==$pages ? $pages : $currentPageNo+1;
        $paginationHtml = '<ul><li><a href="'.get_site_url().'/quotes/author/'.$authorSlug.'/1">First</a></li><li><a href="'.$pagePrevious.'">&lt;</a></li>';
        for ($x = 0; $x < $pages; $x++) {
            if($x+1 == $currentPageNo){
                $paginationHtml .= '<li class="active"><a href="'.get_site_url().'/quotes/author/'.$authorSlug.'/'.($x+1).'">'.($x+1).'</a></li>';
            } else {
                $paginationHtml .= '<li><a href="'.get_site_url().'/quotes/author/'.$authorSlug.'/'.($x+1).'">'.($x+1).'</a></li>';
            };
        };
        $paginationHtml .= '<li><a href="'.get_site_url().'/quotes/author/'.$authorSlug.'/'.$pageNext.'">&gt;</a></li><li><a href="'.get_site_url().'/quotes/author/'.$authorSlug.'/'.$pages.'">Last</a></li></ul>';
        echo $paginationHtml;
        exit();
    }

    add_action('wp_ajax_fetch_pagination_author','get_pagination_author');
    add_action('wp_ajax_nopriv_fetch_pagination_author','get_pagination_author');
     
?>
