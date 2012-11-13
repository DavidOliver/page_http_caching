<?php

require_once(TOOLKIT . '/class.mysql.php');

class Extension_HTTP_Caching extends Extension{

	const TBL_NAME = 'tbl_http_caching';

	public function install(){
		Symphony::Database()->query(
			'CREATE TABLE `' . self::TBL_NAME . '` (
				`page_id` INT(11) unsigned NOT NULL,
				`caching` VARCHAR(7) NOT NULL,
				`intermediary` VARCHAR(7) NOT NULL,
				`max_age` INT unsigned NOT NULL,
				PRIMARY KEY (`page_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
		);

		Symphony::Configuration()->setArray(array(
			'http_caching' => array(
				'default_caching'       => 'off',
				'default_intermediary'  => 'no',
				'default_max_age'       => '60'
			)
		));
		Symphony::Configuration()->write();
	}

	public function uninstall(){
		Symphony::Configuration()->remove('http_caching');
		Symphony::Configuration()->write();
		Symphony::Database()->query('DROP TABLE `' . self::TBL_NAME . '`');
	}
	
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
				'page'      => '/blueprints/pages/',
				'delegate'  => 'PagePostCreate',
				'callback'  => 'savePageSettings'
			),
			array(
				'page'      => '/blueprints/pages/',
				'delegate'  => 'PagePostEdit',
				'callback'  => 'savePageSettings'
			),
			array(
				'page'      => '/blueprints/pages/',
				'delegate'  => 'PagePostDelete',
				'callback'  => 'deletePageSettings'
			),
			array(
				'page'      => '/frontend/',
				'delegate'  => 'FrontendPreRenderHeaders',
				'callback'  => 'updateHeaders'
			)
		);
	}

	public function savePageSettings($context){
		$settings_in = $_POST['http_caching'];
		$settings_out = array(
			'page_id'       => $context['page_id'],
			'caching'       => $settings_in['caching'],
			'intermediary'  => $settings_in['intermediary'],
			'max_age'       => $settings_in['max_age']
		);
		Symphony::Database()->insert($settings_out, self::TBL_NAME, true);
	}

	public function deletePageSettings($context){
		foreach ($context['page_ids'] as $page_id) {
			Symphony::Database()->delete(self::TBL_NAME, sprintf("`page_id` = %d", $page_id));
		}
	}

	private function getPageSettings($page_id){

		/*
			$result['caching'])
			$result['intermediary'])
			$result['max_age'])
		*/

		/*if (!is_int($page_id)) {
			trigger_error('getPageSettings expected Argument 1 to be Integer', E_USER_WARNING);
		}*/

		$result = Symphony::Database()->fetch(
			sprintf(
				'SELECT * FROM ' . self::TBL_NAME . ' WHERE `page_id` = %d',
				$page_id
			)
		);
		return $result[0];
	}

	public function appendPreferences($context){
		$group = new XMLElement('fieldset');
		$group->setAttribute('class', 'settings');

		$group->appendChild(new XMLElement('legend', __('HTTP Caching')));

		//$group->appendChild(new XMLElement('p', __('A paragraph for short intructions.'), array('class' => 'help')));

		// Default behaviour
		$fieldset = new XMLElement('fieldset');
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
		$fieldset = new XMLElement('fieldset');
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
		$label = Widget::Label(__('Default: max-age (seconds)'), $input, 'seconds');
		$group->appendChild($label);

		$context['wrapper']->appendChild($group);
	}

	public function appendPageSettings($context){
		$page_id = $context['fields']['id'];
		$page_settings = $this->getPageSettings($page_id);

		$attr_checked = array('checked' => 'checked');
		${checked_caching_.$page_settings['caching']} = $attr_checked;
		${checked_intermediary_.$page_settings['intermediary']} = $attr_checked;

		$group = new XMLElement('fieldset', null, array('class' => 'settings'));
		
		$group->appendChild(new XMLElement('legend', __('Page HTTP Caching')));

		// HTTP caching
		$fieldset = new XMLElement('fieldset');
		$fieldset->appendChild(new XMLElement('legend', __('HTTP caching')));

		$input = Widget::Input('http_caching[caching]', 'default', 'radio', $checked_caching_default);
		$label = Widget::Label(null, $input, null, null, array('title'=>'Use default setting in Preferences'));
		$label->setValue(__('Default'), false);
		$fieldset->appendChild($label);

		$input = Widget::Input('http_caching[caching]', 'off', 'radio', $checked_caching_off);
		$label = Widget::Label(null, $input, null, null, array('title'=>'Normal Symphony CMS behaviour'
		));
		$label->setValue(__('Off'), false);
		$fieldset->appendChild($label);

		$input = Widget::Input('http_caching[caching]', 'on', 'radio', $checked_caching_on);
		$label = Widget::Label(null, $input);
		$label->setValue(__('On'), false);
		$fieldset->appendChild($label);

		$group->appendChild($fieldset);

		// Intermediary
		$fieldset = new XMLElement('fieldset');
		$fieldset->appendChild(new XMLElement('legend', __('Intermediary caches such as web proxies allowed')));

		$input = Widget::Input('http_caching[intermediary]', 'default', 'radio', $checked_intermediary_default);
		$label = Widget::Label(null, $input);
		$label->setValue(__('Default'), false);
		$fieldset->appendChild($label);

		$input = Widget::Input('http_caching[intermediary]', 'no', 'radio', $checked_intermediary_no);
		$label = Widget::Label(null, $input);
		$label->setValue(__('No'), false);
		$fieldset->appendChild($label);

		$input = Widget::Input('http_caching[intermediary]', 'yes', 'radio', $checked_intermediary_yes);
		$label = Widget::Label(null, $input);
		$label->setValue(__('Yes'), false);
		$fieldset->appendChild($label);

		$group->appendChild($fieldset);

		// max-age
		$input = Widget::Input('http_caching[max_age]', $page_settings['max_age']);
		$label = Widget::Label(__('max-age (seconds; if empty, default setting in Preferences will be used)'), $input, 'seconds');
		$group->appendChild($label);

		$context['form']->appendChild($group);
	}

	public function updateHeaders(){
		// Remember that a page may not have a settings row in our table
		// Also allow for Symphony config file not having our settings in it

		$config = Symphony::Configuration()->get('http_caching');

		if ($config['default_caching'] == 'on') {

			$type = ($config['default_intermediary'] == 'yes') ? 'public' : 'private';

			if ($config['default_max_age'] != '' && ctype_digit($config['default_max_age'])) {
				$max_age = $config['default_max_age'];
			} else {
				return false;
			}

			// Remove unnecessary/unwanted headers
			Frontend::Page()->removeHeaderFromPage('Expires');
			Frontend::Page()->removeHeaderFromPage('Last-Modified');
			Frontend::Page()->removeHeaderFromPage('Pragma');
			/*
			Frontend::Page()->addHeaderToPage('Expires', '');
			Frontend::Page()->addHeaderToPage('Last-Modified', '');
			Frontend::Page()->addHeaderToPage('Pragma', '');
			*/

			Frontend::Page()->addHeaderToPage('Cache-Control', $type . ', max-age=' . $max_age);
		}

	}
	
}

?>