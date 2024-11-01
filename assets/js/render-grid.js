(function($){

    WHDIRender = {

        /**
         * _api_params = {
		 * 		'search'                  : '',
		 * 		'per_page'                : '',
		 * 		'whdi-category'           : '',
		 * 		'page'                    : '',
		 *   };
         *
         */
        _api_params	  : {},

        _api_url      : WHDIApi.ApiURL,


        init: function()
        {
            this._bind();

            if( $('#whdi-themes').length ) {
                $.getJSON( WHDIRender._api_url + 'all-category.json', function( data ) {
                    $(document).trigger( 'whdi-category-loaded', data);
                });

                $.getJSON( "https://hublip.com/whdi/json/0-category.json", function( data ) {
                    $(document).trigger( 'whdi-post-loaded', data);
                });
            }
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
            $( document ).on('whdi-category-loaded' , WHDIRender._loadFirstGrid );
            $( document ).on('whdi-post-loaded'     , WHDIRender._reinitGrid );

            $( document ).on('click', '.filter-links a', WHDIRender._filterClick );

        },

        /**
         * Load First Grid.
         *
         * This is triggered after all category loaded.
         *
         * @param  {object} event Event Object.
         */
        _loadFirstGrid: function( event, data ) {

            event.preventDefault();
            
            $('body').addClass('loading-content');
            $('#whdi-admin').find('.spinner').addClass('is-active');


            if( $('#' + data.args.id).length ) {
                var template = wp.template('whdi-filters');
                $('#' + data.args.id).html(template( data ));
            }

        },

        /**
         * Update WHDI list.
         *
         * @param  {object} event Object.
         * @param  {object} data  API response data.
         */
        _reinitGrid: function( event, data ) {

            var template = wp.template('whdi-list');

            $('body').addClass( 'page-builder-selected' );
            $('body').removeClass( 'loading-content' );
            $('#whdi-admin').find('.spinner').removeClass('is-active');

            $('.filter-count .count').text( data.items_count );

            jQuery('body').attr('data-whdi-demo-last-request', data.items_count);

            jQuery('#whdi-themes').show().html(template( data ));


        },

        /**
         * On Filter Clicked
         *
         * Prepare Before API Request:
         * - Empty search input field to avoid search term on filter click.
         * - Remove Inline Height
         * - Added 'hide-me' class to hide the 'No more sites!' string.
         * - Added 'loading-content' for body.
         * - Show spinner.
         */
        _filterClick: function( event ) {

            event.preventDefault();

            $(this).parents('.filter-links').find('a').removeClass('current');
            $(this).addClass('current');

            // Add 'whdi-category'
            var selected_category_id = jQuery('.filter-links.whdi-category').find('.current').data('group') || '';
            if( '' !== selected_category_id && 'all' !== selected_category_id ) {
                WHDIRender._api_params['whdi-category'] =  selected_category_id;
            }else {
                WHDIRender._api_params['whdi-category'] =  '0';
            }

            $('body').addClass('loading-content');

            // Show themes.
            var category = '';
            if( undefined === WHDIRender._api_params['whdi-category'] ) {
                category = 'all-category';
            }else{
                category = WHDIRender._api_params['whdi-category'] + '-category';
            }

            var category_json = WHDIRender._api_url + category + '.json';
            
            $.getJSON( category_json, function( data ) {
                $(document).trigger( 'whdi-category-loaded', data);
                $(document).trigger( 'whdi-post-loaded', data);
            });
        },

    };

    /**
     * Initialize WHDIRender
     */
    $(function(){
        WHDIRender.init();
    });

})(jQuery);