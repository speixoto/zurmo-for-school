$("[id^='ContactWebForm_serializedData_']").live('change', function()
{
    if ($(this).is(':checked'))
    {
        var attributeId      = $(this).val();
        var elementId        = $(this).attr('id');
        var attributeLabel   = $('label[for=' + elementId + ']').html();
        $(this).closest('div').remove();
        var attributeElement = '<li><div class="dynamic-row webform-chosen-field"><div>';
        attributeElement    += '<input class="webform-chosen-field" type="text" id="placedAttribute_' + attributeId + '" name="placedAttribute[' + attributeId + '][label]" value="' + attributeLabel + '" />';
        attributeElement    += '<input type="checkbox" name="placedAttribute[' + attributeId + '][required]" value="1"/> Required?';
        attributeElement    += '<input class="hiddenAttribute" id="placedAttribute_hidden_' + attributeId + '" type="checkbox" name="placedAttribute[' + attributeId + '][hidden]" value="1" data-value="' + attributeId + '"/> Hidden?';
        attributeElement    += '<input type="text" style="display: none;" id="placedAttribute_hiddenValue_' + attributeId + '" name="placedAttribute[' + attributeId + '][hiddenValue]" value="" /></div>';
        attributeElement    += '<a class="remove-dynamic-row-link" id="' + elementId + '" data-value="' + attributeId + '" href="#">—</a>';
        attributeElement    += '</div></li>';
        $('ul#yw1').append(attributeElement);
    }
});
$('.remove-dynamic-row-link').live('click', function(){
    var attributeId      = $(this).attr('data-value');
    var elementId        = $(this).attr('id');
    var attributeLabel   = $('#placedAttribute_' + attributeId).val();
    $(this).closest('li').remove();
    var attributeElement = '<div class=\'multi-select-checkbox-input\'><label class=\'hasCheckBox\'><label class=\'hasCheckBox\'>';
    attributeElement    += '<input id=\'' + elementId + '\' value=\'' + attributeId + '\' type=\'checkbox\'';
    attributeElement    += ' name=\'ContactWebForm[serializedData][]\'></label></label><label for=\'' + elementId + '\'>' + attributeLabel + '</label></div>';
    $('span#ContactWebForm_serializedData').append(attributeElement);
    return false;
});
$('.hiddenAttribute').live('change', function(){
    var attributeId = $(this).attr('data-value');
    if ($(this).is(':checked'))
    {
        $('#placedAttribute_hiddenValue_' + attributeId).show();
    }
    else
    {
        $('#placedAttribute_hiddenValue_' + attributeId).hide();
    }
});