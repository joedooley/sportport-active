<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:feedpress="https://feed.press/xmlns" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="1.0">
    <!--  Doctype  -->
    <xsl:output method="html"/>
    <!--  Common variables  -->
    <xsl:variable name="language">
        <xsl:choose>
            <xsl:when test="/rss/channel/language">
                <xsl:value-of select="/rss/channel/language"/>
            </xsl:when>
            <xsl:otherwise>en</xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:variable name="locale">
        <xsl:choose>
            <xsl:when test="/rss/channel/feedpress:locale">
                <xsl:value-of select="/rss/channel/feedpress:locale"/>
            </xsl:when>
            <xsl:when test="/atom:feed/feedpress:locale">
                <xsl:value-of select="/atom:feed/feedpress:locale"/>
            </xsl:when>
            <xsl:otherwise>en</xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:variable name="title">
        <xsl:choose>
            <xsl:when test="/rss/channel/title">
                <xsl:value-of select="/rss/channel/title"/>
            </xsl:when>
            <xsl:when test="atom:feed/atom:title">
                <xsl:value-of select="atom:feed/atom:title"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:variable name="subTitle">
        <xsl:choose>
            <xsl:when test="/rss/channel/itunes:subtitle">
                <xsl:value-of select="/rss/channel/itunes:subtitle"/>
            </xsl:when>
            <xsl:when test="/rss/channel/description">
                <xsl:value-of select="/rss/channel/description"/>
            </xsl:when>
            <xsl:when test="atom:feed/atom:subtitle">
                <xsl:value-of select="atom:feed/atom:subtitle"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:variable name="podcastId">
        <xsl:choose>
            <xsl:when test="/rss/channel/feedpress:podcastId">
                <xsl:value-of select="/rss/channel/feedpress:podcastId"/>
            </xsl:when>
            <xsl:when test="/atom:feed/feedpress:podcastId">
                <xsl:value-of select="/atom:feed/feedpress:podcastId"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:variable xmlns:atom10="http://www.w3.org/2005/Atom" name="feedUrl">
        <xsl:choose>
            <xsl:when test="/rss/channel/atom10:link[@rel='via']/@href">
                <xsl:value-of select="/rss/channel/atom10:link[@rel='via']/@href"/>
            </xsl:when>
            <xsl:when test="atom:feed/atom:link[@rel='via']/@href">
                <xsl:value-of select="atom:feed/atom:link[@rel='via']/@href"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:choose>
                    <xsl:when test="/rss/channel/atom10:link[@rel='self']/@href">
                        <xsl:value-of select="/rss/channel/atom10:link[@rel='self']/@href"/>
                    </xsl:when>
                    <xsl:when test="atom:feed/atom:link[@rel='self']/@href">
                        <xsl:value-of select="atom:feed/atom:link[@rel='self']/@href"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:variable name="siteUrl">
        <xsl:choose>
            <xsl:when test="atom:feed/atom:link[@rel='alternate']/@href">
                <xsl:value-of select="atom:feed/atom:link[@rel='alternate']/@href"/>
            </xsl:when>
            <xsl:when test="/rss/channel/link">
                <xsl:value-of select="/rss/channel/link"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:variable name="newsletterId">
        <xsl:choose>
            <xsl:when test="/rss/channel/feedpress:newsletterId">
                <xsl:value-of select="/rss/channel/feedpress:newsletterId"/>
            </xsl:when>
            <xsl:when test="/atom:feed/feedpress:newsletterId">
                <xsl:value-of select="/atom:feed/feedpress:newsletterId"/>
            </xsl:when>
            <xsl:otherwise>none</xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:variable name="cssFile">
        <xsl:choose>
            <xsl:when test="/rss/channel/feedcss">
                <xsl:value-of select="/rss/channel/feedcss"/>
            </xsl:when>
            <xsl:when test="/atom:feed/feedcss">
                <xsl:value-of select="/atom:feed/feedcss"/>
            </xsl:when>
            <xsl:otherwise>/sqfeedcss</xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <!--  Localization  -->
    <xsl:variable name="lang_Subscribe">
        <xsl:choose>
            <xsl:when test="$locale='fr'">Inscription</xsl:when>
            <xsl:when test="$locale='ru'">Подписаться</xsl:when>
            <xsl:when test="$locale='pt'">Subscrever</xsl:when>
            <xsl:when test="$locale='ca'">Subscriu-t’hi</xsl:when>
            <xsl:when test="$locale='it'">Iscriviti</xsl:when>
            <xsl:when test="$locale='es'">Suscríbete</xsl:when>
            <xsl:otherwise>Subscribe</xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:variable name="lang_CopyPaste">
        <xsl:choose>
            <xsl:when test="$locale='fr'">
                Copiez et collez l'adresse du flux dans votre lecteur RSS préféré pour vous inscrire :
            </xsl:when>
            <xsl:when test="$locale='ru'">
                Скопируйте и вставьте ссылку на фид в свой любимый ридер, чтобы подписаться.
            </xsl:when>
            <xsl:when test="$locale='pt'">
                Copie e cole o atalho do feed no seu leitor favorito para subscrever:
            </xsl:when>
            <xsl:when test="$locale='engb'">
                Copy and paste the feed URL in your favourite reader to subscribe:
            </xsl:when>
            <xsl:when test="$locale='enca'">
                Copy and paste the feed URL in your favourite reader to subscribe:
            </xsl:when>
            <xsl:when test="$locale='ca'">
                Copia i enganxa l’adreça web del “feed” al teu lector preferit per subscriure-t’hi:
            </xsl:when>
            <xsl:when test="$locale='it'">
                Per iscriverti copia e incolla l'URL del feed nel reader preferito:
            </xsl:when>
            <xsl:when test="$locale='es'">
                Copia y pega la dirección RSS en tu lector de noticias favorito para suscribirte:
            </xsl:when>
            <xsl:otherwise>
                Copy and paste the feed URL in your favorite reader to subscribe:
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:variable name="lang_ClickLink">
        <xsl:choose>
            <xsl:when test="$locale='fr'">Ou cliquez directement sur un de ces liens :</xsl:when>
            <xsl:when test="$locale='ru'">Или нажмите на одну из этих ссылок:</xsl:when>
            <xsl:when test="$locale='pt'">Ou clique directamente num destes atalhos:</xsl:when>
            <xsl:when test="$locale='ca'">O clica directament un dels enllaços:</xsl:when>
            <xsl:when test="$locale='it'">Oppure fai click su uno di questi link:</xsl:when>
            <xsl:when test="$locale='es'">O pulsa directamente en uno de estos enlaces:</xsl:when>
            <xsl:otherwise>Or directly click on one of these links:</xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:variable name="lang_SubscribeEmail">
        <xsl:choose>
            <xsl:when test="$locale='fr'">
                S'inscrire à
                <xsl:value-of select="$title"/>
                par email
            </xsl:when>
            <xsl:when test="$locale='ru'">
                Подпишитесь на
                <xsl:value-of select="$title"/>
                по E-mail
            </xsl:when>
            <xsl:when test="$locale='pt'">
                Subscreva o feed
                <xsl:value-of select="$title"/>
                por email
            </xsl:when>
            <xsl:when test="$locale='ca'">
                Subscriu-te a
                <xsl:value-of select="$title"/>
                per correu electrònic
            </xsl:when>
            <xsl:when test="$locale='it'">
                Iscriviti a
                <xsl:value-of select="$title"/>
                via email
            </xsl:when>
            <xsl:when test="$locale='es'">
                Suscríbete a
                <xsl:value-of select="$title"/>
                por correo
            </xsl:when>
            <xsl:otherwise>
                Subscribe to
                <xsl:value-of select="$title"/>
                by email
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:variable name="lang_Posted">
        <xsl:choose>
            <xsl:when test="$locale='fr'">Posté le :</xsl:when>
            <xsl:when test="$locale='ru'">Опубликовано:</xsl:when>
            <xsl:when test="$locale='pt'">Publicado:</xsl:when>
            <xsl:when test="$locale='ca'">Enviat:</xsl:when>
            <xsl:when test="$locale='it'">Pubblicato:</xsl:when>
            <xsl:when test="$locale='es'">Publicado:</xsl:when>
            <xsl:otherwise>Posted:</xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:variable name="lang_Enclosure">
        <xsl:choose>
            <xsl:when test="$locale='fr'">Fichier joint :</xsl:when>
            <xsl:when test="$locale='pt'">Anexo:</xsl:when>
            <xsl:when test="$locale='ca'">Arxiu:</xsl:when>
            <xsl:when test="$locale='it'">Allegato:</xsl:when>
            <xsl:otherwise>Enclosure:</xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:variable name="lang_ProvidedBy">
        <xsl:choose>
            <xsl:when test="$locale='fr'">fourni par FeedPress</xsl:when>
            <xsl:when test="$locale='pt'">fornecido por FeedPress</xsl:when>
            <xsl:otherwise>provided by FeedPress</xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <!--  Templates  -->
    <xsl:template xmlns:dc="http://purl.org/dc/elements/1.1/" match="item|atom:entry">
        <li class="regularitem">
            <h4 class="itemtitle">
                <xsl:choose>
                    <xsl:when test="guid[@isPermaLink='true']">
                        <a href="{normalize-space(guid)}">
                            <xsl:value-of select="title"/>
                        </a>
                    </xsl:when>
                    <xsl:when test="link">
                        <a href="{normalize-space(link)}">
                            <xsl:value-of select="title"/>
                        </a>
                    </xsl:when>
                    <xsl:when test="guid[@isPermaLink='true']">
                        <a href="{normalize-space(guid)}">
                            <xsl:value-of select="atom:title"/>
                        </a>
                    </xsl:when>
                    <xsl:when test="atom:link">
                        <a href="{normalize-space(atom:link/@href)}">
                            <xsl:value-of select="atom:title"/>
                        </a>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:choose>
                            <xsl:when test="atom:title">
                                 <xsl:variable name="atitle" select="atom:title" />
                                <xsl:value-of select="atom:title"/>
                            </xsl:when>
                            <xsl:otherwise>
                                 <xsl:variable name="atitle" select="title" />
                                <xsl:value-of select="title"/>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:otherwise>
                </xsl:choose>
            </h4>
            <h5 class="itemposttime">
                <xsl:if test="count(child::pubDate)=1">
                    <span>
                        <xsl:value-of select="$lang_Posted"/>
                    </span>
                    <span class="itemposttime-data">
                        <xsl:value-of select="pubDate"/>
                    </span>
                </xsl:if>
                <xsl:if test="count(child::dc:date)=1">
                    <span>
                        <xsl:value-of select="$lang_Posted"/>
                    </span>
                    <span class="itemposttime-data">
                        <xsl:value-of select="dc:date"/>
                    </span>
                </xsl:if>
            </h5>
            <div class="itemcontent" name="decodeable">
                <xsl:call-template name="outputContent"/>
            </div>
            <xsl:choose>
                <xsl:when test="count(child::enclosure)=1">
                    <p class="mediaenclosure">
                        <xsl:value-of select="$lang_Enclosure"/>
                        <a href="{enclosure/@url}">
                            <xsl:value-of select="child::enclosure/@url"/>
                        </a>
                    </p>
                    <xsl:if test="contains(enclosure/@url, '.mp3')">
                        <audio controls="enabled" preload="none">
                            <source src="{enclosure/@url}" type="audio/mpeg"/>
                        </audio>
                    </xsl:if>
                    <xsl:if test="contains(enclosure/@url, '.m4a')">
                        <audio controls="enabled" preload="none">
                            <source src="{enclosure/@url}" type="audio/mp4"/>
                        </audio>
                    </xsl:if>
                    <xsl:if test="contains(enclosure/@url, '.mp4')">
                        <div class="player">
                            <video controls="controls" preload="metadata">
                                <source src="{enclosure/@url}" type="video/mp4"/>
                            </video>
                        </div>
                    </xsl:if>
                    <xsl:if test="contains(enclosure/@url, '.m4v')">
                        <div class="player">
                            <video controls="controls" preload="metadata">
                                <source src="{enclosure/@url}" type="video/mp4"/>
                            </video>
                        </div>
                    </xsl:if>
                </xsl:when>
                <xsl:when test="count(child::atom:link[@rel=enclosure])=1">
                    <p class="mediaenclosure">
                        <xsl:value-of select="$lang_Enclosure"/>
                        <a href="{child::atom:link[@rel=enclosure]/@url}">
                            <xsl:value-of select="child::atom:link[@rel=enclosure]/@url"/>
                        </a>
                    </p>
                    <xsl:if test="contains(child::atom:link[@rel=enclosure]/@url, '.mp3')">
                        <audio controls="enabled" preload="none">
                            <source src="{child::atom:link[@rel=enclosure]/@url}" type="audio/mpeg"/>
                        </audio>
                    </xsl:if>
                    <xsl:if test="contains(child::atom:link[@rel=enclosure]/@url, '.m4a')">
                        <audio controls="enabled" preload="none">
                            <source src="{child::atom:link[@rel=enclosure]/@url}" type="audio/mp4"/>
                        </audio>
                    </xsl:if>
                    <xsl:if test="contains(child::atom:link[@rel=enclosure]/@url, '.mp4')">
                        <div class="player">
                            <video controls="controls" preload="metadata">
                                <source src="{child::atom:link[@rel=enclosure]/@url}" type="video/mp4"/>
                            </video>
                        </div>
                    </xsl:if>
                    <xsl:if test="contains(child::atom:link[@rel=enclosure]/@url, '.m4v')">
                        <div class="player">
                            <video controls="controls" preload="metadata">
                                <source src="{child::atom:link[@rel=enclosure]/@url}" type="video/mp4"/>
                            </video>
                        </div>
                    </xsl:if>
                </xsl:when>
            </xsl:choose>
        </li>
    </xsl:template>
    <xsl:template match="image">
        <a href="{normalize-space(link)}" title="{title}">
            <img src="{url}" id="feedimage" alt="{title}"/>
        </a>
        <xsl:text/>
    </xsl:template>

    <xsl:template match="logo">
        <img src="{logo}" id="feedimage"/>
        <xsl:text/>
    </xsl:template>


    <xsl:template name="outputContent">
        <xsl:choose>
            <xsl:when xmlns:xhtml="http://www.w3.org/1999/xhtml" test="xhtml:body">
                <xsl:copy-of select="xhtml:body/*"/>
            </xsl:when>
            <xsl:when xmlns:xhtml="http://www.w3.org/1999/xhtml" test="xhtml:div">
                <xsl:copy-of select="xhtml:div"/>
            </xsl:when>
            <xsl:when xmlns:content="http://purl.org/rss/1.0/modules/content/" test="content:encoded">
                <xsl:value-of select="content:encoded" disable-output-escaping="yes"/>
            </xsl:when>
            <xsl:when test="atom:content">
                <xsl:value-of select="atom:content" disable-output-escaping="yes"/>
            </xsl:when>
            <xsl:when test="description">
                <xsl:value-of select="description" disable-output-escaping="yes"/>
            </xsl:when>
            <xsl:when test="atom:summary">
                <xsl:value-of select="atom:summary" disable-output-escaping="yes"/>
            </xsl:when>
        </xsl:choose>
    </xsl:template>
    <xsl:variable name="html_Subscribe">
        <div id="subscribe">
            <div id="feedinput">
                <h4>
                    <xsl:value-of select="$lang_CopyPaste"/>
                </h4>
                <input type="text" name="feed" value="{$feedUrl}"/>
                <input type="button" onclick="subscribeButton();" value="{$lang_Subscribe}"/>
            </div>
            <div id="readerslinks">
                <h4>
                    <xsl:value-of select="$lang_ClickLink"/>
                </h4>
                <xsl:choose>
                    <xsl:when test="$locale='jp'">
                        <a class="subscribe" href="http://rd.yahoo.co.jp/myyahoo/rss/addtomy/users/*http://add.my.yahoo.co.jp/rss?url={$feedUrl}">
                            <img src="http://i.yimg.jp/i/jp/my/addtomy/standard_bb.gif" width="91" height="17" alt="My Yahoo!に追加"/>
                        </a>
                    </xsl:when>
                    <xsl:otherwise>
                        <a class="subscribe" href="http://add.my.yahoo.com/rss?url={$feedUrl}">
                            <img src="http://us.i1.yimg.com/us.yimg.com/i/us/my/addtomyyahoo4.gif" width="91" height="17" alt="My Yahoo!"/>
                        </a>
                    </xsl:otherwise>
                </xsl:choose>
                <a class="subscribe" href="http://www.feedly.com/home#subscription/feed/{$feedUrl}">
                    <img src="http://s3.feedly.com/img/follows/feedly-follow-rectangle-volume-small_2x.png" width="61" height="20" alt="Feedly"/>
                </a>
                <a class="subscribe" href="http://www.netvibes.com/subscribe.php?url={$feedUrl}">
                    <img src="http://www.netvibes.com/img/add2netvibes.gif" alt="Netvibes"/>
                </a>
            </div>
            <xsl:choose>
                <xsl:when test="$newsletterId!='none'">
                    <div id="newsletterlink">
                        <h4>
                            <a onclick="window.open('https://feed.press/e/mailverify?feed_id={$newsletterId}', 'popupwindow', 'scrollbars=yes,width=550,height=520');return true" target="popupwindow" href="https://feed.press/e/mailverify?feed_id={$newsletterId}">
                                <xsl:value-of select="$lang_SubscribeEmail"/>
                            </a>
                        </h4>
                    </div>
                </xsl:when>
                <xsl:otherwise></xsl:otherwise>
            </xsl:choose>
        </div>
    </xsl:variable>
    <xsl:variable name="html_TemplateHeader">
        <head>
            <title>
                <xsl:value-of select="$title"/>
                <xsl:if test="$freeVersion='true'">
                    —
                    <xsl:value-of select="$lang_ProvidedBy"/>
                </xsl:if>
            </title>
            <link href="{$cssFile}" rel="stylesheet" type="text/css" media="all"/>
            <xsl:choose>
                <xsl:when test="$language='ar'">
                    <style>body {direction: rtl;}</style>
                </xsl:when>
                <xsl:otherwise/>
            </xsl:choose>
            <link rel="alternate" type="application/rss+xml" title="{$title}" href="{$feedUrl}"/>
            <meta name="viewport" content="width=device-width"/>
            <xsl:choose>
                <xsl:when test="$podcastId">
                    <meta name="apple-itunes-app" content="app-id={$podcastId}"/>
                </xsl:when>
                <xsl:otherwise/>
            </xsl:choose>

        </head>
    </xsl:variable>
    <xsl:variable name="html_FeedHeader">
        <h1>
            <xsl:choose>
                <xsl:when test="$siteUrl">
                    <a href="{normalize-space($siteUrl)}" title="{$title}">
                        <xsl:value-of select="$title"/>
                    </a>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="$title"/>
                </xsl:otherwise>
            </xsl:choose>
        </h1>
        <xsl:choose>
            <xsl:when test="$freeVersion='true'">
                <xsl:choose>
                    <xsl:when test="$locale='fr'">
                        <h2>proposé avec joie par FeedPress</h2>
                        <p class="about">
                            FeedPress donne de la puissance à ce flux pour que vous puissiez retrouver vos contenus préférés dans votre lecteur RSS.
                            <br/>
                            <a href="https://feed.press">Cliquez ici pour ajouter votre flux →</a>
                        </p>
                    </xsl:when>
                    <xsl:otherwise>
                        <h2>proudly brought to you by FeedPress</h2>
                        <p class="about">
                            FeedPress empowers this feed to let you add your favorite content easily in your reader of choice.
                            <br/>
                            <a href="https://feed.press">Click here to press your own feed →</a>
                        </p>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:otherwise>
                <xsl:if test="$subTitle">
                    <h2>
                        <xsl:value-of select="$subTitle"/>
                    </h2>
                </xsl:if>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:variable name="html_FeedFooter">
        <div id="footer">
            <a href="https://plugin.squirrly.co">
                <img src="//www.squirrly.co/wp-content/uploads/squirrly_logo.png" height="30" />
            </a>
            <p>
                Feed generated with Squirrly SEO
            </p>
        </div>
    </xsl:variable>
    <xsl:variable name="html_FeedFooterScripts">
        <xsl:element name="script">
            <xsl:attribute name="type">text/javascript</xsl:attribute>
            <xsl:attribute name="src">
                //cdnjs.cloudflare.com/ajax/libs/jstimezonedetect/1.0.4/jstz.min.js
            </xsl:attribute>
        </xsl:element>
        <xsl:element name="script">
            <xsl:attribute name="type">text/javascript</xsl:attribute>
            <xsl:attribute name="src">
                //cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.3/moment.min.js
            </xsl:attribute>
        </xsl:element>
        <xsl:element name="script">
            <xsl:attribute name="type">text/javascript</xsl:attribute>
            <xsl:attribute name="src">//static.feedpress.it/js/moment-timezone-data.js</xsl:attribute>
        </xsl:element>
        <xsl:element name="script">
            <xsl:attribute name="type">text/javascript</xsl:attribute>
            <xsl:attribute name="src">//static.feedpress.it/js/plyr.js</xsl:attribute>
        </xsl:element>
        <xsl:element name="script">
            <xsl:attribute name="type">text/javascript</xsl:attribute>
            <xsl:attribute name="src">//static.feedpress.it/js/feed.js</xsl:attribute>
        </xsl:element>
        <script>
<![CDATA[
(function(d,p){ var a=new XMLHttpRequest(), b=d.body; a.open("GET",p,!0); a.send(); a.onload=function(){ var c=d.createElement("div"); c.style.display="none"; c.innerHTML=a.responseText; b.insertBefore(c,b.childNodes[0]) } })(document,"//static.feedpress.it/svg/plyr.svg");
]]>
        </script>
        <script>
<![CDATA[ plyr.setup(); ]]>
        </script>
    </xsl:variable>
</xsl:stylesheet>