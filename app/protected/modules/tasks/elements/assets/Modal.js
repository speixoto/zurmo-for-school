function transferModalValues(dialogId, data, url, attribute, errorInProcess)
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
            beforeSend: function(xhr)
                       {
                           $(dialogId).html('');
                           $(this).makeLargeLoadingSpinner(true, dialogId);
                       },
            success: function(dataOrHtml, textStatus, xmlReq)
                     {
                         $(this).processAjaxSuccessUpdateHtmlOrShowDataOnFailure(dataOrHtml, uniquePortletPageId);
                     },
            complete:function(XMLHttpRequest, textStatus)
                     {
                       $(dialogId).dialog("close");
                     },
            error:function(xhr, textStatus, errorThrown)
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