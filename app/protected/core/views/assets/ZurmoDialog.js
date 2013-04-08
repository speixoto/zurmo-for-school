$.widget(
    "ui.dialog", $.ui.dialog, {
        _size: function() {
            /* If the user has resized the dialog, the .ui-dialog and .ui-dialog-content
             * divs will both have width and height set, so we need to reset them
             */
            var nonContentHeight, minContentHeight, autoHeight,
                options = this.options,
                isVisible = this.uiDialog.is( ":visible" );

            // reset content sizing
            this.element.show().css({
                width: "auto",
                minHeight: 0,
                height: 0
            });

            if ( options.minWidth > options.width ) {
                options.width = options.minWidth;
            }

            // reset wrapper sizing
            // determine the height of all the non-content elements
            nonContentHeight = this.uiDialog.css({
                minHeight: '100px',
                //minWidth: '75%',
                //height: '94%',
                //width: 'auto',
                height: 'auto',
                position : 'absolute'
            }).outerHeight();

            minContentHeight = Math.max( 0, options.minHeight - nonContentHeight );

            if ( options.height === "auto" ) {
                // only needed for IE6 support
                if ( $.support.minHeight ) {
                    this.element.css({
                        minHeight: minContentHeight,
                        height: "auto"
                    });
                } else {
                    this.uiDialog.show();
                    autoHeight = this.element.css( "height", "auto" ).height();
                    if ( !isVisible ) {
                        this.uiDialog.hide();
                    }
                    this.element.height( Math.max( autoHeight, minContentHeight ) );
                }
            } else {
                this.element.height( Math.max( options.height - nonContentHeight, 0 ) );
            }

            if (this.uiDialog.is( ":data(resizable)" ) ) {
                this.uiDialog.resizable( "option", "minHeight", this._minHeight() );
            }
        }
    }
);