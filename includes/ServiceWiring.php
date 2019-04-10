<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Minerva\SkinUserPageHelper;

return [
	'Minerva.ContentHandler' => function ( MediaWikiServices $services ) {
		return ContentHandler::getForTitle( RequestContext::getMain()->getTitle() );
	},
	'Minerva.SkinUserPageHelper' => function ( MediaWikiServices $services ) {
		return new SkinUserPageHelper( RequestContext::getMain()->getTitle() );
	}
];
