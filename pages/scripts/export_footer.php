<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<script type="text/javascript">
    function startGenerate(page,total,begun,filename)
    {
        jQuery(document).ready(function($) {
            var formdata = {
                'action': 'put_csv',
                'post_type' : '<?php echo $post_type;?>',
                'fields' : '<?php echo $fields;?>',
                'fields_order' : '<?php echo $fields_order;?>',
                'page' : page,
                'total' : total,
                'begun' : begun,
                'fname' : filename,
                'text_filename' : '<?php echo $txt_file;?>',
                'generate_order_file' : '<?php echo $generate_order_file;?>',
                'nonce' : '<?php echo $nonce;?>'
            };

            $.post('<?php echo $this->ajax_url;?>',formdata, function( data ) {
                console.log("Begun!!!");
                $("#column2").html("Generating...");
            }).done(function( data ) {
                var obj = jQuery.parseJSON(data);

                if(obj.error)
                {
                    $("#column2").html(obj.error_msg);
                }
                else
                {
                    console.log(data);

                    if (!obj.CSV_DONE) {
                        setTimeout(function () {
                            startGenerate(obj.page, total, obj.begun, filename);
                        }, 1000);
                    }
                    else if (obj.CSV_DONE)
                    {
                        var output = '';

                        output = "<h1>Congratulations, Csv generated successfully.</h1> <br />";

                        output += "<a href='" + obj.fileurl + "' download>Click here</a> to download. <br />";

                        if(obj.has_order_file)
                        {
                            output += "<h1>Order file generated successfully.</h1> <br />";

                            output += "<a href='" + obj.txt_fileurl + "' download>Click here</a> to download.";
                        }

                        $("#column2").html(output);
                    }
                }//if no errors occured
            });
        });
    }

    startGenerate('<?php echo $this->page?>','<?php echo $this->total_record_at_once;?>','true','<?php echo $fname;?>');

</script>