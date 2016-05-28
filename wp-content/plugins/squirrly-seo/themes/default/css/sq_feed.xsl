<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:feedpress="https://feed.press/xmlns" version="1.0">
    <xsl:variable name="freeVersion">false</xsl:variable>
    <xsl:include href="sq_feedstyle.xsl"/>
    <xsl:template match="/">
        <xsl:element name="html">
            <xsl:copy-of select="$html_TemplateHeader"/>
            <xsl:apply-templates select="rss/channel"/>
        </xsl:element>
    </xsl:template>
    <xsl:template match="channel">
        <body>
            <div id="cometestme" style="display:none;">
                <xsl:text disable-output-escaping="yes">&amp;</xsl:text>
            </div>
            <div id="main">
                <div id="header" class="cf">
                    <xsl:apply-templates select="image"/>
                    <xsl:copy-of select="$html_FeedHeader"/>
                </div>

                <ul>
                    <xsl:apply-templates select="item"/>
                </ul>
                <xsl:copy-of select="$html_FeedFooter"/>
            </div>

        </body>
    </xsl:template>
</xsl:stylesheet>

