<?php

    // quotes all pages url's
    $allquotes = admin_url( 'admin.php?page=quotes' );
    $addquote = admin_url( 'admin.php?page=quotes&action=addnew' );
    $editquote = admin_url( 'admin.php?page=quotes&action=edit&id=' );
    $inactivequote = admin_url( 'admin.php?page=quotes&action=inactive&id=' );

    $quoteErr = $authorErr = $topicErr = $statusErr = '';
    $quote = $author = $topic = $status = '';
    
    if($_SERVER["REQUEST_METHOD"] == "POST"){

        $quote = $_POST["quote"];
        $author = $_POST["author"];
        $topic = $_POST["topics"];
        $status = $_POST["status"];

        if(empty($_POST["quote"])){
            $quoteErr = 'Quote text is required';
        };

        if(empty($_POST["author"])){
            $author = '1';
        };

        if(empty($_POST["status"])){
            $statusErr = 'Status is required';
        };

        // insert or update quote and topics map
        if($quote && $author && $status){
            global $wpdb;
            if($_REQUEST['action']=='edit' && $_REQUEST['id']){ // updating existing quote
                $quoteId = $_REQUEST['id'];
                $wpdb->update("wp_quotes",
                    array(
                        'quotes_text' => $quote,
                        'author' => $author,
                        'status' => $status=='active' ? '1' : '0'
                    ),
                    array(
                        'id' => $quoteId
                    )
                );

                $wpdb->update("wp_topics_quotes_map",
                    array(
                        'status' => '0'
                    ),
                    array(
                        'quote_id' => $quoteId
                    )
                );

                foreach($topic as $insert_topic){
                    $checkTopic = $wpdb->get_results("select * from wp_topics_quotes_map where topic_id = $insert_topic and quote_id = $quoteId");
                    if(count($checkTopic)==0){
                        $wpdb->insert("wp_topics_quotes_map", array(
                            "topic_id" => $insert_topic,
                            "quote_id" => $quoteId,
                            "status" => '1'
                        ));
                    } else {
                        $wpdb->update("wp_topics_quotes_map", array(
                            "status" => '1'
                        ),
                        array(
                            "topic_id" => $insert_topic,
                            "quote_id" => $quoteId
                        ));
                    };
                };
                header("Location: $allquotes"); /* Redirect browser */
                exit();
            } else { // adding new quote
                $wpdb->insert("wp_quotes", array(
                    'quotes_text' => $quote,
                    'author' => $author,
                    'status' => $status=='active' ? '1' : '0'
                ));
                $newQuote = $wpdb->insert_id;
                foreach($topic as $insert_topic){
                    $wpdb->insert("wp_topics_quotes_map", array(
                        "topic_id" => $insert_topic,
                        "quote_id" => $newQuote,
                        "status" => '1'
                    ));
                };
                header("Location: $allquotes"); /* Redirect browser */
                exit();
            };
            // form is valid add new row
        };
    };

    // quotes page render
    function quotes_pages_call_function(){
        global $allquotes;
        global $addquote;
        global $editquote;

        $currentTab = '';

        if($_REQUEST['action']==''){
            $currentTab = 'allquotes';
        } else {
            $currentTab = $_REQUEST['action'];
        };

        $showEditTab = $_REQUEST['action']=="edit" ? 'block' : 'none';

        ?>
            <div class="wrap" id="wrap">
                <h2>Quotes</h2>
                <h2 class="nav-tab-wrapper">
                    <a href="<?php echo $allquotes; ?>" class="nav-tab <?php echo $_REQUEST['action']=='' ? 'nav-tab-active' : '';?>">All Quotes</a>
                    <a href="<?php echo $addquote; ?>" class="nav-tab <?php echo $_REQUEST['action']=='addnew' ? 'nav-tab-active' : '';?>">Add New Quote</a>
                    <a href="javascript:void(0)" class="nav-tab <?php echo $_REQUEST['action']=='edit' ? 'nav-tab-active' : '';?>" style="display:<?php echo $_REQUEST['action']=="edit" ? "block" : "none";?>">Edit Quote</a>
                </h2>

        <?php

        // check action and get html

        switch($currentTab){
            case 'allquotes':
                get_all_quotes();
                break;
            case 'addnew':
                add_edit_quote();
                break;
            case 'edit':
                add_edit_quote();
                break;
            case 'inactive':
                $inactiveId = $_REQUEST['id'];
                inactive_quote($inactiveId);
                get_all_quotes();
                break;
            case 'active':
                $activeId = $_REQUEST['id'];
                active_quote($activeId);
                get_all_quotes();
                break;
            default:
                get_all_quotes();
                break;
        }

        ?></div><?php
    }

    // inactive quote function
    function inactive_quote($id){
        global $wpdb;
        global $allquotes;

        $wpdb->update("wp_quotes",
            array(
                'status' => '0'
            ),
            array(
                'id' => $id
            )
        );
    }

    // active quote function
    function active_quote($id){
        global $wpdb;
        global $allquotes;

        $wpdb->update("wp_quotes",
            array(
                'status' => '1'
            ),
            array(
                'id' => $id
            )
        );
    }

    // function to fetch all quotes and show data in html
    function get_all_quotes(){
        global $editquote;
        global $inactivequote;
        global $wpdb;
        $results = $wpdb->get_results( "SELECT * FROM wp_quotes");
        ?>
        <table id="quotesListing" class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Quote</th>
                        <th>Author</th>
                        <th>Topics</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach($results as $x){
                    ?>
                    <tr>
                        <td><?php echo $x->id;?></td>
                        <td>
                            <?php echo $x->quotes_text;?>
                            <div class="row-actions">
                                <span class="edit"><a href="<?php echo $editquote.''.$x->id;?>">Edit</a></span>
                                | 
                                <span class="delete" style="display:<?php echo $x->status==1 ? 'inline-block' : 'none'; ?>;"><a href="javascript:void(0)" onclick="inactiveQuote(<?php echo $x->id; ?>)">Inactive</a></span>
                                <span class="success" style="display:<?php echo $x->status==0 ? 'inline-block' : 'none'; ?>;"><a href="javascript:void(0)" onclick="activeQuote(<?php echo $x->id; ?>)">Active</a></span>
                            </div>
                        </td>
                        <td><?php echo get_author_by_id($x->author);?></td>
                        <td><?php echo get_topics_by_quote($x->id);?></td>
                        <td><?php echo $x->status==1  ? 'Active' : 'Inactive';?></td>
                        <td><?php echo $x->created_at;?></td>
                    </tr>
                    <?php
                };
                ?>
                </tbody>
            </table>
        <?php
    }

    // function to add or edit quote with html
    function add_edit_quote(){
        global $wpdb;
        global $allquotes;
        global $quoteErr;
        global $authorErr;
        global $topicErr;
        global $statusErr;
        global $quote;
        global $author;
        global $topic;
        global $status;
        $currentTab = '';

        if($_REQUEST['action']==''){
            $currentTab = 'allquotes';
        } else {
            $currentTab = $_REQUEST['action'];
        };

        $quoteId;
        $quoteDetails;

        if($_REQUEST['action']=='edit' && $_REQUEST['id']){
            $quoteId = $_REQUEST['id'];
            $quoteDetails = get_quote_by_id($quoteId);
        };

        $allAuthors = get_all_authors();
        $allTopics = get_all_topics();

        ?>
        <div id="poststuff">
            <div id="add_new_quote" class="postbox">
                <h3 class="hndle"><span>Add New Quote</span></h3>
                <div class="inside">
                    <div class="form-wrap">
                        <form method="post" name="quoteForm" action="<?php echo $_SERVER['REQUEST_URI'];?>">
                            <div class="form-field">
                                <label><strong>Quote <span class="required">*</span></strong></label>
                                <?php
                                    $quoteText = '';
                                    if($quoteDetails && $quoteDetails->quotes_text){
                                        $quoteText = $quoteDetails->quotes_text;
                                    };
                                    if($quote){
                                        $quoteText = $quote;
                                    };
                                ?>
                                <textarea name="quote"><?php echo $quoteText; ?></textarea>
                                <span class="error"><?php echo $quoteErr; ?></span>
                            </div>
                            <div class="form-field category-add">
                                <label><strong>Author</strong></label>
                                <?php
                                    $authortext = '';
                                    if($quoteDetails && $quoteDetails->author){
                                        $authortext = $quoteDetails->author;
                                    };
                                    if($author){
                                        $authortext = $author;
                                    };
                                ?>
                                <select name="author">
                                    <option value="">Select Author</option>
                                    <?php
                                    foreach($allAuthors as $authr){
                                        ?>
                                        <option <?php echo $authortext==$authr->id ? 'selected="selected"' : ''; ?> value="<?php echo $authr->id; ?>"><?php echo $authr->name;?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <span class="error"><?php echo $authorErr; ?></span>
                            </div>
                            <div class="form-field category-add">
                                <label>
                                    <strong>Topics</strong>
                                    <a href="javascript:void(0)" class="button button-primary button-small extra" onclick="showAddTopic()">Add New</a>
                                </label>
                                <?php
                                    $topictext = array();
                                    if($quoteDetails && $quoteDetails->topics){
                                        foreach($quoteDetails->topics as $qTop){
                                            array_push($topictext, $qTop->id);
                                        };
                                    };
                                    if($topic){
                                        $topictext = $topic;
                                    };
                                    if(!$topictext){
                                        $topictext = array();
                                    };
                                ?>
                                <select name="topics[]" multiple="true" id="alltopicsselect">
                                    <option value="">Select Topics</option>
                                    <?php
                                    foreach($allTopics as $top){
                                        ?>
                                        <option <?php echo in_array($top->id, $topictext) ? 'selected=selected' : '';?> value="<?php echo $top->id; ?>"><?php echo $top->name;?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <span class="error"><?php echo $topicErr; ?></span>
                            </div>
                            <div class="form-field category-add">
                                <label><strong>Status <span class="required">*</span></strong></label>
                                <?php
                                    if($quoteDetails && $quoteDetails->status){
                                        if($quoteDetails->status==1){
                                            $statustext = 'active';
                                        } else {
                                            $statustext = 'inactive';
                                        };
                                    };
                                    if($status){
                                        $statustext = $status;
                                    };
                                ?>
                                <select name="status">
                                    <option <?php echo $statustext=='' ? 'selected="selected"' : ''; ?> value="">Select Status</option>
                                    <option <?php echo $statustext=='active' ? 'selected="selected"' : ''; ?> value="active">Active</option>
                                    <option <?php echo $statustext=='inactive' ? 'selected="selected"' : ''; ?> value="inactive">Inactive</option>
                                </select>
                                <span class="error"><?php echo $statusErr; ?></span>
                            </div>
                            <div class="form-field">
                                <input type="submit" value="<?php echo $currentTab=='edit' ? 'Update Quote' : 'Add Quote'; ?>" class="button button-primary button-large">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="add_new_topic_popup" style="display:none;">
            <div id="poststuff">
                <div id="add_new_quote" class="postbox">
                    <h3 class="hndle">
                        <span>Add New Topic</span>
                        <a href="javascript:void(0)" class="closeTopicBox" onclick="closeTopicBox()">&times;</a>
                    </h3>
                    <div class="inside">
                        <div class="form-wrap">
                            <form id="topicForm" name="topicForm" onsubmit="addnewtopic(topicForm, event)">
                                <div class="form-field">
                                    <label>Topic Name</label>
                                    <input type="text" name="newtopic">
                                    <span class="error" style="display:none;" id="topicErr">Topic name is required</span>
                                </div>
                                <div class="form-field">
                                    <input type="submit" value="Add Topic" class="button button-primary button-large">
                                </div>
                            </form>
                            <div class="topic_success" style="display:none;">
                                <i class="dashicons-before dashicons-yes"></i>
                                <p>Topic Successfully Added</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    // generic functions to get data
    function get_quote_by_id($id){
        global $wpdb;
        $quote = $wpdb->get_results( "SELECT * FROM wp_quotes where id = $id");
        $quote[0]->topics = $wpdb->get_results( "select b.id, b.name from wp_topics_quotes_map a join wp_quotes_topics b on a.topic_id = b.id where a.quote_id = $id and a.status = '1'");
        return $quote[0];
    }

    function get_all_authors(){
        global $wpdb;
        $authors = $wpdb->get_results( "SELECT id, name FROM wp_quotes_author where status = '1'");
        return $authors;
    };

    function get_all_topics(){
        global $wpdb;
        $topics = $wpdb->get_results( "SELECT id, name FROM wp_quotes_topics");
        return $topics;
    };

    function get_author_by_id($id){
        global $wpdb;
        $authors = $wpdb->get_results( "SELECT name FROM wp_quotes_author where id = $id");
        return $authors[0]->name;
    }

    function get_topics_by_quote($id){
        global $wpdb;
        $topics = $wpdb->get_results( "select b.name from wp_topics_quotes_map a join wp_quotes_topics b on a.topic_id = b.id where a.quote_id = $id and a.status = '1'");
        $quoteTopics = '<ul class="quote_topics">';
        foreach($topics as $topic){
            $quoteTopics.= '<li>'.$topic->name.'</li>';
        }
        $quoteTopics.= '</ul>';
        return $quoteTopics;
    }
     
?>
