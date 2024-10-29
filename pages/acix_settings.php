<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $acix_obj;?>
<div class="wrap">
    <h2>ACIX Settings</h2>

    <?php $acix_obj->acix_menu();?>

    <div class="ac-output">

        <?php
        //get defined post types
        $post_types = get_post_types('', 'names');
        $post_types = $acix_obj->filter_posttypes($post_types);
        ?>

        <h2>Acix Settings</h2>

        <form method="POST">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="p_type">Post Type</label>
                    </th>
                    <td>
                        <select name="p_type" id="p_type">
                            <?php foreach ( $post_types as $post_type ): ?>
                                <?php
                                $sel = '';
                                $p_type = get_option("acix_post_type");
                                if($post_type == $p_type )
                                {
                                    $sel = 'selected="selected"';
                                }
                                ?>
                                <option <?=$sel;?> value="<?=$post_type;?>"><?=$post_type;?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>

            <?php
            $nonce = wp_create_nonce( 'acix_settings_' . get_current_blog_id());
            ?>
            <input type="hidden" name="acix_settings_nonce" value="<?php echo esc_attr( $nonce ) ;?>">

            <p><?php echo "Fields and Meta Keys for this selected post type will be exported in CSV.";?></p>

            <p class="submit"><input type="submit" name="acix_settings" id="acix_settings" class="button button-primary" value="Save"></p>
        </form>
    </div>
</div>
