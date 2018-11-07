<?php

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'SemanticOrganization' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['SemanticOrganization'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['SemanticOrganizationAlias'] = __DIR__ . '/SemanticOrganization.i18n.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for Otelo extension. Please use wfLoadExtension ' .
		'instead, see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return true;
} else {
	die( 'This version of the Otelo extension requires MediaWiki 1.25+' );
}
