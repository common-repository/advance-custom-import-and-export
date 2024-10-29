<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<script type="text/javascript">
    function startImport(begun)
    {
        jQuery(document).ready(function($) {
            var formdata = {
                'action': 'get_csv',
                'post_type' : '<?php echo $post_type;?>',
                'fields' : '<?php echo $fields;?>',
                'fields_order' : '<?php echo $fields_order;?>',
                'begun' : begun,
                'uploaded_file' : <?php echo json_encode($uploaded_file);?>,
                'author_name' : '<?php echo $author_name;?>',
                'nonce' : '<?php echo $nonce;?>'
            };

            $.post('<?php echo $this->ajax_url;?>',formdata, function( data ) {
                console.log("Begun!!!");
                $("#column2").html("Generating...");
            }).done(function( data ) {
                //console.log(typeof data);
                var obj = jQuery.parseJSON(data);

                if(obj.error)
                {
                    $("#column2").html(obj.error_msg);
                }
                else
                {
                    console.log(data);

                    var output = '<h1>Congratulations,Import successful.</h1><br />';
                    output += "<h3>Summary</h3><br />";
                    output += "<b>Post type:</b> "+ obj.post_type + " <br /><br />" ;
                    output += "<b>Total post:</b> "+ obj.total_posts + " <br /><br />" ;
                    output += "<b>Post are written as shown below,</b><br /><br />"+ obj.posts_writtern + " <br />" ;

                    $("#column2").html(output);
                }
            });
        });
    }

    startImport('true');

</script>