jQuery.noConflict();

function submitForm(elem) {
    var attrType = jQuery(elem).prev('.editableinfo').attr('attrType');
    var formData = 'editable_value=' + jQuery(elem).prev('.editableinfo').val()
            + '&id_info=' + jQuery(elem).prev().prev('.id_info').val()
            + '&attrinfo=' + attrType;
    var url = jQuery(elem).parent().attr('action');
    jQuery('#loading-mask').show();
    jQuery('.updating_text').show();
    jQuery(elem).parent().prev().prev('.editable_value').hide();
    jQuery(elem).prev('.editableinfo').hide();
    jQuery.ajax({url: url, type: "GET", data: formData,datatype: 'json',
        success: function(result) {
            var result = jQuery.parseJSON(result);
            jQuery('#loading-mask').hide();
            jQuery('.updating_text').hide();
            jQuery(elem).parent().prev().prev('.editable_value').show();
            if(result.status == 'success'){
                if(attrType == 'status'){
                    jQuery(elem).parent().prev().prev('.editable_value').html(jQuery(elem).prev('.editableinfo').find('option:selected').text());
                }else{
                    jQuery(elem).parent().prev().prev('.editable_value').html(jQuery(elem).prev('.editableinfo').val());
                }
            }
            if(result.status == 'error'){
               alert(result.message);
            }
            jQuery(elem).parent().remove();
            
        },
        error:function(){
            jQuery('#loading-mask').hide(); 
            jQuery('.updating_text').hide();
            jQuery(elem).parent().remove();
            alert('Product has not been updated');
      }  
    }
    );
}
function showElement(elem) {
    if (jQuery(elem).next().next('#product_edit_form').length == 0) {
        var editablevalue = jQuery(elem).html();
        var attrinfo = jQuery(elem).attr('attrinfo');
        var prodid = jQuery(elem).attr('prodid');
        var productEditUrl = jQuery('.productEditUrl').val();
        if (attrinfo == 'name') {
            jQuery(elem).parent().append('<form onsubmit="return false;" id="product_edit_form" action="' + productEditUrl + '">\n\
                <input class="id_info" type="hidden" name="id_info" value="' + prodid + '" />\n\
                <textarea attrType="' + attrinfo + '" class="editableinfo ediatblename" name="product_edit_' + attrinfo + '_value">' + editablevalue + '</textarea>\n\
                <div title="save" value="submit" class="submit_ajax_button" onclick="submitForm(this)" />\n\
                <div title="cancel" class="cancel_button" value="cancel" onclick="jQuery(this).parent().remove()" />\n\
                </form>');
        } else if (attrinfo == 'status') {
            if (editablevalue == 'Enabled') {
                var enabled = 'selected="selected"';
                var disabled = '';
            } else {
                var enabled = '';
                var disabled = 'selected="selected"';
            }
            jQuery(elem).parent().append('<form onsubmit="return false;" id="product_edit_form" action="' + productEditUrl + '">\n\
                <input class="id_info" type="hidden" name="id_info" value="' + prodid + '" />\n\
                <select attrType="' + attrinfo + '" class="editableinfo" name="product_edit_' + attrinfo + '_value">\n\
                <option ' + enabled + ' value="1">Enabled</option>\n\
                <option ' + disabled + ' value="2">Disabled</option>\n\
                </select>\n\
                <div title="save" class="submit_ajax_button" onclick="submitForm(this)" />\n\
                <div title="cancel" class="cancel_button" value="cancel" onclick="jQuery(this).parent().remove()" />\n\
                </form>');
        } else {
            jQuery(elem).parent().append('<form onsubmit="return false;" id="product_edit_form" action="' + productEditUrl + '">\n\
                <input class="id_info" type="hidden" name="id_info" value="' + prodid + '" />\n\
                <input attrType="' + attrinfo + '" class="editableinfo" value="' + editablevalue + '" type="text" name="product_edit_' + attrinfo + '_value" />\n\
                <div title="save" value="submit" class="submit_ajax_button" onclick="submitForm(this)" />\n\
                <div title="cancel" class="cancel_button" value="cancel" onclick="jQuery(this).parent().remove()" />\n\
                </form>');
        }
        return false;
    }
}