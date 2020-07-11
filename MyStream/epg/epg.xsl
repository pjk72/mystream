<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  
<!--	<xsl:strip-space elements="*" /> -->
	<xsl:output method="xml" indent="yes" omit-xml-declaration="yes" encoding="UTF-8"/>

	<xsl:template match="programme">
	<xsl:variable name="descrizione" select="desc" />
	<xsl:variable name="prima" select="substring-before($descrizione,'&#xA;')" />
	<xsl:variable name="dopo-full" select="substring-after($descrizione,'&#xA;')" />
	<xsl:variable name="dopo-mdi" select="substring-before($dopo-full,' - Entra nel gruppo Telegram @epgguide')" />	
	<xsl:variable name="dopo" select="substring-after($dopo-mdi,'&#xA;')" />
  <xsl:value-of select="@start"/>|<xsl:value-of select="@stop"/>|<xsl:value-of select="@channel"/>|<xsl:value-of select="title"/>|<xsl:value-of select="$prima"/>|<xsl:value-of select="$dopo"/>|      
	<xsl:text>&#xa;</xsl:text>
  </xsl:template>

<xsl:template match="tv">
  <xsl:apply-templates select="programme"/>
</xsl:template>
</xsl:stylesheet>