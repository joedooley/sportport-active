<div id="sq_settings" >
    <?php SQ_ObjController::getBlock('SQ_BlockSupport')->init(); ?>
    <?php if (SQ_Tools::$options['sq_api'] == '') { ?>
        <span class="sq_icon"></span>

        <div id="sq_settings_title" ><?php _e('Connect to Squirrly.co', _SQ_PLUGIN_NAME_); ?> </div>
        <div id="sq_settings_login">
            <?php SQ_ObjController::getBlock('SQ_Blocklogin')->init(); ?>
        </div>


        <div class="sq_login_link"><?php _e('Connect to Squirrly and start optimizing your site', _SQ_PLUGIN_NAME_); ?></div>
        <input id="sq_goto_dashboard" style="display:none;  margin: 0 auto; width: 500px; padding: 0px 10px;" type="button" value="&laquo;<?php _e('START HERE', _SQ_PLUGIN_NAME_) ?> &raquo;" />

        <?php
    } else {
        ?>
        <div>
            <span class="sq_icon"></span>
            <div id="sq_settings_title" ><?php _e('Squirrly dashboard', _SQ_PLUGIN_NAME_); ?> </div>
        </div>
        <div id="sq_helpdashboardside" class="sq_helpside"></div>
        <div id="sq_helpdashboardcontent" class="sq_helpcontent"></div>

    <?php } ?>

    <div class="sq_helpcontent" style="display: none; clear: left; <?php echo (SQ_Tools::$options['sq_api'] == '') ? 'text-align: center;' : '' ?>">
        <div style="width: 700px; display: inline-block;">
            <div style="font-size: 24px; margin: 30px 0; color: #999;">All Squirrly Features</div>
            <ul class="sq_slidelist">
                <li>
                    <a href="javascript:void(0);" rel="44987512" style="background-image: url('//image.slidesharecdn.com/kr-150222110827-conversion-gate01/95/squirrly-keyword-research-1-638.jpg?cb=1424624994')"></a>
                    <div>Squirrly Keyword Research</div>
                </li>
                <li>
                    <a href="javascript:void(0);" rel="44987943" style="background-image: url('//image.slidesharecdn.com/sla-150222112751-conversion-gate01/95/squirrly-live-assistant-1-638.jpg?cb=1424626190')"></a>
                    <div>Squirrly Live Assistant</div>
                </li>
                <li>
                    <a href="javascript:void(0);" rel="44987925" style="background-image: url('//image.slidesharecdn.com/snippet1-150222112635-conversion-gate01/95/squirrly-snippet-tool-1-638.jpg?cb=1424626028')"></a>
                    <div>Squirrly Snippet Tool</div>
                </li>
                <li>
                    <a href="javascript:void(0);" rel="45020680" style="background-image: url('//image.slidesharecdn.com/analytics-150223081607-conversion-gate02/95/squirrly-performance-analytics-1-638.jpg?cb=1424701102')"></a>
                    <div>Squirrly Performance Analyticsl</div>
                </li>
                <li>
                    <a href="javascript:void(0);" rel="45062956" style="background-image: url('//image.slidesharecdn.com/firstpage-150224040740-conversion-gate01/95/squirrly-first-page-optimization-1-638.jpg?cb=1427713684')"></a>
                    <div>Squirrly First Page Optimization</div>
                </li>
                <li>
                    <a href="javascript:void(0);" rel="45117764" style="background-image: url('//image.slidesharecdn.com/socialoption-150225050457-conversion-gate02/95/squirrly-open-graph-and-twitter-card-1-638.jpg?cb=1427713066')"></a>
                    <div>Squirrly Open Graph and Twitter Card</div>
                </li>
                <li>
                    <a href="javascript:void(0);" rel="45142191" style="background-image: url('//image.slidesharecdn.com/check-150225143101-conversion-gate02/95/squirrly-check-for-seo-errors-1-638.jpg?cb=1427713151')"></a>
                    <div>Squirrly Check for SEO errors</div>
                </li>
                <li>
                    <a href="javascript:void(0);" rel="46171871" style="background-image: url('//image.slidesharecdn.com/sitemap-150323092133-conversion-gate01/95/squirrly-sitemap-xml-1-638.jpg?cb=1427713209')"></a>
                    <div>Squirrly Sitemap XML</div>
                </li>
                <li>
                    <a href="javascript:void(0);" rel="46209176" style="background-image: url('//image.slidesharecdn.com/favicon-150324035827-conversion-gate01/95/squirrly-faviconico-1-638.jpg?cb=1427713276')"></a>
                    <div>Squirrly Favicon.ico</div>
                </li>
                <li>
                    <a href="javascript:void(0);" rel="46213739" style="background-image: url('//image.slidesharecdn.com/jsonld-150324055711-conversion-gate01/95/squirrly-jsonld-structured-data-1-638.jpg?cb=1427713334')"></a>
                    <div>Squirrly Json-LD Structured Data</div>
                </li>
                <li>
                    <a href="javascript:void(0);" rel="46218043" style="background-image: url('//image.slidesharecdn.com/tracking-150324074838-conversion-gate01/95/squirrly-tracking-tools-1-638.jpg?cb=1427713384')"></a>
                    <div>Squirrly Tracking Tools</div>
                </li>
                <li>
                    <a href="javascript:void(0);" rel="46219965" style="background-image: url('//image.slidesharecdn.com/types-150324083302-conversion-gate01/95/squirrly-settings-for-posts-and-pages-1-638.jpg?cb=1427713476')"></a>
                    <div>Squirrly Settings for Posts and Pages</div>
                </li>
                <li>
                    <a href="javascript:void(0);" rel="46220852" style="background-image: url('//image.slidesharecdn.com/ranking-150324085252-conversion-gate01/95/squirrly-google-rank-option-1-638.jpg?cb=1427713539')"></a>
                    <div>Squirrly Google Rank Option</div>
                </li>
                <li>
                    <a href="javascript:void(0);" rel="46222827" style="background-image: url('//image.slidesharecdn.com/success-150324093815-conversion-gate01/95/measure-your-success-option-from-squirrly-1-638.jpg?cb=1427713584')"></a>
                    <div>Measure Your Success Option from Squirrly</div>
                </li>
                <li>
                    <a href="javascript:void(0);" rel="46256414" style="background-image: url('//image.slidesharecdn.com/robots-150325031929-conversion-gate01/95/squirrly-robotstxt-1-638.jpg?cb=1427713635')"></a>
                    <div>Squirrly Robots.txt</div>
                </li>
                <li>
                    <a href="javascript:void(0);" rel="46440187" style="background-image: url('//image.slidesharecdn.com/audit-150330042921-conversion-gate01/95/squirrly-site-audit-1-638.jpg?cb=1427707809')"></a>
                    <div>Squirrly Site Audit</div>
                </li>
            </ul>
        </div>
    </div>
</div>
