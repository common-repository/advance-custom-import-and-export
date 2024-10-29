<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
    global $acix_obj;

    //https://codex.wordpress.org/Function_Reference/get_current_screen
    $screen = get_current_screen();
?>
<div class="ac-tab-container">
    <ul class="ac-tabs">
        <li <?php if( $screen->id == $acix_obj->main_page ):?> class="active" <?php endif;?>>
            <a href="<?php echo menu_page_url('acix_main',false);?>">Introduction</a>
        </li>
        <li <?php if( $screen->id == $acix_obj->settings_page ):?> class="active" <?php endif;?>>
            <a href="<?php echo menu_page_url('acix_settings',false);?>">ACIX Settings</a>
        </li>
    </ul>
</div>
