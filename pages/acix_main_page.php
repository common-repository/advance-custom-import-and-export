<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $acix_obj;?>
<div class="wrap">
    <h2>ACIX Introduction</h2>

    <?php $acix_obj->acix_menu();?>

    <div class="ac-output">
        <ul class="steps">
            <li>
                <div class="info">First step is to choose what post type we are using to import/export.<a href="<?php echo menu_page_url('acix_settings',false);?>">Click here</a> to set.</div>
                <img src="<?php echo $acix_obj->plugin_url."assets/screenshot-1.png";?>" />
            </li>
            <li>
                <div class="info">Second step is to export the data.We can now generate the file for our selection.To see which fields are available to export <a href="<?php echo menu_page_url('acix_export',false);?>">Click here</a>.</div>
                <img src="<?php echo $acix_obj->plugin_url."assets/screenshot-2.png";?>" width="100%" />
            </li>
            <li>
                <div class="info">See the example below what we have selected.</div>
                <img src="<?php echo $acix_obj->plugin_url."assets/screenshot-3.png";?>" width="100%" />
            </li>
            <li>
                <div class="info">Results, We get after clicking on export. This is the output generated if we have checked the option "Generate file for field order", In previous screen.</div>
                <img src="<?php echo $acix_obj->plugin_url."assets/screenshot-4.png";?>"/>
            </li>
            <li>
                <div class="info">This is the CSV output.</div>
                <img src="<?php echo $acix_obj->plugin_url."assets/screenshot-5.png";?>"/>
            </li>
            <li>
                <div class="info">Third step is to import the data.To see which fields are available to import <a href="<?php echo menu_page_url('acix_import',false);?>">Click here</a>.See the example below what we have selected.</div>
                <img src="<?php echo $acix_obj->plugin_url."assets/screenshot-6.png";?>" width="100%" />
            </li>
            <li>
                <div class="info">Results, We get after clicking on import.</div>
                <img src="<?php echo $acix_obj->plugin_url."assets/screenshot-7.png";?>" />
            </li>
        </ul>
    </div>    
</div>
