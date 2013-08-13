$(".task-start-action").click(
    function()
    {
        var element = $(this).parent().parent().parent();
        var id = $(element).attr('id');
        $("#task-sortable-rows-2").append(element);
        $("#task-sortable-rows-1").remove('#' + id);
        var addedElement = $("#task-sortable-rows-2 #" + id + " .task-start-action");
        $(addedElement).find('.z-label').html('Finish');
        $(addedElement).removeClass('task-start-action').addClass('task-finish-action');
    }
);

