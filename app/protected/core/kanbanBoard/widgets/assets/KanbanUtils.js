function setupKanbanDragDrop(){
    $(window).ready(function(){
        $(".item-to-place").live("mousemove",function(){
            $(this).draggable({
                helper: function(event, ui){
                    var label = $(event.currentTarget).html();
                    var width = $(this).width();
                    var clone = $('<div class="kanban-card clone">' + label + '</div>');
                    clone.width(width);
                    //clone.animate({ width : width}, 250);
                    $('body').append(clone);
                    return clone;
                },
                revert: "invalid",
                snap: ".droppable-dynamic-row-container",
                snapMode: "inner",
                cursor: "pointer",
                start: function(event,ui){
                    $(ui.helper).attr("id", $(this).data("id"));
                },
                stop: function(event, ui){
                    document.body.style.cursor = "auto";
                }
            });
        });

        $( ".droppable-dynamic-rows-container").droppable({
            accept: ".item-to-place",
            hoverClass: "ui-state-active",
            cursor: "pointer",
            drop: function( event, ui ) {
                //todo: hide drop overlay
                $('.dynamic-droppable-area').removeClass('activate-drop-zone');
            },
            activate: function(event,ui){
                dropped = false;
                $('.dynamic-droppable-area').addClass('activate-drop-zone');
            },
            deactivate: function(event,ui){
                $('.dynamic-droppable-area').removeClass('activate-drop-zone');
            }
        });
    });
}