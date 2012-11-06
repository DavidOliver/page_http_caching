<?php

class Extension_Web_Browser_Caching extends Extension {
	
	public function getSubscribedDelegates() {
		return array(
			array(
				'page'		=> '/system/preferences/',
				'delegate'	=> 'AddCustomPreferenceFieldsets',
				'callback'	=> 'appendPreferences'
			),
			array(
				'page' => '/blueprints/pages/',
				'delegate' => 'AppendPageContent',
				'callback' => 'appendPageSettings'
			),
			array(
				'page'		=> '/frontend/',
				'delegate'	=> 'FrontendPreRenderHeaders',
				'callback'	=> 'addHeaders'
			),
		);
	}

	public function appendPreferences($context) {
		$group = new XMLElement('fieldset');
		$group->setAttribute('class', 'settings');

		$group->appendChild(new XMLElement('legend', __('Web Browser Caching')));

		$group->appendChild(new XMLElement('p', __('A paragraph for short intructions.'), array('class' => 'help')));

		$label = Widget::Label(__('Allow HTTP caching for all pages by default'));
		$group->appendChild($label);

		$context['wrapper']->appendChild($group);
	}

	public function appendPageSettings($context){
		$fieldset = new XMLElement('fieldset', null, array('class' => 'settings'));
		
		$fieldset->appendChild(new XMLElement('legend', __('Web Browser Caching')));

		$fieldset->appendChild(new XMLElement('p', __('A paragraph for short intructions.'), array('class' => 'help')));

		$context['form']->appendChild($fieldset);
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