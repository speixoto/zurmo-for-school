function processAjaxSuccessError(id, data)
{
    if(data == 'failure')
    {
        alert('An error has occurred. You have attempted to access a page you do not have access to.');
        return false;
    }
}

function processListViewSummaryClone(listViewId, summaryCssClass)
{
    replacementContent = $('#' + listViewId).find('.' + summaryCssClass).html();
    $('#' + listViewId).parent().parent('.GridView')
    .find('form').first().find('.list-view-items-summary-clone')
    .html(replacementContent);
}

function updateListViewSelectedIds(gridViewId, selectedId, selectedValue)
{
    var array = new Array ();
    var processed = false;
    jQuery.each($('#' + gridViewId + "-selectedIds").val().split(','), function(i, value)
        {
            if(selectedId == value)
            {
                if(selectedValue)
                {
                    array.push(value);
                }
                processed = true;
            }
            else
            {
                if(value != '')
                {
                    array.push(value);
                }
            }
         }
     );
    if(!processed && selectedValue)
    {
        array.push(selectedId);
    }
    $('#' + gridViewId + "-selectedIds").val(array.toString());
}

function resetSelectedListAttributes(selectedListAttributesId, defaultSelectedAttributes)
{
    if($('#' + selectedListAttributesId).length > 0)
    {
        defaults = eval(defaultSelectedAttributes);
        $('#' + selectedListAttributesId).find("option").attr("selected", false);

        $('#' + selectedListAttributesId).parent().find(':checkbox').each(function(){

            if(jQuery.inArray($(this).val(), defaults) == -1)
            {
                $(this).attr('checked', false);
                $(this).parent().removeClass('c_on');
            }
            else
            {
                $(this).attr('checked', true);
                $(this).parent().addClass('c_on');
            }
        });
        $('#' + selectedListAttributesId).children("option").each(function(){
            if(jQuery.inArray($(this).val(), defaults) != -1)
            {
                $(this).prop('selected', true);
            }
        });
    }
}

function resolveLastSelectedListAttributesOption(selectedListAttributesId)
{
    if($('#' + selectedListAttributesId).parent().find('input:checkbox:checked').length == 1)
    {
        $('#' + selectedListAttributesId).parent().find('input:checkbox:checked').parent().addClass('disabled');
    }
    else
    {
        $('#' + selectedListAttributesId).parent().find('input:checkbox').parent().removeClass('disabled');
    }
}