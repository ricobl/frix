<script type="text/javascript">
AC_FL_RunContent(
	'width', '<?= $width ?>',
	'height', '<?= $height ?>',
	
	'movie','<?= $src_no_ext ?>',
	'src','<?= $src_no_ext ?>',
	
	'codebase','http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0',
	'pluginspage','http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash',
	'quality','high'
); //end AC code
</script>
<noscript>
	<object width="<?= $width ?>" height="<?= $height ?>"
		codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0"
		classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">
		
		<param name="quality" value="high" />
		<param name="movie" value="<?= $src ?>" />
		
		<embed pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash"
			type="application/x-shockwave-flash" quality="high"
			src="<?= $src ?>" width="<?= $width ?>" height="<?= $height ?>">
		</embed>
	</object>
</noscript>
