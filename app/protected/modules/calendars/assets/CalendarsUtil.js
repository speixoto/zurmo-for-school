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

function refreshCalendarEvents(url, startDate, endDate)
{
    /*var selectedMyCalendars = getSelectedCalendars('.mycalendar');
    var selectedSharedCalendars = getSelectedCalendars('.sharedcalendar');
    var startDate = getCalendarStartDate('calendar');
    var endDate = getCalendarEndDate('calendar');
    var events = {
        url : url,
        data :function()
        {
            return {
                selectedMyCalendarIds : selectedMyCalendars,
                selectedSharedCalendarIds : selectedSharedCalendars,
                startDate      : startDate,
                endDate        : endDate
                }
        },
        loading: function(bool)
                 {
                    if (bool)
                    {
                        $(this).makeLargeLoadingSpinner(true, '#calendar');
                    }
                    else
                    {
                        $(this).makeLargeLoadingSpinner(false, '#calendar');
                    }
                 }
    };
    return events;*/
    $('#calendar').fullCalendar('removeEventSource', events);
    $('#calendar').fullCalendar('addEventSource', events);
    $('#calendar').fullCalendar('refetchEvents');
}

function getModuleDateTimeAttributes(moduleName, url, targetId, attributeName)
{
    $.ajax({
            url: url,
            dataType: 'html',
            data:{moduleName : moduleName, attribute : attributeName},
            success: function(data) {
                $('#' + targetId).html(data);
            }
        });
}

function getCalendarEvents(url, inputId)
{
    var selectedMyCalendars     = getSelectedCalendars('.mycalendar');
    var selectedSharedCalendars = getSelectedCalendars('.sharedcalendar');
    var view                    = $('#' + inputId).fullCalendar('getView');
    var events = {
        url : url,
        data :function()
        {
            return {
                selectedMyCalendarIds : selectedMyCalendars,
                selectedSharedCalendarIds : selectedSharedCalendars,
                startDate      : $.fullCalendar.formatDate(view.start, 'yyyy-MM-dd'),
                endDate        : $.fullCalendar.formatDate(view.end, 'yyyy-MM-dd'),
                dateRangeType  : view.name
                }
        }
    };
    return events;
}