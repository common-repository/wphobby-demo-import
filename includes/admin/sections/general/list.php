<?php
/**
 * Created by PhpStorm.
 * User: darell
 * Date: 2020/2/19
 * Time: 1:50 PM
 */
?>

<div class="theme-browser rendered">
    <div id="whdi-themes" class="themes wp-clearfix"></div>
</div>
<div class="spinner-wrap">
    <span class="spinner"></span>
</div>
<?php
/**
 * TMPL - List
 */
?>
<script type="text/template" id="tmpl-whdi-list">

    <# if ( data.items.length ) { #>
        <# for ( key in data.items ) { #>

            <div class="theme whdi-theme site-single {{ data.items[ key ].status }}" tabindex="0" aria-describedby="whdi-theme-action whdi-theme-name"
                 data-demo-id="{{{ data.items[ key ].id }}}"
                 data-demo-type="{{{ data.items[ key ]['whdi-site-type'] }}}"
                 data-demo-url="{{{ data.items[ key ]['whdi-site-url'] }}}"
                 data-demo-api="{{{ data.items[ key ]['_links']['self'][0]['href'] }}}"
                 data-demo-name="{{{  data.items[ key ].title.rendered }}}"
                 data-demo-slug="{{{  data.items[ key ].slug }}}"
                 data-screenshot="{{{ data.items[ key ]['featured-image-url'] }}}"
                 data-content="{{{ data.items[ key ].content.rendered }}}"
                 data-required-plugins="{{ JSON.stringify( data.items[ key ]['required-plugins'] ) }}"
                 data-groups=["{{ data.items[ key ].tags }}"]>
            <input type="hidden" class="whdi-site-options" value="{{ JSON.stringify(data.items[ key ]['whdi-site-options-data'] ) }}" />
            <input type="hidden" class="whdi-enabled-extensions" value="{{ JSON.stringify(data.items[ key ]['whdi-enabled-extensions'] ) }}" />

            <div class="inner">
					<span class="site-preview" data-href="{{ data.items[ key ]['whdi-site-url'] }}?TB_iframe=true&width=600&height=550" data-title="{{ data.items[ key ].title.rendered }}">
						<div class="theme-screenshot" style="background-image: url('{{ data.items[ key ]['featured-image-url'] }}');"></div>
					</span>
                <# if ( data.items[ key ]['whdi-site-type'] ) { #>
                    <# var type = ( data.items[ key ]['whdi-site-type'] !== 'premium' ) ? ( data.items[ key ]['whdi-site-type'] ) : 'agency'; #>
                        <span class="site-type {{data.items[ key ]['whdi-site-type']}}">{{ type }}</span>
                        <# } #>
                            <# if ( data.items[ key ].status ) { #>
                                <span class="status {{data.items[ key ].status}}">{{data.items[ key ].status}}</span>
                                <# } #>
                                    <div class="theme-id-container">
                                        <h3 class="theme-name" id="whdi-theme-name"> {{{ data.items[ key ].title.rendered }}} </h3>
                                        <div class="theme-actions">
                                            <button class="button-primary button preview install-theme-preview"><?php esc_html_e( 'Preview', 'wphobby-demo-import' ); ?></button>
                                        </div>
                                    </div>
            </div>
            </div>
            <# } #>
                <# } else { #>
                    <p class="no-themes" style="display:block;">
                        <?php _e( 'No Demos found, Try a different search.', 'wphobby-demo-import' ); ?>
                        <span class="description">
				<?php
                /* translators: %1$s External Link */
                printf( __( 'Don\'t see a site that you would like to import?<br><a target="_blank" href="%1$s">Please suggest us!</a>', 'wphobby-demo-import' ), esc_url( 'https://hublip.com/sites-suggestions/?utm_source=demo-import-panel&utm_campaign=whdi-sites&utm_medium=suggestions' ) );
                ?>
			</span>
                    </p>
                    <# } #>
</script>