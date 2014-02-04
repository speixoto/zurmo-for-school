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

/*function getCalendarEvents(url, startDate, endDate, callback)
{
    var selectedMyCalendars = getSelectedCalendars('.mycalendar');
    var selectedSharedCalendars = getSelectedCalendars('.sharedcalendar');
    $.ajax({
            url: url,
            dataType: 'json',
            data: {
                selectedMyCalendarIds : selectedMyCalendars,
                selectedSharedCalendarIds : selectedSharedCalendars,
                start      : startDate,
                end        : endDate
            },
            success: function(events) {
                callback(events);
            }
        });
}*/

function getCalendarStartDate(inputId)
{
    var view = $('#' + inputId).fullCalendar('getView');
    var startDate = view.start;
    console.log(startDate);
    var month = parseInt(startDate.getMonth()) + 1;
    var day   = startDate.getDate();
    var year  = startDate.getFullYear();
    return year + '-' + month + '-' + day;
}

function getCalendarEndDate(inputId)
{
    var view = $('#' + inputId).fullCalendar('getView');
    var endDate   = view.end;
    var month = parseInt(endDate.getMonth()) + 1;
    var day   = endDate.getDate();
    var year  = endDate.getFullYear();
    return year + '-' + month + '-' + day;
}

function getCalendarEvents(url, inputId)
{
    var selectedMyCalendars = getSelectedCalendars('.mycalendar');
    var selectedSharedCalendars = getSelectedCalendars('.sharedcalendar');
    var startDate = getCalendarStartDate(inputId);
    var endDate = getCalendarEndDate(inputId);
    var view = $('#' + inputId).fullCalendar('getView');
    var events = {
        url : url,
        data :function()
        {
            return {
                selectedMyCalendarIds : selectedMyCalendars,
                selectedSharedCalendarIds : selectedSharedCalendars,
                startDate      : startDate,
                endDate        : endDate,
                view           : view.name
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
    return events;
}