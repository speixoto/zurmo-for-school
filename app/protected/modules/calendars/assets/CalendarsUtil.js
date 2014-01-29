function getSelectedCalendars(selector)
{
    var selectedCal = [];
    $(selector).each(function()
    {
        if($(this).is(':checked'))
        {
            selectedCal.push($(this).val());
        }
    });
    var selectedCalString = '';
    if(selectedCal.length > 0)
    {
        selectedCalString = selectedCal.join(',');
    }
    return selectedCalString;
}

/**
 * Adds the calendar row to the shared calendar list view
 */
function addCalendarRowToSharedCalendarListView(calendarId, url, sharedListContainerId, errorInProcess)
{
    url = url + "?id=" + calendarId;
    $.ajax(
        {
            type: 'GET',
            url: url,
            beforeSend: function(xhr)
                       {
                           $('#modalContainer').html('');
                           $(this).makeLargeLoadingSpinner(true, '#modalContainer');
                       },
            success: function(dataOrHtml, textStatus, xmlReq)
                     {
                         $(this).processAjaxSuccessUpdateHtmlOrShowDataOnFailure(dataOrHtml, sharedListContainerId);
                     },
            complete:function(XMLHttpRequest, textStatus)
                     {
                       $('#modalContainer').dialog('close');
                     },
            error:function(xhr, textStatus, errorThrown)
                  {
                      alert(errorInProcess);
                  }
        }
    );
}

