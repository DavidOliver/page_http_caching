<?php

class Extension_HTTP_Cache_Headers extends Extension {
	
	public function getSubscribedDelegates() {
		return array(
			array(
				'page'		=> '/frontend/',
				'delegate'	=> 'FrontendPreRenderHeaders',
				'callback'	=> 'addHeaders'
			)
		);
	}
	
	public function addHeaders() {
		
		// Remove unnecessary/unwanted headers
		Frontend::Page()->removeHeaderFromPage('Expires');
		Frontend::Page()->removeHeaderFromPage('Last-Modified');
		Frontend::Page()->removeHeaderFromPage('Pragma');
		/*
		Frontend::Page()->addHeaderToPage('Expires', '');
		Frontend::Page()->addHeaderToPage('Last-Modified', '');
		Frontend::Page()->addHeaderToPage('Pragma', '');
		*/

		Frontend::Page()->addHeaderToPage('Cache-Control', 'public, max-age=60');
	}
	
}

?>