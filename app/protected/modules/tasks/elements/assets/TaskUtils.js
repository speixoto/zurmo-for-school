function transferUserModalValues(dialogId, data, url, attribute, errorInProcess)
{
    var userId;
    $.each(data, function(sourceFieldId, value)
    {
      if(sourceFieldId == 'Task_' + attribute + '_id')
      {
        userId = value;
      }
    });
    url = url + "&attribute=" + attribute + "&userId=" + userId;
    $.ajax(
        {
            type: 'GET',
            url: url,
            beforeSend: function()
                       {
                           $(dialogId).html('');
                           $(this).makeLargeLoadingSpinner(true, dialogId);
                       },
            success: function(data)
                     {
                         $("#permissionContent").html(data);
                         $(dialogId).dialog().dialog("close");
                     },
            error:function()
                  {
                      alert(errorInProcess);
                  }
        }
    );
    $.each(data, function(sourceFieldId, value)
    {
      $('#'+ sourceFieldId).val(value).trigger('change');
    });
    $(dialogId).dialog("close");
}

/**
 * Update task status
 */
function updateTaskStatus(status, url, errorInProcess)
{
    url = url + "&status=" + status;
    $.ajax(
        {
            type: 'GET',
            url: url,
            dataType: 'html',
            success: function(data)
                     {
                         $('#completionDate').html(data);
                     },
            error:function()
                  {
                      alert(errorInProcess);
                  }
        }
    );
}

function saveTaskFromRelation(url, errorInProcess)
{
    $.ajax(
        {
            type: 'POST',
            url: url,
            data: $("#task-modal-edit-form").serialize(),
            dataType: 'html',
            success: function(data)
                     {
                         form = $("#task-modal-edit-form");
                         form.find(".attachLoading:first").removeClass("loading");
                         form.find(".attachLoading:first").removeClass("loading-ajax-submit");
                     },
            error:function()
                  {
                      alert(errorInProcess);
                  }
        }
    );
}