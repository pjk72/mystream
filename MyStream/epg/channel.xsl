<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  
<!--	<xsl:strip-space elements="*" /> -->
	<xsl:output method="xml" indent="yes" omit-xml-declaration="yes" encoding="UTF-8"/>

<xsl:template match="channel">
<xsl:value-of select="@id"/>|<xsl:value-of select="display-name"/>|
<xsl:text>&#xa;</xsl:text>
</xsl:template>

<xsl:template match="tv">
  <xsl:apply-templates select="channel"/>
</xsl:template>
</xsl:stylesheet>