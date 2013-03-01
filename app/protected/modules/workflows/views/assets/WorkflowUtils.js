$(window).ready(function(){
    $(".attribute-to-place").live("mousemove",function(){
        $(this).draggable({
            helper: function(event){
                var label = $(event.target).html();
                var width = $('.wrapper').width() * 0.75 - 55;
                var clone = $('<div class="dynamic-attribute-row clone">' + label + '</div>');
                //clone.width(width);
                clone.animate({ width : width}, 250);
                $('body').append(clone);
                return clone;
            },
            revert: "invalid",
            snap: ".droppable-attribute-container",
            snapMode: "inner",
            cursor: "pointer",
            start: function(event,ui){
                $(ui.helper).attr("id", $(this).attr("id"));
            },
            stop: function(event, ui){
                document.body.style.cursor = "auto";
            }
        });
    });
    var isDragging = false;
    $( ".droppable-attributes-container").droppable({
        accept: ".attribute-to-place",
        hoverClass: "ui-state-active",
        cursor: "pointer",
        drop: function( event, ui ) {
            //console.log(event, ui);
            //todo: hide drop overlay
            isDragging = false;
            $('.dynamic-droppable-area').removeClass('activate-drop-zone');
        },
        activate: function(event,ui){
            isDragging = true;
            $('.dynamic-droppable-area').addClass('activate-drop-zone');
        },
        deactivate: function(event,ui){
        }
    });
    /*
    $(".hasTree").hover(
        function(){
            $('.dynamic-droppable-area').addClass('activate-drop-zone');
        },
        function(){
            if (isDragging == false){
                $('.dynamic-droppable-area').removeClass('activate-drop-zone');
            }
        }
    );
    */
});

function rebuildWorkflowTriggersAttributeRowNumbersAndStructureInput(divId){
    rowCount = 1;
    structure = '';
    $('#' + divId).find('.dynamic-attribute-row-number-label').each(function(){
        $(this).html(rowCount + '.');
        if(structure != ''){
            structure += ' AND ';
        }
        structure += rowCount;
        $(this).parent().find('.structure-position').val(rowCount);
        rowCount ++;
    });
    $('#' + divId).find('.triggers-structure-input').val(structure);
    if(rowCount == 1){
        //hmm. not sure exactly how this will be named.
        $('#show-triggers-structure-wrapper').hide();
    } else {
        $('#show-triggers-structure-wrapper').show();
    }
}

function rebuildWorkflowActionRowNumbers(divId){
    rowCount = 1;
    structure = '';
    $('#' + divId).find('.dynamic-action-row-number-label').each(function(){
        $(this).html(rowCount + '.');
        rowCount ++;
    });
}
function toggleWorkflowShouldSetValueWrapper(checkboxId)
{
    if ($('#' + checkboxId).attr('checked') == 'checked')
    {
        $('#' + checkboxId).parent().parent().find('.dynamic-action-attribute-type-and-value-wrapper').show();
    }
    else
    {
        $('#' + checkboxId).parent().parent().find('.dynamic-action-attribute-type-and-value-wrapper').hide();
    }
}