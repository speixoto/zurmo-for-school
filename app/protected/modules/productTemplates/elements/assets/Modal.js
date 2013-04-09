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
                         if($("#MassEdit_productTemplate").length > 0) {
                             $('#Product_type_value').removeAttr('disabled');
                             removeDisabledCss('type_value');
                             toggleCheckboxes('type');
                             $('.token-input-list').removeClass('disabled');
                             $('#Product_null').removeAttr('disabled');
                             $('#ProductCategoriesForm_ProductCategory_ids').removeAttr('disabled');
                             removeDisabledCss('null');
                             toggleCheckboxes('null');
                             $('#Product_pricefrequency_value').removeAttr('disabled');
                             removeDisabledCss('pricefrequency_value');
                             toggleCheckboxes('pricefrequency');
                             $('#Product_sellPrice').removeAttr('disabled');
                             removeDisabledCss('sellPrice');
                             toggleCheckboxes('sellPrice');
                         }
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

function removeDisabledCss(element)
{
    if ($('#Product_' + element).attr('type') != 'button')
    {
        if ($('#Product_' + element).attr('href') != undefined)
        {
            $('#Product_' + element).css('display', '');
        }
    };
}

function toggleCheckboxes(element)
{
    $('#MassEdit_' + element).attr('checked', 'checked');
    //document.getElementById("MassEdit_" + element).checked=true;
}