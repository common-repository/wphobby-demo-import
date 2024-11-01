<?php
/**
 * WHobby Demo Import Admin Page Top Section
 */
?>
<div id="whdi-sites-menu-page">
</div>
<?php
/**
 * TMPL - Single Demo Preview
 */
?>
<script type="text/template" id="tmpl-whdi-site-preview">
    <div class="whdi-sites-preview theme-install-overlay wp-full-overlay expanded">
        <div class="wp-full-overlay-sidebar">
            <div class="wp-full-overlay-header"
                 data-demo-id="{{{data.id}}}"
                 data-demo-type="{{{data.whdi_demo_type}}}"
                 data-demo-url="{{{data.whdi_demo_url}}}"
                 data-demo-api="{{{data.demo_api}}}"
                 data-demo-name="{{{data.demo_name}}}"
                 data-demo-slug="{{{data.slug}}}"
                 data-screenshot="{{{data.screenshot}}}"
                 data-content="{{{data.content}}}"
                 data-required-plugins="{{data.required_plugins}}">
                <input type="hidden" class="whdi-site-options" value="{{data.whdi_site_options}}" >
                <input type="hidden" class="whdi-enabled-extensions" value="{{data.whdi_enabled_extensions}}" >
                <button class="close-full-overlay"><span class="screen-reader-text"><?php esc_html_e( 'Close', 'wphobby-demo-import' ); ?></span></button>
                <button class="previous-theme"><span class="screen-reader-text"><?php esc_html_e( 'Previous', 'wphobby-demo-import' ); ?></span></button>
                <button class="next-theme"><span class="screen-reader-text"><?php esc_html_e( 'Next', 'wphobby-demo-import' ); ?></span></button>
                <!-- <a class="button hide-if-no-customize whdi-site-import" href="#" data-import="disabled"><?php esc_html_e( 'Import Site', 'wphobby-demo-import' ); ?></a> -->
                <a class="button hide-if-no-customize button-primary whdi-demo-import" href="#" data-import="disabled"><?php esc_html_e( 'Import Site', 'wphobby-demo-import' ); ?></a>

            </div>
            <div class="wp-full-overlay-sidebar-content">
                <div class="install-theme-info">

                    <span class="site-type {{{data.whdi_demo_type}}}">{{{data.whdi_demo_type}}}</span>
                    <h3 class="theme-name">{{{data.demo_name}}}</h3>

                    <# if ( data.screenshot.length ) { #>
                        <div class="theme-screenshot-wrap">
                            <img class="theme-screenshot" src="{{{data.screenshot}}}" alt="">
                        </div>
                        <# } #>

                            <div class="theme-details">
                                {{{data.content}}}
                            </div>
                            <a href="#" class="theme-details-read-more"><?php _e( 'Read more', 'wphobby-demo-import' ); ?> &hellip;</a>

                            <div class="whdi-sites-advanced-options-wrap">

                                <div class="whdi-sites-advanced-options">

                                    <ul class="whdi-site-contents">
                                        <li class="whdi-sites-import-plugins">
                                            <input type="checkbox" name="plugins" checked="checked" class="disabled checkbox" readonly>
                                            <strong><?php _e( 'Install Required Plugins', 'wphobby-demo-import' ); ?></strong>
                                            <span class="whdi-sites-tooltip-icon" data-tip-id="whdi-sites-tooltip-plugins-settings"><span class="dashicons dashicons-editor-help"></span></span>
                                            <div class="whdi-sites-tooltip-message" id="whdi-sites-tooltip-plugins-settings" style="display: none;">
                                                <ul class="required-plugins-list"><span class="spinner is-active"></span></ul>
                                            </div>
                                        </li>
                                        <li class="whdi-sites-import-customizer">
                                            <label>
                                                <input type="checkbox" name="customizer" checked="checked" class="checkbox">
                                                <strong>Import Customizer Settings</strong>
                                                <span class="whdi-sites-tooltip-icon" data-tip-id="whdi-sites-tooltip-customizer-settings"><span class="dashicons dashicons-editor-help"></span></span>
                                                <div class="whdi-sites-tooltip-message" id="whdi-sites-tooltip-customizer-settings" style="display: none;">
                                                    <p><?php _e( 'Customizer is what gives a design to the website; and selecting this option replaces your current design with a new one.', 'wphobby-demo-import' ); ?></p>
                                                    <p><?php _e( 'Backup of current customizer settings will be stored in "wp-content/whdi-sites" directory, just in case if you want to restore it later.', 'wphobby-demo-import' ); ?></p>
                                                </div>
                                            </label>
                                        </li>
                                        <li class="whdi-sites-import-xml">
                                            <label>
                                                <input type="checkbox" name="xml" checked="checked" class="checkbox">
                                                <strong>Import Content</strong>
                                            </label>
                                            <span class="whdi-sites-tooltip-icon" data-tip-id="whdi-sites-tooltip-site-content"><span class="dashicons dashicons-editor-help"></span></span>
                                            <div class="whdi-sites-tooltip-message" id="whdi-sites-tooltip-site-content" style="display: none;"><p><?php _e( 'Selecting this option will import dummy pages, posts, images and menus. If you do not want to import dummy content, please uncheck this option.', 'wphobby-demo-import' ); ?></p></div>
                                        </li>
                                        <li class="whdi-sites-import-widgets">
                                            <label>
                                                <input type="checkbox" name="widgets" checked="checked" class="checkbox">
                                                <strong>Import Widgets</strong>
                                            </label>
                                        </li>
                                    </ul>
                                </div>

                                <ul>
                                    <li class="whdi-sites-reset-data">
                                        <label>
                                            <input type="checkbox" name="reset" checked="checked" class="checkbox">
                                            <strong>Delete Previously Imported Site</strong>
                                            <div class="whdi-sites-tooltip-message" id="whdi-sites-tooltip-reset-data" style="display: none;"><p><?php _e( 'WARNING: Selecting this option will delete data from your current website. Choose this option only if this is intended.', 'wphobby-demo-import' ); ?></p></div>
                                        </label>
                                    </li>
                                </ul>

                                <!-- <p><a href="#" class="whdi-sites-advanced-options-button"><?php _e( 'Advanced Options', 'wphobby-demo-import' ); ?></a></p> -->

                            </div>

                            <!-- <div class="whdi-sites-advanced-options">
						<h4><?php _e( 'Required Plugins', 'wphobby-demo-import' ); ?> </h4>
						<div class="required-plugins"></div>
					</div> -->
                </div>
            </div>

            <div class="wp-full-overlay-footer">
                <div class="footer-import-button-wrap">
                    <a class="button button-hero hide-if-no-customize button-primary whdi-demo-import" href="#" data-import="disabled">
                        <?php esc_html_e( 'Import Site', 'wphobby-demo-import' ); ?>
                        <span class="percent"></span>
                    </a>
                    <div class="whdi-site-import-process-wrap" style="display: none;">
                        <progress class="whdi-site-import-process" max="100" value="0"></progress>
                    </div>
                    <!-- <a class="button button-hero hide-if-no-customize whdi-site-import" href="#">
						<?php esc_html_e( 'Import Site', 'wphobby-demo-import' ); ?>
					</a> -->
                </div>
                <button type="button" class="collapse-sidebar button" aria-expanded="true"
                        aria-label="Collapse Sidebar">
                    <span class="collapse-sidebar-arrow"></span>
                    <span class="collapse-sidebar-label"><?php esc_html_e( 'Collapse', 'wphobby-demo-import' ); ?></span>
                </button>

                <div class="devices-wrapper">
                    <div class="devices">
                        <button type="button" class="preview-desktop active" aria-pressed="true" data-device="desktop">
                            <span class="screen-reader-text"><?php _e( 'Enter desktop preview mode', 'wphobby-demo-import' ); ?></span>
                        </button>
                        <button type="button" class="preview-tablet" aria-pressed="false" data-device="tablet">
                            <span class="screen-reader-text"><?php _e( 'Enter tablet preview mode', 'wphobby-demo-import' ); ?></span>
                        </button>
                        <button type="button" class="preview-mobile" aria-pressed="false" data-device="mobile">
                            <span class="screen-reader-text"><?php _e( 'Enter mobile preview mode', 'wphobby-demo-import' ); ?></span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
        <div class="wp-full-overlay-main">
            <iframe src="{{{data.whdi_demo_url}}}" title="<?php esc_attr_e( 'Preview', 'wphobby-demo-import' ); ?>"></iframe>
            <div class="whdi-sites-result-preview" style="display: none;">
                <div class="inner">
                    <h2><?php _e( 'We\'re importing your website.', 'wphobby-demo-import' ); ?></h2>
                    <p><?php _e( 'The process can take anywhere between 2 to 10 minutes depending on the size of the website and speed of connection.', 'wphobby-demo-import' ); ?></p>
                    <p><?php _e( 'Please do not close this browser window until the site is imported completely.', 'wphobby-demo-import' ); ?></p>
                    <div class="current-importing-status-wrap">
                        <div class="current-importing-status">
                            <div class="current-importing-status-title"></div>
                            <div class="current-importing-status-description"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>
