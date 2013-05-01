function copyProductTemplateDataForProduct(templateId, url)
{
    url = url + "?id=" + templateId;
    $.ajax(
        {
            type: 'GET',
            url: url,
            dataType: 'json',
            success: function(data)
                     {
                         $("#ProductCategoriesForm_ProductCategory_ids").tokenInput("clear");
                         $(data.categoryOutput).each(function(index)
                         {
                            $("#ProductCategoriesForm_ProductCategory_ids").tokenInput("add", {id: this.id, name: this.name});
                         });
                         $('#Product_type_value').val(data.productType);
                         $('#Product_pricefrequency_value').val(data.productPriceFrequency);
                         $('#Product_sellPrice_currency_id').val(data.productSellPriceCurrency);
                         $('#Product_sellPrice_value').val(data.productSellPriceValue);
                     }
        }
    );
}

function addProductRowToPortletGridView(productTemplateId, url, relationAttributeName, relationModelId, uniquePortletPageId, errorInProcess)
{
    url = url + "&id=" + productTemplateId + "&relationModelId=" + relationModelId + "&relationAttributeName=" + relationAttributeName;
    $.ajax(
        {
            type: 'GET',
            url: url,
            beforeSend: function(xhr)
                       {
                           $('#modalContainer').html('');
                           makeLargeLoadingSpinner(true, '#modalContainer');
                       },
            success: function(dataOrHtml, textStatus, xmlReq)
                     {
                         processAjaxSuccessUpdateHtmlOrShowDataOnFailure(dataOrHtml, uniquePortletPageId);
                         //$("#product-portlet-grid-view").yiiGridView.update("product-portlet-grid-view");
                     },
            complete:function(XMLHttpRequest, textStatus)
                     {
                       $('#modalContainer').dialog('close');
                       $('#product_opportunity_name').val('');
                       $('#product_opportunity_id').val('');
                       $('#product-configuration-form').hide('slow');
                       //juiPortlets.refresh();
                     },
            error:function(xhr, textStatus, errorThrown)
                  {
                      alert(errorInProcess);
                  }
        }
    );
}