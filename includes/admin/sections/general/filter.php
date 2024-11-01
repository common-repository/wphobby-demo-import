<?php
/**
 * WHobby Demo Import Admin Page Filter Section
 */
?>
<div id="whdi-filters">

    <?php if ( apply_filters( 'whdi_show_filters', true ) ) { ?>
        <div class="wp-filter hide-if-no-js">
            <div class="section-left">

                <!-- All Filters -->
                <div class="filter-count">
                    <span class="count"></span>
                </div>
                <div class="filters-wrap" style="display: none;">
                    <div id="whdi-page-builder"></div>
                </div>
                <div class="filters-wrap">
                    <div id="whdi-category"></div>
                </div>

            </div>

            <div class="section-right">

                <div class="search-form">
                    <label class="screen-reader-text" for="wp-filter-search-input"><?php _e( 'Search Sites', 'wphobby-demo-import' ); ?> </label>
                    <input placeholder="<?php _e( 'Search Sites...', 'wphobby-demo-import' ); ?>" type="search" aria-describedby="live-search-desc" id="wp-filter-search-input" class="wp-filter-search">
                </div>

            </div>
        </div>
    <?php } ?>

</div>

<?php
/**
 * TMPL - Filters
 */
?>
<script type="text/template" id="tmpl-whdi-filters">

    <# if ( data ) { #>

        <ul class="{{ data.args.wrapper_class }} {{ data.args.class }}">

            <# if ( data.args.show_all ) { #>
                <li>
                    <a href="#" data-group="all"> All </a>
                </li>
                <# } #>

                    <# for ( key in data.items ) { #>
                        <# if ( data.items[ key ].count ) { #>
                            <li>
                                <a href="#" data-group='{{ data.items[ key ].id }}' class="{{ data.items[ key ].name }}">
                                    {{ data.items[ key ].name }}
                                </a>
                            </li>
                            <# } #>
                                <# } #>

        </ul>
        <# } #>
</script>
