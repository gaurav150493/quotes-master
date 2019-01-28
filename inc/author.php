<?php

    // quotes all pages url's
    $allauthor = admin_url( 'admin.php?page=authors' );
    $addauthor = admin_url( 'admin.php?page=authors&action=addnew' );
    $editauthor = admin_url( 'admin.php?page=authors&action=edit&id=' );

    $anameErr = '';
    $aname = $adob = $adod = $aprofession = $anationality = '';
    
    if($_SERVER["REQUEST_METHOD"] == "POST"){

        $aname = $_POST["aname"];
        $aimage = $_POST["aimage"];
        $adob = $_POST["adob"];
        $adod = $_POST["adod"];
        $aprofession = $_POST["aprofession"];
        $anationality = $_POST["anationality"];

        if(empty($_POST["aname"])){
            $anameErr = 'Author name is required';
        };

        if(empty($_POST["aimage"])){
            $aimage = null;
        };

        if(empty($_POST["adob"])){
            $adob = null;
        };

        if(empty($_POST["adod"])){
            $adod = null;
        };

        if(empty($_POST["aprofession"])){
            $aprofession = null;
        };

        if(empty($_POST["anationality"])){
            $anationality = null;
        };

        // insert or update author
        if($aname){
            global $wpdb;
            if($_REQUEST['action']=='edit' && $_REQUEST['id']){ // updating existing quote
                $authorId = $_REQUEST['id'];
                $wpdb->update("wp_quotes_author",
                    array(
                        'name' => $aname,
                        'image' => $aimage,
                        'date_of_birth' => $adob,
                        'date_of_death' => $adod,
                        'profession' => $aprofession,
                        'nationality' => $anationality,
                        'status' => '1'
                    ),
                    array(
                        'id' => $authorId
                    )
                );
                header("Location: $allauthor"); /* Redirect browser */
                exit();
            } else { // adding new author
                $newauthorslug = strtolower($aname);
                $newauthorslug = str_replace(' ', '-', $newauthorslug);
                $wpdb->insert("wp_quotes_author", array(
                    'name' => $aname,
                    'slug' => $newauthorslug,
                    'image' => $aimage,
                    'date_of_birth' => $adob,
                    'date_of_death' => $adod,
                    'profession' => $aprofession,
                    'nationality' => $anationality,
                    'status' => '1'
                ));
                header("Location: $allauthor"); /* Redirect browser */
                exit();
            };
            // form is valid add new row
        };
    };

    // quotes page render
    function author_pages_call_function(){
        global $allauthor;
        global $addauthor;
        global $editauthor;

        $currentTab = '';

        if($_REQUEST['action']==''){
            $currentTab = 'allauthor';
        } else {
            $currentTab = $_REQUEST['action'];
        };

        ?>
            <div class="wrap" id="wrap">
                <h2>Authors</h2>
                <h2 class="nav-tab-wrapper">
                    <a href="<?php echo $allauthor; ?>" class="nav-tab <?php echo $_REQUEST['action']=='' ? 'nav-tab-active' : '';?>">All Authors</a>
                    <a href="<?php echo $addauthor; ?>" class="nav-tab <?php echo $_REQUEST['action']=='addnew' ? 'nav-tab-active' : '';?>">Add New Author</a>
                    <a href="javascript:void(0)" class="nav-tab <?php echo $_REQUEST['action']=='edit' ? 'nav-tab-active' : '';?>" style="display:<?php echo $_REQUEST['action']=="edit" ? "block" : "none";?>">Edit Author</a>
                </h2>

        <?php

        // check action and get html

        switch($currentTab){
            case 'allauthor':
                show_all_authors();
                break;
            case 'addnew':
                add_edit_author();
                break;
            case 'edit':
                add_edit_author();
                break;
            case 'delete':
                $deleteId = $_REQUEST['id'];
                delete_author($deleteId);
                show_all_authors();
                break;
            default:
                show_all_authors();
                break;
        }

        ?></div><?php
    }

    // delete author function
    function delete_author($id){
        global $wpdb;
        global $allauthor;

        $wpdb->update("wp_quotes_author",
            array(
                'status' => '0'
            ),
            array(
                'id' => $id
            )
        );
    }

    // function to fetch all authors and show data in html
    function show_all_authors(){
        global $editauthor;
        global $wpdb;
        $results = $wpdb->get_results( "SELECT * FROM wp_quotes_author where status = '1'");
        ?>
        <table id="authorsListing" class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date of birth</th>
                        <th>Date of death</th>
                        <th>Profession</th>
                        <th>Nationality</th>
                        <th>Created at</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach($results as $x){
                    ?>
                    <tr>
                        <td><?php echo $x->name;?></td>
                        <td>
                            <?php echo $x->date_of_birth;?>
                            <div class="row-actions">
                                <span class="edit"><a href="<?php echo $editauthor.''.$x->id;?>">Edit</a></span>
                                | 
                                <span class="delete"><a href="javascript:void(0)" onclick="deleteAuthor(<?php echo $x->id; ?>)">Delete</a></span>
                            </div>
                        </td>
                        <td><?php echo $x->date_of_death;?></td>
                        <td><?php echo $x->profession;?></td>
                        <td><?php echo $x->nationality;?></td>
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
    function add_edit_author(){
        global $wpdb;

        global $allauthor;

        global $anameErr;

        global $aname;
        global $aimage;
        global $adob;
        global $adod;
        global $aprofession;
        global $anationality;

        $currentTab = '';

        if($_REQUEST['action']==''){
            $currentTab = 'allauthor';
        } else {
            $currentTab = $_REQUEST['action'];
        };

        $authorId;
        $authorDetails;

        if($_REQUEST['action']=='edit' && $_REQUEST['id']){
            global $wpdb;
            $authorId = $_REQUEST['id'];
            $authors = $wpdb->get_results( "SELECT * FROM wp_quotes_author where id = $authorId");
            $authorDetails = $authors[0];
        };

        $aname = $_POST["aname"];
        $adob = $_POST["adob"];
        $adod = $_POST["adod"];
        $aprofession = $_POST["aprofession"];
        $anationality = $_POST["anationality"];

        ?>
        <div id="poststuff">
            <div id="add_new_quote" class="postbox">
                <h3 class="hndle"><span>Add New Author</span></h3>
                <div class="inside">
                    <div class="form-wrap">
                        <form method="post" name="authorForm" action="<?php echo $_SERVER['REQUEST_URI'];?>">
                            <div class="form-field">
                                <label><strong>Name <span class="required">*</span></strong></label>
                                <?php
                                    $authornametext = '';
                                    if($authorDetails && $authorDetails->name){
                                        $authornametext = $authorDetails->name;
                                    };
                                    if($aname){
                                        $authornametext = $aname;
                                    };
                                ?>
                                <input type="text" name="aname" value="<?php echo $authornametext; ?>">
                                <span class="error"><?php echo $anameErr; ?></span>
                            </div>

                            <div class="form-field">
                                <label><strong>Image</strong></label>
                                <?php
                                    $authorimgtext = '';
                                    if($authorDetails && $authorDetails->image){
                                        $authorimgtext = $authorDetails->image;
                                    };
                                    if($aimage){
                                        $authorimgtext = $aimage;
                                    };
                                ?>
                                <input type="text" name="aimage" value="<?php echo $authorimgtext; ?>">
                            </div>

                            <div class="form-field">
                                <label><strong>Date of birth</strong></label>
                                <?php
                                    $adobtext = '';
                                    if($authorDetails && $authorDetails->date_of_birth){
                                        $adobtext = $authorDetails->date_of_birth;
                                    };
                                    if($adob){
                                        $adobtext = $adob;
                                    };
                                ?>
                                <input type="text" id="adob" name="adob" autocomplete="off" value="<?php echo $adobtext; ?>">
                            </div>

                            <div class="form-field">
                                <label><strong>Date of death</strong></label>
                                <?php
                                    $adodtext = '';
                                    if($authorDetails && $authorDetails->date_of_death){
                                        $adodtext = $authorDetails->date_of_death;
                                    };
                                    if($adod){
                                        $adodtext = $adod;
                                    };
                                ?>
                                <input type="text" id="adod" name="adod" autocomplete="off" value="<?php echo $adodtext; ?>">
                            </div>

                            <div class="form-field">
                                <label><strong>Profession</strong></label>
                                <?php
                                    $aprofessiontext = '';
                                    if($authorDetails && $authorDetails->profession){
                                        $aprofessiontext = $authorDetails->profession;
                                    };
                                    if($adob){
                                        $aprofessiontext = $aprofession;
                                    };
                                ?>
                                <input type="text" name="aprofession" value="<?php echo $aprofessiontext; ?>">
                            </div>

                            <div class="form-field">
                                <label><strong>Nationality</strong></label>
                                <?php
                                    $anationalitytext = '';
                                    if($authorDetails && $authorDetails->nationality){
                                        $anationalitytext = $authorDetails->nationality;
                                    };
                                    if($anationality){
                                        $anationalitytext = $anationality;
                                    };
                                ?>
                                <input type="text" name="anationality" value="<?php echo $anationalitytext; ?>">
                            </div>

                            <div class="form-field">
                                <input type="submit" value="<?php echo $currentTab=='edit' ? 'Update Author' : 'Add Author'; ?>" class="button button-primary button-large">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
?>
