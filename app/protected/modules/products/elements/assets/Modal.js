function addProductRowToPortletGridView(productId, url, relatedField, relatedFieldId)
{
    url = url + "?id=" + productId + "&relatedFieldId=" + relatedFieldId + "&relatedField=" + relatedField;
    $.ajax(
        {
            type: 'GET',
            url: url,
            dataType: 'json',
            success: function(data)
                     {
                         $('#product_opportunity_name').val('');
                         $('#product_opportunity_id').val('');
                         $('#product-configuration-form').hide('slow');
                         $("#product-portlet-grid-view").yiiGridView.update("product-portlet-grid-view");
                     }
        }
    );
}