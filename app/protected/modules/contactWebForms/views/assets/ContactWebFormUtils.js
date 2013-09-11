$("[id^='ContactWebForm_serializedData_']").live('change', function()
{
    if ($(this).is(':checked'))
    {
        var attributeId      = $(this).val();
        var elementId        = $(this).attr('id');
        var attributeLabel   = $('label[for=' + elementId + ']').html();
        $(this).closest('div').remove();
        var attributeElement = '<li><div class="dynamic-row"><div>';
        attributeElement    += '<label id="label_for_placedAttribute_' + attributeId + '" for=\'' + elementId + '\'>' + attributeLabel + '</label>';
        attributeElement    += '<input class="inline-edit" type=\'hidden\' id=\'placedAttribute_' + attributeId + '\' name=\'placedAttribute[' + attributeId + '][label]\' value=\'' + attributeLabel + '\' />';
        attributeElement    += '<div id=\'requiredAttribute_placedAttribute_' + attributeId + '\' style=\'display: none;\'><input element-identifier=\'placedAttribute_' + attributeId + '\' class=\'isRequired\' type=\'checkbox\' name=\'placedAttribute[' + attributeId + '][required]\' value=\'1\'/> Required?</div>';
        attributeElement    += '</div><a class="remove-dynamic-row-link" id=\'' + elementId + '\' data-value=\'' + attributeId + '\' href="#">—</a>';
        attributeElement    += '<a id="editIcon_placedAttribute_' + attributeId + '" onclick="editInline(\'placedAttribute_' + attributeId + '\');" href="javascript: void(0);">Edit</a></div></li>';
        $('ul#yw1').append(attributeElement);
    }
});
$('.remove-dynamic-row-link').live('click', function(){
    var attributeId      = $(this).attr('data-value');
    var elementId        = $(this).attr('id');
    var attributeLabel   = $('label[for=' + elementId + ']').html();
    $(this).closest('li').remove();
    var attributeElement = '<div class=\'multi-select-checkbox-input\'><label class=\'hasCheckBox\'><label class=\'hasCheckBox\'>';
    attributeElement    += '<input id=\'' + elementId + '\' value=\'' + attributeId + '\' type=\'checkbox\'';
    attributeElement    += ' name=\'ContactWebForm[serializedData][]\'></label></label><label for=\'' + elementId + '\'>' + attributeLabel + '</label></div>';
    $('span#ContactWebForm_serializedData').append(attributeElement);
    return false;
});

$('.inline-edit').live({
    keydown: function(e){
        if (e.keyCode == 13) {
            $(this).focusout();
        }
    },
    focusout: function() {
        var elementId = $(this).attr('id');
        disableInlineEdit(elementId);
    }
});

$('.isRequired').live('change', function(){
    var elementId = $(this).attr('element-identifier');
    disableInlineEdit(elementId);
});

function disableInlineEdit(elementId)
{
    $('#editIcon_' + elementId).show();

    //change this element's type back to hidden
    var element = document.getElementById(elementId);
    element.type = 'hidden';

    //update value of label
    var attributeLabel = $('#' + elementId).val();
    $('#label_for_' + elementId).html(attributeLabel);
    $('#label_for_' + elementId).show();
    $('#requiredAttribute_' + elementId).hide();
}

function editInline(elementId)
{
    var element = document.getElementById(elementId);
    element.type = 'text';
    //var elementValue = $('#' + elementId).val();
    //$('#' + elementId).focus().val('').val(elementValue);
    $('#requiredAttribute_' + elementId).show();
    $('#editIcon_' + elementId).hide();
    $('#label_for_' + elementId).hide();
}