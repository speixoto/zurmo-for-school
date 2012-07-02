function rebuildDynamicSearchRowNumbersAndStructureInput(formId)
{
    rowCount = 1;
    structure = '';
    $('#' + formId).find('.dynamic-search-row-number-label').each(function()
    {
        $(this).html(rowCount + '.');
        if(structure != '')
        {
            structure += ' AND ';
        }
        structure += rowCount;
        $(this).parent().find('.structure-position').val(rowCount);
        rowCount ++;
    });
    $('#' + formId).find('.dynamic-search-structure-input').val(structure);
}