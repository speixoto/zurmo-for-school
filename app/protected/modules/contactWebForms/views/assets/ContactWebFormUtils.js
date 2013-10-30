$("[id^='ContactWebForm_serializedData_']").live('change', function()
{
    if ($(this).is(':checked'))
    {
        var attributeId      = $(this).val();
        var elementId        = $(this).attr('id');
        var attributeLabel   = $('label[for=' + elementId + ']').html();
        var ajaxAction       = $('#getPlacedAttributeAction').val();
        $(this).closest('div').remove();
        $.ajax({
            type: "GET",
            url: ajaxAction + "?attributeName=" + attributeId,
            data: {attributeLabel: attributeLabel}
        }).done(function(response) {
                $('ul#yw1').append(response);
        });
    }
});
$('.remove-dynamic-row-link').live('click', function(){
    var attributeId      = $(this).attr('data-value');
    var elementId        = $(this).attr('id');
    var attributeLabel   = $(this).data('label');
    $(this).closest('li').remove();
    var attributeElement = '<div class=\'multi-select-checkbox-input\'><label class=\'hasCheckBox\'>' +
                           '<label class=\'hasCheckBox\'>';
    attributeElement    += '<input id=\'' + elementId + '\' value=\'' + attributeId + '\' type=\'checkbox\'';
    attributeElement    += ' name=\'ContactWebForm[serializedData][]\'></label></label>' +
                           '<label for=\'' + elementId + '\'>' + attributeLabel + '</label></div>';
    $('span#ContactWebForm_serializedData').append(attributeElement);
    return false;
});
$('.hiddenAttribute').live('change', function(){
    var attributeId = $(this).attr('data-value');
    if ($(this).is(':checked'))
    {
        $('#hiddenAttributeElement_' + attributeId).show();
    }
    else
    {
        $('#hiddenAttributeElement_' + attributeId).hide();
    }
});