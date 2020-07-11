<?xml version="1.0" encoding="UTF-8"?>
<!--
2008-03-02
	Correction de la conversion des dates.
	Ajout de ce journal des changements.
2008-02-11
	Première version publique.
-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="xml" indent="yes" encoding="UTF-8" />
  <xsl:strip-space elements="*"/>

  <xsl:template match="/html">
    <xsl:comment> http://o.mengue.free.fr/blog/2008/02/11/51-les-programmes-tele-de-telerama-en-xmltv </xsl:comment>
    <tv generator-info-name="Télérama → XMLTV"
        source-info-url="http://television.telerama.fr/tele/grille.php"
        source-info-name="Télérama">
      <xsl:apply-templates mode="channel" select="body/div/div[@class='chaine']" />
      <xsl:apply-templates mode="programme" select="body/div/div/div/div[contains(@class, 'emission')]/div/div[substring(@id, 0, 6)='data_']" />
    </tv>
  </xsl:template>
  <xsl:template match="text()" priority="-1"/>

  <xsl:template mode="channel" match="div">
    <channel>
      <xsl:attribute name="id">
        <xsl:value-of select="substring(../@id, 6)"/>
      </xsl:attribute>
      <display-name lang="fr"><xsl:value-of select="./@title"/></display-name>
      <icon width="40" height="40">
        <xsl:attribute name="src">http://icon-telerama.sdv.fr/tele/imedia/images_chaines_tra/Transparent/40x40/<xsl:value-of select="substring(../@id, 6)"/>.gif</xsl:attribute>
      </icon>
    </channel>
  </xsl:template>

  <xsl:template mode="programme" match="div">
    <programme>
      <xsl:attribute name="channel">
        <xsl:value-of select="div[@class='Id_Chaine']"/>
      </xsl:attribute>
      <xsl:attribute name="start">
        <xsl:apply-templates mode="xmltv-time-from-iso-8601" select="div[@class='Date_Debut']"/>
      </xsl:attribute>
      <xsl:attribute name="stop">
        <xsl:apply-templates mode="xmltv-time-from-iso-8601" select="div[@class='Date_Fin']"/>
      </xsl:attribute>
      <xsl:attribute name="showview">
        <xsl:value-of select="div[@class='ShowView']"/>
      </xsl:attribute>
      <title lang="fr">
        <xsl:value-of select="div[@class='Titre']"/>
      </title>
      <sub-title lang="fr">
        <xsl:value-of select="div[@class='Sous_Titre']"/>
      </sub-title>
      <desc lang="fr">
        <xsl:value-of select="div[@class='resume_long']"/>
      </desc>
      <category lang="fr">
        <xsl:call-template name="categories">
          <xsl:with-param name="category" select="div[@class='Type']"/>
        </xsl:call-template>
      </category>
      <length units="seconds">
        <xsl:value-of select="div[@class='DureeEnSecondes']"/>
    </length>
    <url>http://television.telerama.fr<xsl:value-of select="../span[@class='titre']/a/@href" /></url>
        <xsl:call-template name="rating">
          <xsl:with-param name="noteToTransform" select="div[@class='note_T']"/>
        </xsl:call-template>
    </programme>
  </xsl:template>

  <xsl:template mode="xmltv-time-from-iso-8601" match="*|@*">
    <xsl:value-of
        select="concat(substring(.,1,4),substring(.,6,2),substring(.,9,2),substring(.,12,2),substring(.,15,2),'00 +0100')"/>
  </xsl:template>

  <xsl:template name="rating">
    <xsl:param name="noteToTransform"/>
    <xsl:choose>
      <xsl:when test="contains($noteToTransform, '1')">
        <star-rating>2/5</star-rating>
      </xsl:when>
      <xsl:when test="contains($noteToTransform, '2')">
        <star-rating>3/5</star-rating>
      </xsl:when>
      <xsl:when test="contains($noteToTransform, '3')">
        <star-rating>4/5</star-rating>
      </xsl:when>
      <xsl:when test="contains($noteToTransform, '5')">
        <star-rating>1/5</star-rating>
      </xsl:when>
      <xsl:when test="contains($noteToTransform, '6')">
        <star-rating>0/5</star-rating>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="categories">
    <xsl:param name="category"/>
    <xsl:choose>
      <xsl:when test="contains($category, 'Dessin animé')">
        <xsl:text>Children</xsl:text>
      </xsl:when>
      <xsl:otherwise>
          <xsl:value-of select="$category"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>
