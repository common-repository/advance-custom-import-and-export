<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $acix_obj;?>
<div class="wrap">
    <h2>Export CSV</h2>

    <?php
    $p_type = get_option("acix_post_type");

    $meta_fields = $acix_obj->get_meta_fields($p_type);

    $t_names = $acix_obj->get_taxonomies($p_type);

    $meta_fields = array_merge($meta_fields,$t_names);
    ?>

    <?php if(isset($_POST['export_btn']) && $_POST['export_btn'] != ""): ?>
        <div class="column" id="column2"></div>
    <?php else: ?>
        <form method="POST" enctype="multipart/form-data">

            <div class="column-container">
                <p class="drag-info">Drag the fields from here to right side.
                    They will appear in the CSV as the same order.
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
                <p class="drag-info"><input type="checkbox" value="Yes" name="generate_order_file" id="generate_order_file"> Generate file for fields order<div style="font-size:12px;padding-left: 20px;">This will provide you an order file for your selection, It will be useful at the time of import.</div></p>

                <p class="drag-info">Drag your fields here ...</p>

                <div class="column" id="column2"></div>

                <?php
                $nonce = wp_create_nonce('acix_export_' . get_current_blog_id());
                ?>
                <input type="hidden" name="action" value="export_csv">
                <input type="hidden" name="export_nonce" value="<?php echo esc_attr($nonce); ?>">
                <input type="hidden" name="selected_fields_order" id="selected_fields_order" value=""/>
                <input type="hidden" name="selected_fields" id="selected_fields" value=""/>

                <p class="submit"><input type="submit" name="export_btn" id="export_btn" class="button button-primary" value="Export"></p>
            </div>

        </form>
    <?php endif; ?>
</div>