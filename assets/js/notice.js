(function($){

    WHDIInstallTheme = {

        /**
         * Init
         */
        init: function() {
            this._auto_close_notice();
            this._bind();
        },

        /**
         * Binds events for the WHDI Sites.
         *
         * @since 1.3.2
         *
         * @access private
         * @method _bind
         */
        _bind: function()
        {
            $( document ).on( 'click', '.whdi-theme-not-installed', WHDIInstallTheme._install_and_activate );
            $( document ).on( 'click', '.whdi-theme-installed-but-inactive', WHDIInstallTheme._activateTheme );
            $( document ).on('wp-theme-install-success' , WHDIInstallTheme._activateTheme);
        },

        /**
         * Close Getting Started Notice
         *
         * @param  {object} event
         * @return void
         */
        _auto_close_notice: function() {

            if( $( '.whdi-getting-started-btn' ).length ) {
                $.ajax({
                        url: WHDIInstallThemeVars.ajaxurl,
                        type: 'POST',
                        data: {
                            'action' : 'whdi-getting-started-notice',
                            '_ajax_nonce' : WHDIInstallThemeVars._ajax_nonce,
                        },
                    })
                    .done(function (result) {
                    });
            }

        },

        /**
         * Activate Theme
         *
         * @since 1.3.2
         */
        _activateTheme: function( event, response ) {
            event.preventDefault();

            $('#whdi-theme-activation-nag a').addClass('processing');

            if( response ) {
                $('#whdi-theme-activation-nag a').text( WHDIInstallThemeVars.installed );
            } else {
                $('#whdi-theme-activation-nag a').text( WHDIInstallThemeVars.activating );
            }

            // WordPress adds "Activate" button after waiting for 1000ms. So we will run our activation after that.
            setTimeout( function() {

                $.ajax({
                        url: WHDIInstallThemeVars.ajaxurl,
                        type: 'POST',
                        data: {
                            'action' : 'whdi-activate-theme',
                            '_ajax_nonce' : WHDIInstallThemeVars._ajax_nonce,
                        },
                    })
                    .done(function (result) {
                        if( result.success ) {
                            $('#whdi-theme-activation-nag a').text( WHDIInstallThemeVars.activated );

                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        }

                    });

            }, 3000 );

        },

        /**
         * Install and activate
         *
         * @since 1.3.2
         *
         * @param  {object} event Current event.
         * @return void
         */
        _install_and_activate: function(event ) {
            event.preventDefault();
            var theme_slug = $(this).data('theme-slug') || '';
            var btn = $( event.target );

            if ( btn.hasClass( 'processing' ) ) {
                return;
            }

            btn.text( WHDIInstallThemeVars.installing ).addClass('processing');

            if ( wp.updates.shouldRequestFilesystemCredentials && ! wp.updates.ajaxLocked ) {
                wp.updates.requestFilesystemCredentials( event );
            }

            wp.updates.installTheme( {
                slug: theme_slug
            });
        }

    };

    /**
     * Initialize
     */
    $(function(){
        WHDIInstallTheme.init();
    });

})(jQuery);