<?php
include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once(dirname(__FILE__).'/twenga.php');

@ini_set('memory_limit', '300M');
if (!ini_get('safe_mode'))
	@set_time_limit(300);
include(dirname(__FILE__).'/service/buildcatalog.php');
$oBuildCatalog = new BuildCatalog();

$sFilename = 'twenga_last_modified.txt';
$bCleIsDiff = true;
$dDateHeader = gmdate( 'D, d M Y H:i:s' );
$sVersionGenerated = $oBuildCatalog->getVersionGenerated();

if(file_exists($sFilename)){
	$sHandle = fopen($sFilename, 'r');
	$sContentFile = fgets($sHandle);
	$aDataFile = explode('_', $sContentFile);
	if(is_array($aDataFile) && count($aDataFile) > 1){
		if($aDataFile[1] == $sVersionGenerated){
			$bCleIsDiff = false;
			$dDateHeader = $aDataFile[0];
		}
	}
	fclose($sHandle);
}

if($bCleIsDiff){
	$sHandle = fopen($sFilename, 'w+');
	fwrite($sHandle, $dDateHeader.'_'.$sVersionGenerated);
	fclose($sHandle);
}

header( 'Last-Modified: ' . $dDateHeader . ' GMT' );	
header("Content-type: text/xml; charset=utf-8");
$oBuildCatalog->_buildXML();