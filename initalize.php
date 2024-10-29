<?php
/**
* Plugin Name: Advance Custom Import and Export
* Plugin URI:
* Description: ACIX , Advance custom import and export plugin enables an easier way to import and export the posts. Plugins enables a good way to create backups.
* Version: 1.0
* Author: Khc Technologies OPC Private Limited
* Author URI: http://khctechnologies.com
* License: GPLv2
*/

defined( 'ABSPATH' ) or die( 'Access Prohibited, No script kiddies please!' );

if(!class_exists('acix_init')):

    class acix_init
    {
        public $main_page; //main page object
        public $import_page; //import page object
        public $export_page; //export page object
        public $settings_page; //settings page object

        public $ajax_url; //Ajax Based URL
        public $plugin_url,$plugin_dir;

        public $_message;

        private $posted_ptype,$posted_fields,$posted_fields_order,$author_name;
        private $page,$total_record_at_once;
        private $uploaded_csv_obj;
        private $uploaded_txt_mode ,$uploaded_txt_obj;//New field added in version 1.1
        private $generate_order_file;//New field added in version 1.1

        public function __construct()
        {
            $this->plugin_url = plugin_dir_url( __FILE__ );

            $this->plugin_dir = plugin_dir_path( __FILE__ );

            $this->ajax_url = admin_url( 'admin-ajax.php' );

            $this->page = 1;

            $this->total_record_at_once = 10;

            $this->addActions();//initiate method

            $this->addAjaxActions();//initiate method
        }//constructor ends here

        public function addActions()
        {
            add_action('admin_enqueue_scripts', array($this, 'setup_header') );

            add_action("admin_menu", array($this, "setup_admin_menus") );

            add_action("admin_init", array($this, "setup_post_actions") );
        }

        public function addAjaxActions()
        {
            //ajax based event
            add_action( 'wp_ajax_put_csv', array( $this,'put_csv' ) );
            add_action( 'wp_ajax_nopriv_put_csv', array( $this,'put_csv' ) );

            add_action( 'wp_ajax_get_csv', array( $this,'get_csv' ) );
            add_action( 'wp_ajax_nopriv_get_csv', array( $this,'get_csv' ) );
        }

        public function setup_header()
        {
            wp_enqueue_script('jquery');

            wp_enqueue_script('jquery-ui-core');

            wp_enqueue_script('jquery-ui-droppable');

            wp_enqueue_script('jquery-ui-sortable');

            wp_enqueue_script( 'acix_admin_custom_script', $this->plugin_url . '/js/script.js', array(), '1.0.0', true);

            wp_register_style( 'acix_css', $this->plugin_url . '/css/styles.css', false, '1.0.0' );

            wp_enqueue_style( 'acix_css' );

            wp_register_style( 'acix_admin_css', $this->plugin_url . '/css/admin-style.css', false, '1.0.0' );

            wp_enqueue_style( 'acix_admin_css' );
        }

        public function setup_admin_menus()
        {
            $this->main_page = add_menu_page( 'Introduction', 'Acix Tools', 'manage_options', 'acix_main', array($this, 'acix_main_page'), $this->plugin_url . '/images/icon_hover.png' );
            //$page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position

            $this->import_page = add_submenu_page('acix_main', 'Import CSV', 'Import', 'manage_options','acix_import', array($this, 'acix_import_page') );
            //add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function)

            $this->export_page = add_submenu_page('acix_main', 'Export CSV', 'Export', 'manage_options','acix_export', array($this, 'acix_export_page') );

            $this->settings_page = add_submenu_page('acix_main', 'Acix Settings', 'Settings', 'manage_options','acix_settings', array($this, 'acix_settings_page') );
        }

        public function acix_menu()
        {
            include_once( $this->plugin_dir . '/pages/acix_menu.php');
        }

        public function acix_main_page()
        {
            include_once( $this->plugin_dir . '/pages/acix_main_page.php');
        }

        public function acix_import_page()
        {
            include_once( $this->plugin_dir . '/pages/acix_import.php');
        }

        public function acix_export_page()
        {
            include_once( $this->plugin_dir . '/pages/acix_export.php');
        }

        public function acix_settings_page()
        {
            include_once( $this->plugin_dir . '/pages/acix_settings.php');
        }


        /**
        *   Creating Admin Notifications
        *
        */
        public function update_notice()
        {
            echo '<div class="updated notice"><p><strong>'. __($this->_message) .'</strong></p></div>';
        }

        public function error_notice()
        {
            echo '<div class="error notice"><p><strong>'.  __($this->_message) .'</strong></p></div>';
        }

        public function auth_failed_notice()
        {
            echo '<div class="update-nag notice"><p><strong>'.  __($this->_message) .'</strong></p></div>';
        }

        /**
        *   Processing function will start from below
        *
        */
        public function filter_posttypes($post_types)
        {
            $delete_post_types = array("page","attachment", "revision", "nav_menu_item");

            // Search for the array key and unset
            foreach($delete_post_types as $key){
                $keyToDelete = array_search($key, $post_types);
                unset($post_types[$keyToDelete]);
            }

            return $post_types;
        }

        public function get_meta_fields($post_type = 'post')
        {
            $default_fields = array("post_title" => "default", "post_content" => "default", "post_excerpt" => "default", "post_status" => "default", "post_name" => "default");
            //http://wordpress.stackexchange.com/questions/58834/echo-all-meta-keys-of-a-custom-post-type
            global $wpdb;

            $query = "
                SELECT DISTINCT($wpdb->postmeta.meta_key)
                FROM $wpdb->posts
                LEFT JOIN $wpdb->postmeta
                ON $wpdb->posts.ID = $wpdb->postmeta.post_id
                WHERE $wpdb->posts.post_type = '%s'
                AND $wpdb->postmeta.meta_key != ''
                AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)'
                AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'
            ";

            $meta_keys = $wpdb->get_col($wpdb->prepare($query, $post_type));

            $kv_array = array();

            foreach($meta_keys as $meta_key)
            {
                $kv_array[$meta_key] = 'meta_field';
            }

            $meta_keys = array_merge($default_fields,$kv_array);

            return $meta_keys;
        }

        public function get_taxonomies($post_type = 'post')
        {
            //https://codex.wordpress.org/Function_Reference/get_object_taxonomies

            $taxonomy_names = get_object_taxonomies( $post_type );

            $kv_array = array();

            foreach($taxonomy_names as $taxonomy_name)
            {
                $kv_array[$taxonomy_name] = 'taxonomy';
            }

            return $kv_array;
        }

        /**
        * Exporting Function start here
        */
        private function total_pages_needed($p_type,$postsperpage)
        {
            //lets count total posts
            $total_posts_exists = 0;

            $count_post = wp_count_posts($p_type);

            $total_posts_exists = $count_post->publish + $count_post->future + $count_post->draft + $count_post->pending + $count_post->private ;

            $total_posts_exists += $count_post->trash + $count_post->auto-draft + $count_post->inherit ;

            return $pages_needed = ceil($total_posts_exists / $postsperpage) ;
        }

        public function export_function()
        {
            $this->call_export_function($this->posted_ptype, $this->posted_fields, $this->posted_fields_order, $this->generate_order_file);//calling ajax based function
        }

        public function call_export_function($post_type,$fields,$fields_order,$generate_order_file)
        {
            $nonce = wp_create_nonce( 'acix_generate_csv_' . get_current_blog_id());

            //create filename of CSV
            $prefix = "acix" . $post_type . $nonce . strtotime(date("mdy")) . mt_rand();

            $fname =  $prefix . ".csv";

            $txt_file = $prefix . ".txt";

            include_once( $this->plugin_dir . '/pages/scripts/export_footer.php');
        }

        public function put_csv()
        {
            try
            {
                $nonce = $_POST['nonce'];
                $item_nonce = 'acix_generate_csv_' . get_current_blog_id();
                if ( ! wp_verify_nonce( $nonce, $item_nonce ) )
                {
                    $return_array = array();

                    $return_array['error'] = true;

                    $return_array['error_msg'] = "Security warning, Breach detected...";

                    die();
                }
                else
                {
                    $text_filename = $_POST['text_filename'];

                    $return_array = array();

                    $upload_dir = wp_upload_dir();

                    $filename = $upload_dir['path'] . "/" . $_POST['fname'];

                    $fileurl = $upload_dir['url'] . "/" . $_POST['fname'];

                    $file = fopen($filename, "a+");

                    $p_type = $_POST['post_type'];

                    $postsperpage = $_POST['total'];

                    $pages_needed = $this->total_pages_needed($p_type,$postsperpage);

                    $paged = isset($_POST['page']) ? $_POST['page'] : 1;

                    $args = array(
                        'post_type' => $p_type,
                        'posts_per_page' => $postsperpage,
                        'paged' => $paged
                    );

                    $list = array();

                    $fields_order = explode(",",$_POST['fields_order']);

                    $fields = json_decode(stripslashes($_POST['fields']));

                    $wp_query = new WP_Query($args);

                    if($wp_query->have_posts()) :
                        while($wp_query->have_posts()) : $wp_query->the_post();
                            global $post;
                            $data = array();

                            //lets get all fields one by one
                            foreach($fields as $field)
                            {
                                //if field is default field
                                if($field->type == 'default')
                                {
                                    if ($field->field == 'post_title') $data['post_title'] = sanitize_text_field(wp_strip_all_tags($post->post_title));

                                    if ($field->field == 'post_content') $data['post_content'] = sanitize_text_field(get_the_content());

                                    if ($field->field == 'post_excerpt') $data['post_excerpt'] = sanitize_text_field(get_the_excerpt());

                                    if ($field->field == 'post_status') $data['post_status'] = sanitize_text_field(get_post_status(get_the_ID()));

                                    if ($field->field == 'post_name') $data['post_name'] = sanitize_text_field(wp_strip_all_tags($post->post_name));
                                }
                                elseif( $field->type == 'meta_field' )
                                {
                                    $data[$field->field] = sanitize_text_field(get_post_meta(get_the_ID(), $field->field, true));
                                }
                                elseif($field->type == 'taxonomy')
                                {
                                    //Returns Array of Term Names for "my_taxonomy"
                                    $term_list = wp_get_post_terms(get_the_ID(), $field->field, array("fields" => "names"));

                                    $term_list = implode(',', $term_list);

                                    $data[$field->field] = $term_list;
                                }
                            }

                            $row = array();
                            //lets set them in order
                            foreach($fields_order as $field_order)
                            {
                                $row[$field_order] = $data[$field_order];
                            }

                            $list[] = $row;
                        endwhile;
                    endif;

                    foreach ($list as $line)
                    {
                        fputcsv($file,$line);
                    }

                    fclose($file);

                    if($paged < $pages_needed )
                    {
                        $return_array['CSV_DONE'] = false;
                        $return_array['page'] = $paged + 1;
                    }
                    else if($paged == $pages_needed)
                    {
                        $return_array['CSV_DONE'] = true;
                    }//lets end generating

                    //after csv file is generated lets generate the order file
                    if($paged == $pages_needed) :
                        //new field added for generating order file, added in version 1.1
                        $generate_order_file = $_POST['generate_order_file'];
                        if($generate_order_file == 'Yes'):

                            $txt_filename = $upload_dir['path'] . "/" . $text_filename;

                            $txt_fileurl = $upload_dir['url'] . "/" . $text_filename;

                            $write_data = array();

                            $write_data['is_file'] = true;

                            $write_data['fields_order'] = $_POST['fields_order'];

                            $write_data['fields'] = $_POST['fields'];

                            $txt_file = fopen($txt_filename, "a+");

                            $txt_data = base64_encode(serialize($write_data));

                            fwrite($txt_file, $txt_data);

                            fclose($txt_file);

                            $return_array['has_order_file'] = true;

                            $return_array['txt_fileurl'] = $txt_fileurl;
                        endif;
                    endif;

                    $return_array['begun'] = false ;

                    $return_array['fileurl'] = $fileurl ;
                }
                echo json_encode($return_array);

                die();
            }catch (Exception $e){
                $return_array = array();

                $return_array['error'] = true;

                $return_array['error_msg'] = "Exception: " .$e->getMessage();
                die();
            }
        }
        /**
        * Exporting Function ends here
        */

        /**
        * Importing Function starts here
        */
        public function import_function()
        {
            if($this->uploaded_txt_mode)
            {
                //version 1.1

                $txt_mode = $this->uploaded_txt_mode;

                $uploaded_txt_file = $this->uploaded_txt_obj['file'];

                $text_handle = fopen($uploaded_txt_file, "rb");

                $content = fgets($text_handle, filesize($uploaded_txt_file) + 1);

                fclose($text_handle);

                $read_data = unserialize(base64_decode($content));//un serialize the data

                $is_file = $read_data['is_file'];

                if($is_file)
                {
                    $this->posted_fields_order = $read_data['fields_order'];

                    $this->posted_fields = $read_data['fields'];
                }
            }

            $this->call_import_function($this->posted_ptype, $this->posted_fields, $this->posted_fields_order, $this->uploaded_csv_obj);//calling ajax based function
        }

        public function call_import_function($post_type,$fields,$fields_order,$upld_file)
        {
            $nonce = wp_create_nonce( 'acix_import_csv_' . get_current_blog_id());

            $uploaded_file = $upld_file['file'];

            $author_name = $this->author_name;

            include_once( $this->plugin_dir . '/pages/scripts/import_footer.php');
        }

        public function get_csv()
        {
            try
            {
                $nonce = $_POST['nonce'];
                $item_nonce = 'acix_import_csv_' . get_current_blog_id();
                if ( ! wp_verify_nonce( $nonce, $item_nonce ) )
                {
                    $return_array = array();

                    $return_array['error'] = true;

                    $return_array['error_msg'] = "Security warning, Breach detected...";

                    die();
                }
                else {
                    $return_array = array();

                    $p_type = $_POST['post_type'];

                    $author_name = $_POST['author_name'];

                    $filename = stripslashes($_POST['uploaded_file']);

                    $fields_order = explode(",", $_POST['fields_order']);

                    $fields = json_decode(stripslashes($_POST['fields']));

                    $handle = fopen($filename, "r");

                    $posts_writtern = array();

                    if ($handle) {
                        while (!feof($handle))
                        {
                            $row = fgetcsv($handle, 4096);
                            // Process buffer here..

                            if(!empty($row) && isset($row))
                            {
                                $list = array();

                                $i = 0;
                                //lets get the order of imported fields
                                foreach($fields_order as $field_order)
                                {
                                    $list[$field_order] = $row[$i];
                                    $i++;
                                }

                                //lets process the fields and its data
                                // Create post object
                                $my_post = array(
                                    'post_title'    => isset($list['post_title']) ? sanitize_text_field(wp_strip_all_tags($list['post_title'])) : '',
                                    'post_name'     => isset($list['post_name']) ? sanitize_text_field(wp_strip_all_tags($list['post_name'])) : '',
                                    'post_content'  => isset($list['post_content']) ? sanitize_text_field($list['post_content']) : '',
                                    'post_excerpt'  => isset($list['post_excerpt']) ? sanitize_text_field($list['post_excerpt']) : '',
                                    'post_status'   => isset($list['post_status']) ? sanitize_text_field($list['post_status']) : 'publish',
                                    'post_type'     => $p_type,
                                    'post_author' => $author_name,
                                );

                                // Insert the post into the database
                                $inserted_post_id = wp_insert_post( $my_post );

                                $posts_writtern[] = sanitize_text_field(wp_strip_all_tags($list['post_title']));

                                foreach($fields as $field)
                                {
                                    if( $field->type == 'meta_field' )
                                    {
                                        add_post_meta($inserted_post_id, $field->field, $list[$field->field]);
                                    }
                                    else if($field->type == 'taxonomy')
                                    {
                                        $taxonomies = explode(",",$list[$field->field]);

                                        if(!empty($taxonomies))
                                        {
                                            foreach($taxonomies as $tax)
                                            {
                                                if(isset($tax) && $tax!="")
                                                {
                                                    if (!term_exists($tax, $field->field)) {
                                                        $cid = wp_insert_term($tax, $field->field);
                                                    } else {
                                                        $term = get_term_by('name', $tax, $field->field);
                                                        $cid = $term->term_id;
                                                    }

                                                    wp_set_object_terms($inserted_post_id, array($cid), $field->field);
                                                }
                                            }
                                        }//more than one categories to insert
                                    }
                                }
                            }//endof main if thats check whether row is empty or what
                        }
                        fclose($handle);
                    }

                    $return_array['post_type'] = $p_type;
                    $return_array['total_posts'] = count($posts_writtern);
                    $return_array['posts_writtern'] = implode("<br />",$posts_writtern);
                    $return_array['uploaded_file'] = $filename;
                }
                echo json_encode($return_array);die();
            }catch (Exception $e){
                $return_array = array();

                $return_array['error'] = true;

                $return_array['error_msg'] = "Exception: " .$e->getMessage();
                die();
            }
        }
        /**
        * Importing Function ends here
        */

        public function setup_post_actions()
        {
            if (isset($_POST["acix_settings"]) && $_POST["acix_settings"]!="")
            {
                try
                {
                    // Do the saving
                    $save_item_nonce = 'acix_settings_' . get_current_blog_id();

                    $nonce = $_POST['acix_settings_nonce'];

                    if ( ! wp_verify_nonce( $nonce, $save_item_nonce ) )
                    {
                        //security check failed
                        $this->_message = "Security warning, Breach detected...";
                        add_action( 'admin_notices', array($this, 'auth_failed_notice'));
                    }
                    else
                    {
                        $this->_message = "Settings updated";

                        update_option("acix_post_type", $_POST['p_type']);//main option updated

                        add_action( 'admin_notices', array($this, 'update_notice'));
                    }
                }catch (Exception $e){

                        $this->_message = "Exception: " .$e->getMessage();

                        add_action( 'admin_notices', array($this, 'error_notice'));
                }
            }//process settings form
            else if (isset($_POST["export_btn"]) && $_POST["export_btn"]!="")
            {
                try
                {
                    $export_nonce = 'acix_export_' . get_current_blog_id();

                    $nonce = $_POST['export_nonce'];

                    if (!wp_verify_nonce($nonce, $export_nonce)) {
                        //security check failed
                        $this->_message = "Security warning, Breach detected...";
                        add_action('admin_notices', array($this, 'auth_failed_notice'));
                    }else {
                        $p_type = get_option("acix_post_type");
                        if ($p_type == '') {
                            $this->_message = "Please select the post type <a href='" . menu_page_url('acix_settings', false) . "'>here</a> to export";
                            add_action('admin_notices', array($this, 'error_notice'));
                        } else {
                            $selected_fields_order = $_POST['selected_fields_order'];
                            $selected_fields = $_POST['selected_fields'];

                            //generate order file , Version 1.1
                            $generate_order_file = $_POST['generate_order_file'];

                            if ($generate_order_file != 'Yes') {
                                $generate_order_file = 'No';
                            }

                            //lets use these datas to generate csv
                            $this->posted_ptype = $p_type;
                            $this->posted_fields = $selected_fields;
                            $this->posted_fields_order = $selected_fields_order;
                            $this->generate_order_file = $generate_order_file;//Version 1.1

                            if ($selected_fields == '') {
                                $this->_message = "Please select atleast one field to export. <a href='" . menu_page_url('acix_export', false) . "'>Try again</a> to export";
                                add_action('admin_notices', array($this, 'error_notice'));
                            } else {
                                $this->_message = "Seat and Have Fun!!! Currently exporting...";
                                add_action('admin_notices', array($this, 'update_notice'));

                                add_action('admin_footer', array($this, 'export_function' ));
                            }//lets start exporting the posts
                        }
                    }
                }catch (Exception $e){

                    $this->_message = "Exception: " .$e->getMessage();

                    add_action( 'admin_notices', array($this, 'error_notice'));
                }
            }//process export form
            else if(isset($_POST["import_btn"]) && $_POST["import_btn"]!="")
            {
                try
                {
                    $import_nonce = 'acix_import_' . get_current_blog_id();

                    $nonce = $_POST['import_nonce'];

                    if (!wp_verify_nonce($nonce, $import_nonce)) {
                        //security check failed
                        $this->_message = "Security warning, Breach detected...";
                        add_action('admin_notices', array($this, 'auth_failed_notice'));
                    }else {
                        $p_type = get_option("acix_post_type");

                        if ($p_type == '') {
                            $this->_message = "Please select the post type <a href='" . menu_page_url('acix_settings', false) . "'>here</a> to import";
                            add_action('admin_notices', array($this, 'error_notice'));
                        } else {
                            $selected_fields_order = $_POST['selected_fields_order'];
                            $selected_fields = $_POST['selected_fields'];

                            //lets use these datas to generate csv
                            $this->posted_ptype = $p_type;
                            $this->posted_fields = $selected_fields;
                            $this->posted_fields_order = $selected_fields_order;

                            //author_name version 1.1
                            $author_name = $_POST['author_name'];

                            $this->author_name = $author_name;

                            if($author_name == '')
                            {
                                $this->_message = "Please select author to use for imported data.";
                                add_action('admin_notices', array($this, 'error_notice'));
                            }
                            else if ( empty($_FILES['imported_csv']['name']))
                            {
                                $this->_message = "Please upload your csv file to import.";
                                add_action('admin_notices', array($this, 'error_notice'));
                            }
                            else if(empty($_FILES['imported_order_file']['name']) && $selected_fields == '')//version 1.1
                            {
                                $this->_message = "Please upload order file or select atleast one field to import. <a href='" . menu_page_url('acix_import', false) . "'>Try again</a> to import";
                                add_action('admin_notices', array($this, 'error_notice'));
                            }
                            else{
                                $fileOBJ = $_FILES['imported_csv'];
                                $mime =  array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');
                                $uploaded_file = wp_check_filetype($fileOBJ['name']);

                                if( in_array($uploaded_file['type'],$mime) )
                                {
                                    if ( ! function_exists( 'wp_handle_upload' ) ) {
                                        require_once( ABSPATH . 'wp-admin/includes/file.php' );
                                    }

                                    $upload_overrides = array( 'test_form' => false );

                                    $movefile = wp_handle_upload( $fileOBJ, $upload_overrides );

                                    if ( $movefile && !isset( $movefile['error'] ) )
                                    {
                                        $this->uploaded_csv_obj = $movefile ;

                                        //lets upload json file
                                        if(! empty($_FILES['imported_order_file']['name']))//version 1.1
                                        {
                                            //lets upload json file
                                            $txt_fileOBJ = $_FILES['imported_order_file'];

                                            $text_mime =  array('text/plain');

                                            $uploaded_text_file = wp_check_filetype($txt_fileOBJ['name']);

                                            if( in_array($uploaded_text_file['type'],$text_mime) )
                                            {
                                                $move_textfile = wp_handle_upload( $txt_fileOBJ, $upload_overrides );

                                                if ( $move_textfile && !isset( $move_textfile['error'] ) )
                                                {
                                                    $this->uploaded_txt_mode = true;

                                                    $this->uploaded_txt_obj = $move_textfile ;//New field added in version 1.1

                                                    $this->_message = "Seat and Have Fun!!! Currently importing...";

                                                    add_action('admin_notices', array($this, 'update_notice'));

                                                    add_action('admin_footer', array($this, 'import_function' ));
                                                }
                                                else
                                                {
                                                    /**
                                                     * Error generated by _wp_handle_upload()
                                                     * @see _wp_handle_upload() in wp-admin/includes/file.php
                                                     */
                                                    $this->_message = $move_textfile['error'];
                                                    add_action('admin_notices', array($this, 'error_notice'));
                                                }//end of movefile
                                            }
                                            else
                                            {
                                                $this->_message = "Please upload valid text file to import.";
                                                add_action('admin_notices', array($this, 'error_notice'));
                                            }
                                        }
                                        else
                                        {
                                            $this->_message = "Seat and Have Fun!!! Currently importing...";
                                            add_action('admin_notices', array($this, 'update_notice'));

                                            add_action('admin_footer', array($this, 'import_function' ));
                                        }
                                    }
                                    else
                                    {
                                        /**
                                         * Error generated by _wp_handle_upload()
                                         * @see _wp_handle_upload() in wp-admin/includes/file.php
                                         */
                                        $this->_message = $movefile['error'];
                                        add_action('admin_notices', array($this, 'error_notice'));
                                    }//end of movefile
                                }
                                else {
                                    $this->_message = "Please upload valid csv file to import.";
                                    add_action('admin_notices', array($this, 'error_notice'));
                                }
                            }//end of innerif
                        }
                    }
                }catch (Exception $e){

                    $this->_message = "Exception: " .$e->getMessage();

                    add_action( 'admin_notices', array($this, 'error_notice'));
                }
            }
        }
    }

//making and object to run
$GLOBALS['acix_obj'] = new acix_init();

endif;
?>