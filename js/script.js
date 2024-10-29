//https://pippinsplugins.com/drag-and-drop-order-for-plugin-options/
//http://www.sitepoint.com/using-jquery-interactions-in-your-wordpress-admin/
//http://jqueryui.com/sortable/#connect-lists

//trigger on document ready
jQuery(document).ready(function($){
    var jsonObj = [];

    $( "#column1, #column2" ).sortable({
        connectWith: ".column",
        handle: 'h2',
        cursor: 'move',
        placeholder: 'placeholder',
        opacity: 0.4,
        stop: function(event, ui){
            $(ui.item).find('h2').click();

            $('#column2').each(function(){
                var item = {};
                item ["type"] = $(ui.item).attr('data-type') ;
                item ["field"] = $(ui.item).attr('id') ;

                var found = false;
                for(var x=0; x < jsonObj.length; x++)
                {
                    if (jsonObj[x].field == item ["field"])
                    {
                        found = true;
                        break;
                    }//if item is already exists in array
                }
                if (!found) {
                    jsonObj.push(item);
                }

                var jsonString = JSON.stringify(jsonObj);

                //http://stackoverflow.com/questions/15009448/creating-a-json-dynamically-with-each-input-value-using-jquery
                $("#selected_fields").val(jsonString);

                var itemorder = $(this).sortable('toArray');

                $("#selected_fields_order").val(itemorder);
            });
        }
    }).disableSelection();
});