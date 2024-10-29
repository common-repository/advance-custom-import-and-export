<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $acix_obj;?>
<div class="wrap">
    <h2>Import CSV</h2>

    <?php
    $p_type = get_option("acix_post_type");

    $meta_fields = $acix_obj->get_meta_fields($p_type);

    $t_names = $acix_obj->get_taxonomies($p_type);

    $meta_fields = array_merge($meta_fields,$t_names);
    ?>

    <?php if(isset($_POST['import_btn']) && $_POST['import_btn'] != ""): ?>
        <div class="column" id="column2"></div>
    <?php else: ?>
        <form method="POST" enctype="multipart/form-data">

            <div class="column-container">
                <p class="drag-info">Drag the fields from here to right side.
                    Order of fields in csv and dragged fields must need to be same.
                    These are the fields found for selected post type <a href="<?php echo menu_page_url('acix_settings', false); ?>">here</a>.</p>

                <div class="column" id="column1">

                    <?php foreach ($meta_fields as $meta_field => $type) : ?>
                        <div class="dragbox" id="<?= $meta_field; ?>" data-type="<?= $type ?>">
                            <h2><?= $meta_field; ?></h2>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>

            <div class="column-container">
                <p class="drag-info">Author:
                <select name="author_name" id="author_name">
                    <option value="">Select Author name</option>
                <?php
                    $args = array(
                                    'orderby'       => 'name',
                                    'order'         => 'ASC',
                                    'optioncount'   => false,
                                    'exclude_admin' => false,
                                    'show_fullname' => false,
                                    'hide_empty'    => false,
                                    'echo'          => false,
                                    'html'        => false
                                 );

                    $list_authors = wp_list_authors($args);

                    $authors = explode(",",$list_authors);

                    foreach($authors as $author)
                    {
                        $user = get_user_by( 'login', $author );

                        echo '<option value="'.$user->ID.'">'.$author.'</option>';
                    }
                ?>
                </select>
                </p>

                <p class="drag-info">CSV File :<input type="file" name="imported_csv" /></p>

                <p class="drag-info">Choose Order File :<input type="file" name="imported_order_file" /><div style="font-size:12px;padding-left: 20px;">Text file have higher priority than your selected fields. So avoid uploading text file if you want to select fields.</div></p>

                <p class="drag-info">OR Drag your fields as per same order in CSV...</p>

                <div class="column" id="column2"></div>

                <?php
                $nonce = wp_create_nonce('acix_import_' . get_current_blog_id());
                ?>
                <input type="hidden" name="action" value="import_csv">
                <input type="hidden" name="import_nonce" value="<?php echo esc_attr($nonce); ?>">
                <input type="hidden" name="selected_fields_order" id="selected_fields_order" value=""/>
                <input type="hidden" name="selected_fields" id="selected_fields" value=""/>

                <p class="submit"><input type="submit" name="import_btn" id="import_btn" class="button button-primary" value="Import"></p>
            </div>

        </form>
    <?php endif; ?>
</div>
