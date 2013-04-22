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
//                         var newRow = '<tr class="odd"><td>' + data.name +
//                                        '</td><td><div valueid="12" id="viewValue-product-portlet-grid-view_c1-' + data.id +
//                                        '" class="viewValue-product-portlet-grid-view_c1">' + data.quantity +
//                                        '</div><div style="display: none;" id="field-product-portlet-grid-view_c1-' + data.id +
//                                        '"><input itemid="' + data.id +
//                                        '" type="text" value="' + data.quantity +
//                                        '" name="quantity[' + data.id +
//                                        ']" id="quantity_' + data.id +
//                                        '"></div></td><td><div valueid="' + data.id +
//                                        '" id="viewValue-product-portlet-grid-view_c2-' + data.id +
//                                        '" class="viewValue-product-portlet-grid-view_c2">' + data.productSellPriceValue +
//                                        '</div><div style="display: none;" id="field-product-portlet-grid-view_c2-' + data.id +
//                                        '"><input itemid="' + data.id +
//                                        '" type="text" value="' + data.productSellPriceValue +
//                                        '" name="sellPrice[' + data.id +
//                                        ']" id="sellPrice_' + data.id +
//                                        '"></div></td><td><ul class="options-menu edit-row-menu nav"><li class="parent last"><a href="javascript:void(0);"><span></span></a><ul><li class="last"><a id="product-portlet-grid-view-delete-' + data.id +
//                                        '" href="#"><span>Delete</span></a></li></ul></li></ul></td></tr>';
//
//                         $('#product-portlet-grid-view .items > tbody:last').append(newRow);
                         $('#product_opportunity_name').val('');
                         $('#product_opportunity_id').val('');
                         $('#product-configuration-form').hide('slow');
                         $("#product-portlet-grid-view").yiiGridView.update("product-portlet-grid-view");
                     }
        }
    );
}