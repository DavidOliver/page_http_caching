<?php

class Extension_HTTP_Caching extends Extension{
	
	public function getSubscribedDelegates(){
		return array(
			array(
				'page'      => '/system/preferences/',
				'delegate'  => 'AddCustomPreferenceFieldsets',
				'callback'  => 'appendPreferences'
			),
			array(
				'page'      => '/blueprints/pages/',
				'delegate'  => 'AppendPageContent',
				'callback'  => 'appendPageSettings'
			),
			array(
				'page'      => '/frontend/',
				'delegate'  => 'FrontendPreRenderHeaders',
				'callback'  => 'addHeaders'
			)
		);
	}

	public function appendPreferences($context){
		$group = new XMLElement('fieldset');
		$group->setAttribute('class', 'settings');

		$group->appendChild(new XMLElement('legend', __('HTTP Caching')));

		//$group->appendChild(new XMLElement('p', __('A paragraph for short intructions.'), array('class' => 'help')));

		// Default behaviour
		$fieldset = (new XMLElement('fieldset'));
		$fieldset->appendChild(new XMLElement('legend', __('Default: frontend page HTTP caching')));

		$input = Widget::Input('settings[http_caching][default_caching]', 'off', 'radio');
		$label = Widget::Label(null, $input, null, null, array('title'=>'Normal Symphony CMS behaviour'));
		$label->setValue(__('Off'), false);
		$fieldset->appendChild($label);

		$input = Widget::Input('settings[http_caching][default_caching]', 'on', 'radio');
		$label = Widget::Label(null, $input);
		$label->setValue(__('On'), false);
		$fieldset->appendChild($label);

		$group->appendChild($fieldset);

		// Default intermediary
		$fieldset = (new XMLElement('fieldset'));
		$fieldset->appendChild(new XMLElement('legend', __('Default: intermediary caches such as web proxies allowed')));

		$input = Widget::Input('settings[http_caching][default_intermediary]', 'no', 'radio');
		$label = Widget::Label(null, $input);
		$label->setValue(__('No'), false);
		$fieldset->appendChild($label);

		$input = Widget::Input('settings[http_caching][default_intermediary]', 'yes', 'radio');
		$label = Widget::Label(null, $input);
		$label->setValue(__('Yes'), false);
		$fieldset->appendChild($label);

		$group->appendChild($fieldset);

		// Default max-age
		$input = Widget::Input('settings[http_caching][default_max_age]');
		$label = Widget::Label('Default: max-age (seconds)', $input, 'seconds');
		$group->appendChild($label);

		$context['wrapper']->appendChild($group);
	}

	public function appendPageSettings($context){
		$fieldset = new XMLElement('fieldset', null, array('class' => 'settings'));
		
		$fieldset->appendChild(new XMLElement('legend', __('HTTP Caching')));

		//$fieldset->appendChild(new XMLElement('p', __('A paragraph for short intructions.'), array('class' => 'help')));

		$context['form']->appendChild($fieldset);
	}

	public function addHeaders(){
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