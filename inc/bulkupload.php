<?php

    $uploadStatus;
    $uploadErr;
    $fileContent = array();
    $fileheader;
    $filebody = array();
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(empty($_FILES['quotesbulkdatafile']['name'])){
            $uploadStatus = 'Please select a file to upload';
        } else {
            $fileMime = explode('.', $_FILES['quotesbulkdatafile']['name'])[1];
            if($fileMime=='csv'){
                $file = fopen($_FILES['quotesbulkdatafile']['tmp_name'],"r");
                for ($i = 0; $row = fgetcsv($file); ++$i) {
                    $currRow = '';
                    for($x = 0; $x < count($row); $x++){
                        $currRow = $currRow.'---'.$row[$x];
                    }
                    $currRow = substr($currRow, 3);
                    array_push($fileContent, $currRow);
                }
                fclose($file);
                if($fileContent[0]=='Quote Text---Likes---Author Name---Author Image---Author DOB---Author DOD---Author Profession---Author Nationality---Topic'){
                    $uploadStatus = 'done';
                } else {
                    $uploadStatus = 'Invalid CSV format. Please download valid sample format and re-upload.';
                };
            } else{
                $uploadStatus = 'Please upload CSV File';
            };
        };

        if($uploadStatus=='done'){
            for($x=0; $x<count($fileContent); $x++){
                if(!empty($fileContent[$x])){
                    $fileContent[$x] = explode('---', $fileContent[$x]);
                    if($x==0){
                        $fileheader = $fileContent[$x];
                    } else {
                        $currRow = array();
                        for($i=0; $i<count($fileContent[$x]); $i++){
                            $currRow[$fileheader[$i]] = $fileContent[$x][$i];
                            if($i==count($fileContent[$x])-1){
                                $currRow[$fileheader[$i]] = $fileContent[$x][$i].''.$fileContent[$x][$i+1];
                            };
                        };
                        array_push($filebody, $currRow);
                    };
                };
            };
            upload_bulk_data($filebody);
        } else {
            $uploadErr = $uploadStatus;
        };
    }

    // bulk upload page callback function
    function bulkupload_pages_call_function(){
        global $uploadErr;

        ?>
        <div class="wrap" id="wrap">
            <div id="quotes_bulk_msg" class="updated notice is-dismissible" style="display:<?php echo $_REQUEST['action']=='success' ? 'block' : 'none'; ?>;">
                <p>Quotes Added Successfully.</p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
            </div>
            <div id="quotes_bulk_msg" class="error is-dismissible" style="display:<?php echo $_REQUEST['action']=='error' ? 'block' : 'none'; ?>;">
                <p style="color:#cd0000;">Quote text is required in all rows.</p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
            </div>
            <div id="poststuff">
                <div id="add_quote_bulk" class="postbox">
                    <h3 class="hndle"><span>Bulk Upload Quotes</span></h3>
                    <div class="inside">
                        <p>Browse and choose a CSV file to upload (Format should be like <a target="_blank" href="http://mylofamily.com/wp-content/uploads/2019/01/sample.csv">sample CSV</a> format), Then Click Upload</p>
                        <div class="form-wrap">
                            <form method="post" enctype="multipart/form-data" name="bulkuploadForm" action="<?php echo $_SERVER['REQUEST_URI'];?>">
                                <div class="form-field">
                                    <label for="import-file">Choose a file to upload:&nbsp;
                                        <input type="file" id="import-file" name="quotesbulkdatafile">
                                    </label>
                                    <span class="error"><?php echo $uploadErr; ?></span>
                                </div>
                                <div class="form-field">
                                    <input type="submit" value="Upload" class="button button-primary button-large">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    // date convert function
    function convertDate($date){
        if($date){
            $splittedDate = explode('/', $date);
            $day = $splittedDate[0];
            $month = $splittedDate[1];
            $year = $splittedDate[2];
            $newDate = $year.'-'.$month.'-'.$day;
            return date("Y-m-d", strtotime($newDate));
        } else {
            return '';
        };
    }

    // upload bulk data function
    function upload_bulk_data($data){
        global $wpdb;
        global $uploadStatus;

        for($i=0; $i<count($data); $i++){
            if(!empty($data[$i])){
                if(empty($data[$i]['Quote Text'])){
                    $uploadErr = true;
                    break;
                } else {
                    $currAuthor;
                    $currTopics = array();
        
                    // check if user exists or insert
                    if($data[$i]['Author Name']){
                        $isAuthorExist = $wpdb->get_results("select * from wp_quotes_author where name = '".$data[$i]['Author Name']."'");
                        if(count($isAuthorExist)>0){
                            $currAuthor = $isAuthorExist[0]->id;
                        } else {
                            $newauthorslug = strtolower($data[$i]['Author Name']);
                            $newauthorslug = trim($newauthorslug);
                            $newauthorslug = str_replace(' ', '-', $newauthorslug);
                            $wpdb->insert("wp_quotes_author", array(
                                'name' => trim($data[$i]['Author Name']),
                                'slug' => $newauthorslug,
                                'image' => $data[$i]['Author Image'],
                                'date_of_birth' => !empty($data[$i]['Author DOB']) ? convertDate($data[$i]['Author DOB']) : null,
                                'date_of_death' => !empty($data[$i]['Author DOD']) ? convertDate($data[$i]['Author DOD']) : null,
                                'profession' => $data[$i]['Author Profession'],
                                'nationality' => $data[$i]['Author Nationality'],
                                'status' => '1'
                            ));
                            $currAuthor = $wpdb->insert_id;
                        };
                    } else {
                        $currAuthor = 1;
                    };
        
                    // check if topics exists or insert and topic map
                    if($data[$i]['Topic']){
                        $topicArr = explode(',', $data[$i]['Topic']);
                        for($x = 0; $x < count($topicArr); $x++){
                            $topicArr[$x]=trim($topicArr[$x]);
                            $isTopicExist = $wpdb->get_results("select * from wp_quotes_topics where name = '".$topicArr[$x]."'");
                            if(count($isTopicExist)>0){
                                array_push($currTopics, $isTopicExist[0]->id);
                                $currentTopicId = $isTopicExist[0]->id;
                            } else {
                                $newtopicslug = strtolower($topicArr[$x]);
                                $newtopicslug = trim($newtopicslug);
                                $newtopicslug = str_replace(' ', '-', $newtopicslug);
                                $wpdb->insert("wp_quotes_topics", array(
                                    'name' => $topicArr[$x],
                                    'slug' => $newtopicslug
                                ));
                                array_push($currTopics, $wpdb->insert_id);
                                $currentTopicId = $wpdb->insert_id;
                            };
                        };
                    };
        
                    // insert quote
                    $wpdb->insert("wp_quotes", array(
                        'quotes_text' => $data[$i]['Quote Text'],
                        'author' => $currAuthor,
                        'likes' => $data[$i]['Likes'],
                        'status' => '1'
                    ));
                    $currentQuoteId = $wpdb->insert_id;
                    for($x = 0; $x < count($currTopics); $x++){
                        $wpdb->insert("wp_topics_quotes_map", array(
                            'topic_id' => $currTopics[$x],
                            'quote_id' => $currentQuoteId,
                            'status' => '1'
                        ));
                    };
                };
            };
        };
        if($uploadErr==true){
            header("Location: ".admin_url( 'admin.php?page=bulk-upload&action=error' ));
            exit();
        } else {
            header("Location: ".admin_url( 'admin.php?page=bulk-upload&action=success' ));
            exit();
        };
    }
?>
