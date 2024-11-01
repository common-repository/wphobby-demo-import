/**
 * AJAX Request Queue
 *
 * - add()
 * - remove()
 * - run()
 * - stop()
 *
 * @since 1.0.0
 */
var WHDISitesAjaxQueue = (function() {

    var requests = [];

    return {

        /**
         * Add AJAX request
         *
         * @since 1.0.0
         */
        add:  function(opt) {
            requests.push(opt);
        },

        /**
         * Remove AJAX request
         *
         * @since 1.0.0
         */
        remove:  function(opt) {
            if( jQuery.inArray(opt, requests) > -1 )
                requests.splice($.inArray(opt, requests), 1);
        },

        /**
         * Run / Process AJAX request
         *
         * @since 1.0.0
         */
        run: function() {
            var self = this,
                oriSuc;

            if( requests.length ) {
                oriSuc = requests[0].complete;

                requests[0].complete = function() {
                    if( typeof(oriSuc) === 'function' ) oriSuc();
                    requests.shift();
                    self.run.apply(self, []);
                };

                jQuery.ajax(requests[0]);

            } else {

                self.tid = setTimeout(function() {
                    self.run.apply(self, []);
                }, 1000);
            }
        },

        /**
         * Stop AJAX request
         *
         * @since 1.0.0
         */
        stop:  function() {

            requests = [];
            clearTimeout(this.tid);
        }
    };

}());

