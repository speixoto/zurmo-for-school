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