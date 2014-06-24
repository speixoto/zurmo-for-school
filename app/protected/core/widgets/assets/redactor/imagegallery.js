if (!RedactorPlugins) var RedactorPlugins = {};

RedactorPlugins.imagegallery = {
	init: function ()
	{
	    this.buttonAdd('zurmoImage', 'Image Gallery', this.imageGalleryButton);
	},
    imageGalleryButton: function(buttonName, buttonDOM, buttonObj, e)
	{
        var callback = $.proxy(this.insertFromGalleryModal, this);
        var url = this.opts.urlForImageGallery;
        var linkForInsertSelector = '.' + this.opts.linkForInsertClass;

        $.ajax({
            url: url,
            type: "GET",
            success: function (data) {
                $('#redactor_modal_inner').empty().append(data);
                $('#redactor_modal').off('click', linkForInsertSelector);
                $('#redactor_modal').on('click', linkForInsertSelector, callback);
                $('#redactor_modal').addClass('ui-dialog redactor-image-modal');
            },
            error: function (xhr, status) {
                alert("Sorry, there was a problem!");
            }
        });

        this.modalInit(this.opts.curLang.image, '', 800);

    },
    insertFromGalleryModal: function(event)
    {
        var element = event.target;
        var imageurl = $(element).data('url');
        this.imageInsert({'filelink': imageurl}, false);
        this.modalClose();
    }
};