(function($){

    var WHDISSEImport = {
        complete: {
            posts: 0,
            media: 0,
            users: 0,
            comments: 0,
            terms: 0,
        },

        updateDelta: function (type, delta) {
            this.complete[ type ] += delta;

            var self = this;
            requestAnimationFrame(function () {
                self.render();
            });
        },
        updateProgress: function ( type, complete, total ) {
            var text = complete + '/' + total;

            if( 'undefined' !== type && 'undefined' !== text ) {
                total = parseInt( total, 10 );
                if ( 0 === total || isNaN( total ) ) {
                    total = 1;
                }
                var percent = parseInt( complete, 10 ) / total;
                var progress     = Math.round( percent * 100 ) + '%';
                var progress_bar = percent * 100;

                if( progress_bar <= 100 ) {
                    var process_bars = document.getElementsByClassName( 'whdi-site-import-process' );
                    for ( var i = 0; i < process_bars.length; i++ ) {
                        process_bars[i].value = progress_bar;
                    }
                    WHDIAdmin._log_title( 'Importing Content.. ' + progress );
                }
            }
        },
        render: function () {
            var types = Object.keys( this.complete );
            var complete = 0;
            var total = 0;

            for (var i = types.length - 1; i >= 0; i--) {
                var type = types[i];
                this.updateProgress( type, this.complete[ type ], this.data.count[ type ] );

                complete += this.complete[ type ];
                total += this.data.count[ type ];
            }

            this.updateProgress( 'total', complete, total );
        }
    };

    WHDIAdmin = {

        _stored_data  : {
            'whdi-site-category' : [],
            'whdi-site-page-builder': [],
            'whdi-sites' : [],
        },

        current_site: [],

        templateData: {},

        log_file        : '',
        customizer_data : '',
        wxr_url         : '',
        options_data    : '',
        widgets_data    : '',
        import_start_time  : '',
        import_end_time    : '',


        init: function()
        {
            this._bind();
        },

        /**
         * Binds events for the WHDI Plugin.
         *
         * @since 1.0.0
         * @access private
         * @method _bind
         */
        _bind: function()
        {
            $( document ).on('click', '.install-theme-preview', WHDIAdmin._preview);
            $( document ).on('click', '.close-full-overlay', WHDIAdmin._fullOverlay);
            $( document ).on('click', '.next-theme', WHDIAdmin._nextTheme);
            $( document ).on('click', '.previous-theme', WHDIAdmin._previousTheme);
            $( document ).on('click', '.collapse-sidebar', WHDIAdmin._collapse);
            $( document ).on('click', '.whdi-demo-import', WHDIAdmin._importDemo);

            //$( document ).on('whdi-install-and-activate-required-plugins-done' , WHDIAdmin._process_import );
            //
            //$( document ).on( 'whdi-import-set-site-data-done', WHDIAdmin._importWPForms );


            $( document ).on( 'whdi-import-set-site-data-done', WHDIAdmin._resetData );
            $( document ).on( 'whdi-reset-data', WHDIAdmin._backup_before_rest_options );
            $( document ).on( 'whdi-backup-settings-before-reset-done', WHDIAdmin._reset_wp_forms );
            $( document ).on( 'whdi-delete-wp-forms-done', WHDIAdmin._reset_posts );

            $( document ).on('whdi-install-and-activate-required-plugins-done' , WHDIAdmin._process_import );
            $( document ).on('whdi-reset-data-done', WHDIAdmin._importWPForms );
            $( document ).on('whdi-import-wpforms-done', WHDIAdmin._importCustomizerSettings );
            $( document ).on('whdi-import-customizer-settings-done' , WHDIAdmin._importXML );
            $( document ).on('whdi-import-xml-done' , WHDIAdmin._importSiteOptions );
            $( document ).on('whdi-import-options-done', WHDIAdmin._importWidgets );
            $( document ).on('whdi-import-widgets-done', WHDIAdmin._importEnd );

        },

        /**
         * Individual Site Preview
         *
         * On click on image, more link & preview button.
         */
        _preview: function( event ) {

            event.preventDefault();

            var site_id = $(this).parents('.site-single').data('demo-id') || '';

            if( WHDIAdmin._stored_data ) {
                var site_data = WHDIAdmin._get_site_details( site_id );

                if( site_data ) {
                    // Set current site details.
                    WHDIAdmin.current_site = site_data;

                    // Set current screen.
                    WHDIAdmin._set_current_screen( 'get-started' );
                }
            }

            var self = $(this).parents('.theme');
            self.addClass('theme-preview-on');

            $('html').addClass('whdi-site-preview-on');

            WHDIAdmin._renderDemoPreview( self );
        },

        /**
         * Render Demo Preview
         */
        _renderDemoPreview: function(anchor) {

            var demoId             	   = anchor.data('demo-id') || '',
                apiURL                 = anchor.data('demo-api') || '',
                demoType               = anchor.data('demo-type') || '',
                demoURL                = anchor.data('demo-url') || '',
                screenshot             = anchor.data('screenshot') || '',
                demo_name              = anchor.data('demo-name') || '',
                demo_slug              = anchor.data('demo-slug') || '',
                content                = anchor.data('content') || '',
                requiredPlugins        = anchor.data('required-plugins') || '',
                whdiSiteOptions       = anchor.find('.whdi-site-options').val() || '';
                whdiEnabledExtensions = anchor.find('.whdi-enabled-extensions').val() || '';

            var template = wp.template('whdi-site-preview');

            templateData = [{
                id                       : demoId,
                whdi_demo_type          : demoType,
                whdi_demo_url           : demoURL,
                demo_api                 : apiURL,
                screenshot               : screenshot,
                demo_name                : demo_name,
                slug                     : demo_slug,
                content                  : content,
                required_plugins         : JSON.stringify(requiredPlugins),
                whdi_site_options       : whdiSiteOptions,
                whdi_enabled_extensions : whdiEnabledExtensions,
            }];

            // delete any earlier fullscreen preview before we render new one.
            $('.theme-install-overlay').remove();

            $('#whdi-sites-menu-page').append(template(templateData[0]));
            $('.theme-install-overlay').css('display', 'block');

            WHDIAdmin._checkNextPrevButtons();

            // Check is site imported recently and set flag.
            $.ajax({
                    url  : WHDI_Admin.ajaxurl,
                    type : 'POST',
                    data : {
                        action : 'whdi-set-reset-data',
                        '_ajax_nonce'      : WHDI_Admin._ajax_nonce,
                    },
                })
                .done(function ( response ) {
                    if( response.success ) {
                        WHDIAdmin.site_imported_data = response.data;
                }
            });


            WHDIAdmin.requiredPlugins = requiredPlugins;

            // Add disabled class from import button.
            $('.whdi-demo-import')
                .addClass('disabled not-click-able')
                .removeAttr('data-import');

            $('.required-plugins').addClass('loading').html('<span class="spinner is-active"></span>');

            // Required Required.
            $.ajax({
                    url  : WHDI_Admin.ajaxurl,
                    type : 'POST',
                    dataType: 'json',
                    data : {
                        action           : 'whdi-required-plugins',
                        _ajax_nonce      : WHDI_Admin._ajax_nonce,
                        required_plugins : requiredPlugins
                    },
                })
                .fail(function( jqXHR ){
                    // Remove loader.
                    $('.required-plugins').removeClass('loading').html('');

                })
                .done(function ( response ) {
                    required_plugins = response.data['required_plugins'];

                    // Release disabled class from import button.
                    $('.whdi-demo-import')
                        .removeClass('disabled not-click-able')
                        .attr('data-import', 'disabled');

                    // Remove loader.
                    $('.required-plugins').removeClass('loading').html('');
                    $('.required-plugins-list').html('');

                    /**
                     * Count remaining plugins.
                     * @type number
                     */
                    var remaining_plugins = 0;

                    /**
                     * Not Installed
                     *
                     * List of not installed required plugins.
                     */
                    if ( typeof required_plugins.notinstalled !== 'undefined' ) {

                        // Add not have installed plugins count.
                        remaining_plugins += parseInt( required_plugins.notinstalled.length );

                        $( required_plugins.notinstalled ).each(function( index, plugin ) {
                            $('.required-plugins-list').append('<li class="plugin-card plugin-card-'+plugin.slug+'" data-slug="'+plugin.slug+'" data-init="'+plugin.init+'" data-name="'+plugin.name+'">'+plugin.name+'</li>');
                        });
                    }

                    /**
                     * Inactive
                     *
                     * List of not inactive required plugins.
                     */
                    if ( typeof required_plugins.inactive !== 'undefined' ) {

                        // Add inactive plugins count.
                        remaining_plugins += parseInt( required_plugins.inactive.length );

                        $( required_plugins.inactive ).each(function( index, plugin ) {
                            $('.required-plugins-list').append('<li class="plugin-card plugin-card-'+plugin.slug+'" data-slug="'+plugin.slug+'" data-init="'+plugin.init+'" data-name="'+plugin.name+'">'+plugin.name+'</li>');
                        });
                    }

                    /**
                     * Active
                     *
                     * List of not active required plugins.
                     */
                    if ( typeof required_plugins.active !== 'undefined' ) {

                        $( required_plugins.active ).each(function( index, plugin ) {
                            $('.required-plugins-list').append('<li class="plugin-card plugin-card-'+plugin.slug+'" data-slug="'+plugin.slug+'" data-init="'+plugin.init+'" data-name="'+plugin.name+'">'+plugin.name+'</li>');
                        });
                    }

                    /**
                     * Enable Demo Import Button
                     * @type number
                     */
                    WHDIAdmin.requiredPlugins = required_plugins;

                });

            return;
        },

        _get_site_details: function( site_id ) {
            var all_sites = WHDIAdmin._stored_data['whdi-sites'] || [];

            if( ! all_sites ) {
                return false;
            }

            var single_site = all_sites.filter(function (site) { return site.id == site_id });
            if( ! single_site ) {
                return false;
            }

            if( ! $.isArray( single_site ) ) {
                return false;
            }

            return single_site[0];
        },

        _set_current_screen: function( screen ) {
            WHDIAdmin.current_screen = screen;
            var old_screen = $('.whdi-sites-preview').attr( 'screen' ) || '';


            if( old_screen ) {
                $('.whdi-sites-preview').removeClass( 'screen-' + old_screen );
            }

            $('.whdi-sites-preview').attr( 'screen', screen );
            $('.whdi-sites-preview').addClass( 'screen-' + screen );
        },

        /**
         * Full Overlay
         */
        _fullOverlay: function (event) {
            event.preventDefault();

            $('body').removeClass('importing-site');
            $('.previous-theme, .next-theme').removeClass('disabled');
            $('.theme-install-overlay').css('display', 'none');
            $('.theme-install-overlay').remove();
            $('.theme-preview-on').removeClass('theme-preview-on');
            $('html').removeClass('whdi-site-preview-on');
        },

        /**
         * Next Theme.
         */
        _nextTheme: function (event) {
            event.preventDefault();
            currentDemo = jQuery('.theme-preview-on')
            currentDemo.removeClass('theme-preview-on');
            nextDemo = currentDemo.next('.theme');
            nextDemo.addClass('theme-preview-on');

            var site_id = $(this).parents('.wp-full-overlay-header').data('demo-id') || '';

            if( WHDIAdmin._stored_data ) {
                var site_data = WHDIAdmin._get_site_details( site_id );

                if( site_data ) {
                    // Set current site details.
                    WHDIAdmin.current_site = site_data;
                }
            }

            WHDIAdmin._renderDemoPreview( nextDemo );
        },

        /**
         * Previous Theme.
         */
        _previousTheme: function (event) {
            event.preventDefault();

            currentDemo = jQuery('.theme-preview-on');
            currentDemo.removeClass('theme-preview-on');
            prevDemo = currentDemo.prev('.theme');
            prevDemo.addClass('theme-preview-on');

            var site_id = $(this).parents('.wp-full-overlay-header').data('demo-id') || '';

            if( WHDIAdmin._stored_data ) {
                var site_data = WHDIAdmin._get_site_details( site_id );


                if( site_data ) {
                    // Set current site details.
                    WHDIAdmin.current_site = site_data;
                }
            }

            WHDIAdmin._renderDemoPreview(prevDemo);
        },

        /**
         * Check Next Previous Buttons.
         */
        _checkNextPrevButtons: function() {
            currentDemo = jQuery('.theme-preview-on');
            nextDemo = currentDemo.nextAll('.theme').length;
            prevDemo = currentDemo.prevAll('.theme').length;

            if (nextDemo == 0) {
                jQuery('.next-theme').addClass('disabled');
            } else if (nextDemo != 0) {
                jQuery('.next-theme').removeClass('disabled');
            }

            if (prevDemo == 0) {
                jQuery('.previous-theme').addClass('disabled');
            } else if (prevDemo != 0) {
                jQuery('.previous-theme').removeClass('disabled');
            }

            return;
        },

        /**
         * Collapse Sidebar.
         */
        _collapse: function() {
            event.preventDefault();

            overlay = jQuery('.wp-full-overlay');

            if (overlay.hasClass('expanded')) {
                overlay.removeClass('expanded');
                overlay.addClass('collapsed');
                return;
            }

            if (overlay.hasClass('collapsed')) {
                overlay.removeClass('collapsed');
                overlay.addClass('expanded');
                return;
            }
        },

        /**
         * Fires when a nav item is clicked.
         *
         * @since 1.0
         * @access private
         * @method _importDemo
         */
        _importDemo: function(event) {
            event.preventDefault();

            var date = new Date();

            WHDIAdmin.import_start_time = new Date();

            var disabled = $(this).attr('data-import');

            if ( typeof disabled !== 'undefined' && disabled === 'disabled' || $this.hasClass('disabled') ) {

                $('.whdi-demo-import').addClass('updating-message installing')
                    .text( 'Installing Theme Demo' );

                $('.whdi-sites-result-preview').show();
                var output = '<div class="current-importing-status-title"></div><div class="current-importing-status-description"></div>';
                $('.current-importing-status').html( output );

                /**
                 * Process Bulk Plugin Install & Activate
                 */
                WHDIAdmin._bulkPluginInstallActivate();
            }
        },

        /**
         * Bulk Plugin Active & Install
         */
        _bulkPluginInstallActivate: function()
        {

            if( 0 === WHDIAdmin.requiredPlugins.length ) {
                return;
            }

            var not_installed 	 = WHDIAdmin.requiredPlugins.notinstalled || '';
            var activate_plugins = WHDIAdmin.requiredPlugins.inactive || '';

            // First Install Bulk.
            if( not_installed.length > 0 ) {
                WHDIAdmin._installAllPlugins( not_installed );
            }

            // Second Activate Bulk.
            if( activate_plugins.length > 0 ) {
                WHDIAdmin._activateAllPlugins( activate_plugins );
            }

            if( activate_plugins.length <= 0 && not_installed.length <= 0 ) {
                WHDIAdmin._enable_demo_import_button();
            }
            
            WHDIAdmin._enable_demo_import_button();

        },

        /**
         * Install All Plugins.
         */
        _installAllPlugins: function( not_installed ) {

            WHDIAdmin._log_title( 'Installing Required Plugins..' );

            $.each( not_installed, function(index, single_plugin) {

                WHDIAdmin._log_title( 'Installing Plugin - ' + WHDIAdmin.ucwords( single_plugin.name ));

                var $card = $( '.plugin-card-' + single_plugin.slug );

                // Add each plugin activate request in Ajax queue.
                // @see wp-admin/js/updates.js
                wp.updates.queue.push( {
                    action: 'install-plugin', // Required action.
                    data:   {
                        slug: single_plugin.slug
                    }
                } );
            });

            // Required to set queue.
            wp.updates.queueChecker();
        },

        /**
         * Activate All Plugins.
         */
        _activateAllPlugins: function( activate_plugins ) {

            WHDIAdmin._log_title( 'Activating Required Plugins..' );

            $.each( activate_plugins, function(index, single_plugin) {

                WHDISitesAjaxQueue.add({
                    url: WHDI_Admin.ajaxurl,
                    type: 'POST',
                    data: {
                        'action'            : 'whdi-required-plugin-activate',
                        'init'              : single_plugin.init,
                        '_ajax_nonce'      : WHDI_Admin._ajax_nonce,
                    },
                    success: function( result ){

                        if( result.success ) {

                            var pluginsList = WHDIAdmin.requiredPlugins.inactive;

                            // Reset not installed plugins list.
                            WHDIAdmin.requiredPlugins.inactive = WHDIAdmin._removePluginFromQueue( single_plugin.slug, pluginsList );

                            // Enable Demo Import Button
                            WHDIAdmin._enable_demo_import_button();
                        } else {
                        }
                    }
                });
            });
            WHDISitesAjaxQueue.run();
        },

        /**
         * Remove plugin from the queue.
         */
        _removePluginFromQueue: function( removeItem, pluginsList ) {
            return jQuery.grep(pluginsList, function( value ) {
                return value.slug != removeItem;
            });
        },

        ucwords: function( str ) {
            if( ! str ) {
                return '';
            }

            str = str.toLowerCase().replace(/\b[a-z]/g, function(letter) {
                return letter.toUpperCase();
            });

            str = str.replace(/-/g, function(letter) {
                return ' ';
            });

            return str;
        },

        /**
         * Enable Demo Import Button.
         */
        _enable_demo_import_button: function( type ) {

            type = ( undefined !== type ) ? type : 'free';

            $('.install-theme-info .theme-details .site-description').remove();

            switch( type ) {

                case 'free':
                       // Trigger Demo Import after plugin install and active
                       $(document).trigger( 'whdi-install-and-activate-required-plugins-done' );
                     break;
                default:
                    break;
            }

        },

        _process_import: function() {

            var $theme  = $('.whdi-sites-preview').find('.wp-full-overlay-header'),
                apiURL  = $theme.data('demo-api') || '';

            $('body').addClass('importing-site');
            $('.previous-theme, .next-theme').addClass('disabled');

            // Remove all notices before import start.
            $('.install-theme-info > .notice').remove();

            $('.whdi-demo-import').attr('data-import', 'disabled')
                .addClass('updating-message installing')
                .text( 'Importing...' );

            // Site Import by API URL.
            if( apiURL ) {
                WHDIAdmin._importSite( apiURL );
            }

        },

        /**
         * Start Import Process by API URL.
         *
         * @param  {string} apiURL Site API URL.
         */
        _importSite: function( apiURL ) {

            WHDIAdmin._log_title( 'Started Importing..' );

            // 1. Request Site Import
            $.ajax({
                    url  : WHDI_Admin.ajaxurl,
                    type : 'POST',
                    dataType: 'json',
                    data : {
                        'action'  : 'whdi-import-set-site-data',
                        'api_url' : apiURL,
                        '_ajax_nonce'      : WHDI_Admin._ajax_nonce,
                    },
                })
                .fail(function( jqXHR ){
                    WHDIAdmin._log_title( jqXHR.status + ' ' + jqXHR.responseText + ' ' + jqXHR.statusText, true );
                })
                .done(function ( demo_data ) {
                    // 1. Fail - Request Site Import
                    if( false === demo_data.success ) {
                        WHDIAdmin._importFailMessage( demo_data.data );
                    } else {

                        // Set log file URL.
                        if( 'log_file' in demo_data.data ){
                            WHDIAdmin.log_file_url  = decodeURIComponent( demo_data.data.log_file ) || '';
                        }

                        // 1. Pass - Request Site Import
                        WHDIAdmin.customizer_data = JSON.stringify(demo_data.data['whdi-site-customizer-data']).replace(/\\"/g, '"')
                        WHDIAdmin.wxr_url         = encodeURI( demo_data.data['whdi-site-wxr-path'] ) || '';
                        WHDIAdmin.wpforms_url     = encodeURI( demo_data.data['whdi-site-wpforms-path'] ) || '';
                        WHDIAdmin.options_data    = JSON.stringify( demo_data.data['whdi-site-options-data'] ) || '';
                        WHDIAdmin.widgets_data    = JSON.stringify( demo_data.data['whdi-site-widgets-data'] ) || '';

                        $(document).trigger( 'whdi-import-set-site-data-done' );
                    }

                });

        },

        _log_title: function( data, append ) {

            var markup = '<p>' +  data + '</p>';
            if (typeof data == 'object' ) {
                var markup = '<p>'  + JSON.stringify( data ) + '</p>';
            }

            if ( append ) {
                $('.current-importing-status-title').append( markup );
            } else {
                $('.current-importing-status-title').html( markup );
            }
        },

        /**
         * Import Error Button.
         *
         * @param  {string} data Error message.
         */
        _importFailMessage: function( message ) {

            $('.whdi-demo-import')
                .addClass('go-pro button-primary')
                .removeClass('updating-message installing')
                .removeAttr('data-import')
                .attr('target', '_blank')
                .append('<i class="dashicons dashicons-external"></i>')
                .removeClass('whdi-demo-import');

            WHDIAdmin._log_title( message );
        },

        /**
         * 1. Import WPForms Options.
         */
        _importWPForms: function( event ) {
            if ( WHDIAdmin._is_process_customizer() ) {

                console.log(WHDIAdmin.wpforms_url);

                $.ajax({
                        url  : WHDI_Admin.ajaxurl,
                        type : 'POST',
                        dataType: 'json',
                        data : {
                            action      : 'whdi-import-wpforms',
                            wpforms_url : WHDIAdmin.wpforms_url,
                            _ajax_nonce      : WHDI_Admin._ajax_nonce,
                        },
                        beforeSend: function() {
                            WHDIAdmin._log_title( 'Importing WP Forms..' );
                        },
                    })
                    .fail(function( jqXHR ){
                        console.log(jqXHR);

                        WHDIAdmin._log_title( jqXHR.status + ' ' + jqXHR.responseText, true );
                    })
                    .done(function ( forms ) {

                        console.log(forms);

                        // 1. Fail - Import WPForms Options.
                        if( false === forms.success ) {
                            WHDIAdmin._log_title( forms.data );
                        } else {

                            // 1. Pass - Import Customizer Options.
                            $(document).trigger( 'whdi-import-wpforms-done' );
                        }
                    });
            } else {
                $(document).trigger( 'whdi-import-wpforms-done' );
            }
        },

        /**
         * 1. Import Customizer Options.
         */
        _importCustomizerSettings: function( event ) {
            if ( WHDIAdmin._is_process_customizer() ) {
                $.ajax({
                        url  : WHDI_Admin.ajaxurl,
                        type : 'POST',
                        dataType: 'json',
                        data : {
                            action          : 'whdi-import-customizer-settings',
                            customizer_data : WHDIAdmin.customizer_data,
                            _ajax_nonce     : WHDI_Admin._ajax_nonce,
                        },
                        beforeSend: function() {
                        },
                    })
                    .fail(function( jqXHR ){
                        WHDIAdmin._log_title( jqXHR.status + ' ' + jqXHR.responseText, true );
                    })
                    .done(function ( customizer_data ) {
                        // 1. Fail - Import Customizer Options.
                        if( false === customizer_data.success ) {
                            WHDIAdmin._log_title( customizer_data.data );
                        } else {

                            // 1. Pass - Import Customizer Options.
                            $(document).trigger( 'whdi-import-customizer-settings-done' );
                        }
                    });
            } else {
                $(document).trigger( 'whdi-import-customizer-settings-done' );
            }

        },

        _is_process_customizer: function() {
            if ( $( '.whdi-sites-import-customizer' ).find('.checkbox').is(':checked') ) {
                return true;
            }
            return false;
        },

        /**
         * 2. Prepare XML Data.
         */
        _importXML: function() {

            if ( WHDIAdmin._is_process_xml() ) {
                $.ajax({
                        url  : WHDI_Admin.ajaxurl,
                        type : 'POST',
                        dataType: 'json',
                        data : {
                            action  : 'whdi-import-prepare-xml',
                            wxr_url : WHDIAdmin.wxr_url,
                            _ajax_nonce      : WHDI_Admin._ajax_nonce,
                        },
                        beforeSend: function() {
                            $('.whdi-site-import-process-wrap').show();
                            WHDIAdmin._log_title( 'Importing Content..' );
                        },
                    })
                    .fail(function( jqXHR ){
                        WHDIAdmin._log_title( jqXHR.status + ' ' + jqXHR.responseText, true );
                    })
                    .done(function ( xml_data ) {


                        // 2. Fail - Prepare XML Data.
                        if( false === xml_data.success ) {
                            WHDIAdmin._log_title( xml_data );
                            var error_msg = xml_data.data.error || xml_data.data;
                            WHDIAdmin._log_title( error_msg );

                        } else {

                            var xml_processing = $('.whdi-demo-import').attr( 'data-xml-processing' );

                            if( 'yes' === xml_processing ) {
                                return;
                            }

                            $('.whdi-demo-import').attr( 'data-xml-processing', 'yes' );

                            // 2. Pass - Prepare XML Data.

                            // Import XML though Event Source.
                            WHDISSEImport.data = xml_data.data;
                            WHDISSEImport.render();

                            $('.current-importing-status-description').html('').show();

                            $('.whdi-sites-result-preview .inner').append('<div class="whdi-site-import-process-wrap"><progress class="whdi-site-import-process" max="100" value="0"></progress></div>');

                            var evtSource = new EventSource( WHDISSEImport.data.url );
                            evtSource.onmessage = function ( message ) {
                                var data = JSON.parse( message.data );
                                switch ( data.action ) {
                                    case 'updateDelta':

                                        WHDISSEImport.updateDelta( data.type, data.delta );
                                        break;

                                    case 'complete':
                                        evtSource.close();

                                        $('.current-importing-status-description').hide();
                                        $('.whdi-demo-import').removeAttr( 'data-xml-processing' );

                                        document.getElementsByClassName("whdi-site-import-process").value = '100';

                                        $('.whdi-site-import-process-wrap').hide();

                                        $(document).trigger( 'whdi-import-xml-done' );

                                        break;
                                }
                            };
                            evtSource.addEventListener( 'log', function ( message ) {
                                var data = JSON.parse( message.data );
                                var message = data.message || '';
                                if( message && 'info' === data.level ) {
                                    message = message.replace(/"/g, function(letter) {
                                        return '';
                                    });
                                    $('.current-importing-status-description').html( message );
                                }
                            });
                        }
                    });
            } else {
                $(document).trigger( 'whdi-import-xml-done' );
            }


        },

        _is_process_xml: function() {
            if ( $( '.whdi-sites-import-xml' ).find('.checkbox').is(':checked') ) {
                return true;
            }
            return false;
        },

        /**
         * 3. Import Site Options.
         */
        _importSiteOptions: function( event ) {
            if ( WHDIAdmin._is_process_xml() ) {
                $.ajax({
                        url  : WHDI_Admin.ajaxurl,
                        type : 'POST',
                        dataType: 'json',
                        data : {
                            action       : 'whdi-import-options',
                            options_data : WHDIAdmin.options_data,
                            _ajax_nonce      : WHDI_Admin._ajax_nonce,
                        },
                        beforeSend: function() {
                            $('.whdi-site-import-process-wrap').show();
                            WHDIAdmin._log_title( 'Importing Options..' );
                            $('.whdi-demo-import .percent').html('');
                        },
                    })
                    .fail(function( jqXHR ){
                        WHDIAdmin._log_title( jqXHR.status + ' ' + jqXHR.responseText, true );
                    })
                    .done(function ( options_data ) {

                        // 3. Fail - Import Site Options.
                        if( false === options_data.success ) {
                            WHDIAdmin._log_title( options_data );
                        } else {

                            // 3. Pass - Import Site Options.
                            $(document).trigger( 'whdi-import-options-done' );
                        }
                    });
            } else {
                $(document).trigger( 'whdi-import-options-done' );
            }
        },

        /**
         * 4. Import Widgets.
         */
        _importWidgets: function( event ) {
            if ( WHDIAdmin._is_process_widgets() ) {
                $.ajax({
                        url  : WHDI_Admin.ajaxurl,
                        type : 'POST',
                        dataType: 'json',
                        data : {
                            action       : 'whdi-import-widgets',
                            widgets_data : WHDIAdmin.widgets_data,
                            _ajax_nonce      : WHDI_Admin._ajax_nonce,
                        },
                        beforeSend: function() {
                            WHDIAdmin._log_title( 'Importing Widgets..' );
                        },
                    })
                    .fail(function( jqXHR ){
                        WHDIAdmin._log_title( jqXHR.status + ' ' + jqXHR.responseText, true );
                    })
                    .done(function ( widgets_data ) {
                        // 4. Fail - Import Widgets.
                        if( false === widgets_data.success ) {
                            WHDIAdmin._log_title( widgets_data.data );

                        } else {

                            // 4. Pass - Import Widgets.
                            WHDIAdmin._log_title( 'Imported Widgets!' );
                            $(document).trigger( 'whdi-import-widgets-done' );
                        }
                    });
            } else {
                $(document).trigger( 'whdi-import-widgets-done' );
            }
        },

        _is_process_widgets: function() {
            if ( $( '.whdi-sites-import-widgets' ).find('.checkbox').is(':checked') ) {
                return true;
            }
            return false;
        },

        /**
         * 5. Import Complete.
         */
        _importEnd: function( event ) {

            $.ajax({
                    url  : WHDI_Admin.ajaxurl,
                    type : 'POST',
                    dataType: 'json',
                    data : {
                        action : 'whdi-import-end',
                        _ajax_nonce      : WHDI_Admin._ajax_nonce,
                    },
                    beforeSend: function() {
                        WHDIAdmin._log_title( 'Import Complete!' );
                    }
                })
                .fail(function( jqXHR ){
                    WHDIAdmin._log_title( jqXHR.status + ' ' + jqXHR.responseText + ' ' + jqXHR.statusText, true );
                })
                .done(function ( data ) {

                    // 5. Fail - Import Complete.
                    if( false === data.success ) {
                        WHDIAdmin._log_title( data.data );
                    } else {

                        $('body').removeClass('importing-site');
                        $('.previous-theme, .next-theme').removeClass('disabled');

                        var date = new Date();

                        WHDIAdmin.import_end_time = new Date();
                        var diff    = ( WHDIAdmin.import_end_time.getTime() - WHDIAdmin.import_start_time.getTime() );

                        var time    = '';
                        var seconds = Math.floor( diff / 1000 );
                        var minutes = Math.floor( seconds / 60 );
                        var hours   = Math.floor( minutes / 60 );

                        minutes = minutes - ( hours * 60 );
                        seconds = seconds - ( minutes * 60 );

                        if( hours ) {
                            time += hours + ' Hours ';
                        }
                        if( minutes ) {
                            time += minutes + ' Minutes ';
                        }
                        if( seconds ) {
                            time += seconds + ' Seconds';
                        }

                        var	output  = '<h2>Done 🎉</h2>';
                        output += '<p>Your starter site has been imported successfully in '+time+'! Now go ahead, customize the text, images, and design to make it yours!</p>';
                        output += '<p>You can now start making changes according to your requirements.</p>';
                        output += '<p><a class="button button-primary button-hero" href="'+WHDI_Admin.siteURL+'" target="_blank">View Site <i class="dashicons dashicons-external"></i></a></p>';

                        $('.rotating,.current-importing-status-wrap,.notice-warning').remove();
                        $('.whdi-sites-result-preview .inner').html(output);

                        // 5. Pass - Import Complete.
                        WHDIAdmin._importSuccessButton();
                    }
                });
        },

        /**
         * Import Success Button.
         *
         * @param  {string} data Error message.
         */
        _importSuccessButton: function() {

            $('.whdi-demo-import').removeClass('updating-message installing')
                .removeAttr('data-import')
                .addClass('view-site')
                .removeClass('whdi-demo-import')
                .text( WHDI_Admin.strings.viewSite )
                .attr('target', '_blank')
                .append('<i class="dashicons dashicons-external"></i>')
                .attr('href', WHDI_Admin.siteURL );
        },

        _resetData: function( event ) {
            event.preventDefault();

            if ( $( '.whdi-sites-reset-data' ).find('.checkbox').is(':checked') ) {
                $(document).trigger( 'whdi-reset-data' );
            } else {
                $(document).trigger( 'whdi-reset-data-done' );
            }
        },

        _backup_before_rest_options: function() {
            WHDIAdmin._backupOptions( 'whdi-backup-settings-before-reset-done' );
            WHDIAdmin.backup_taken = true;
        },

        _backupOptions: function( trigger_name ) {
            $.ajax({
                    url  : WHDI_Admin.ajaxurl,
                    type : 'POST',
                    data : {
                        action : 'whdi-backup-settings',
                        _ajax_nonce      : WHDI_Admin._ajax_nonce,
                    },
                    beforeSend: function() {
                        WHDIAdmin._log_title( 'Processing Customizer Settings Backup..' );
                    },
                })
                .fail(function( jqXHR ){
                    WHDIAdmin._log_title( jqXHR.status + ' ' + jqXHR.responseText, true );
                })
                .done(function ( data ) {

                    // 1. Pass - Import Customizer Options.
                    WHDIAdmin._log_title( 'Customizer Settings Backup Done..' );

                    // Custom trigger.
                    $(document).trigger( trigger_name );
                });
        },

        _reset_wp_forms: function() {

            if( WHDIAdmin.site_imported_data['reset_wp_forms'].length ) {
                WHDIAdmin.reset_remaining_wp_forms = WHDIAdmin.site_imported_data['reset_wp_forms'].length;

                $.each( WHDIAdmin.site_imported_data['reset_wp_forms'], function(index, post_id) {
                    WHDIAdmin._log_title( 'Deleting WP Forms..' );
                    WHDISitesAjaxQueue.add({
                        url: WHDI_Admin.ajaxurl,
                        type: 'POST',
                        data: {
                            action  : 'whdi-sites-delete-wp-forms',
                            post_id : post_id,
                            _ajax_nonce      : WHDI_Admin._ajax_nonce,
                        },
                        success: function( result ){

                            if( WHDIAdmin.reset_processed_wp_forms < WHDIAdmin.site_imported_data['reset_wp_forms'].length ) {
                                WHDIAdmin.reset_processed_wp_forms+=1;
                            }

                            WHDIAdmin._log_title( 'Deleting Form ' + WHDIAdmin.reset_processed_wp_forms + ' of ' + WHDIAdmin.site_imported_data['reset_wp_forms'].length + '<br/>' + result.data );

                            WHDIAdmin.reset_remaining_wp_forms-=1;
                            if( 0 == WHDIAdmin.reset_remaining_wp_forms ) {
                                $(document).trigger( 'whdi-delete-wp-forms-done' );
                            }
                        }
                    });
                });
                WHDISitesAjaxQueue.run();

            } else {
                $(document).trigger( 'whdi-delete-wp-forms-done' );
            }
        },

        _reset_posts: function() {
            if( WHDIAdmin.site_imported_data['reset_posts'].length ) {

                WHDIAdmin.reset_remaining_posts = WHDIAdmin.site_imported_data['reset_posts'].length;

                $.each( WHDIAdmin.site_imported_data['reset_posts'], function(index, post_id) {

                    WHDIAdmin._log_title( 'Deleting Posts..' );

                    WHDISitesAjaxQueue.add({
                        url: WHDI_Admin.ajaxurl,
                        type: 'POST',
                        data: {
                            action  : 'whdi-sites-delete-posts',
                            post_id : post_id,
                            _ajax_nonce      : WHDI_Admin._ajax_nonce,
                        },
                        success: function( result ){

                            if( WHDIAdmin.reset_processed_posts < WHDIAdmin.site_imported_data['reset_posts'].length ) {
                                WHDIAdmin.reset_processed_posts+=1;
                            }

                            WHDIAdmin._log_title( 'Deleting Post ' + WHDIAdmin.reset_processed_posts + ' of ' + WHDIAdmin.site_imported_data['reset_posts'].length + '<br/>' + result.data );

                            WHDIAdmin.reset_remaining_posts-=1;
                            if( 0 == WHDIAdmin.reset_remaining_posts ) {
                                $(document).trigger( 'whdi-delete-posts-done' );
                                $(document).trigger( 'whdi-reset-data-done' );
                            }
                        }
                    });
                });
                WHDISitesAjaxQueue.run();

            } else {
                $(document).trigger( 'whdi-delete-posts-done' );
                $(document).trigger( 'whdi-reset-data-done' );
            }
        },


    };

    /**
     * Initialize WHDIAdmin
     */
    $(function(){
        WHDIAdmin.init();
    });

})(jQuery);