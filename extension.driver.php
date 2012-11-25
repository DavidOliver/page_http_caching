<?php

class Extension_Page_HTTP_Caching extends Extension {

	const FULL_NAME  = 'Page HTTP Caching';
	const NAME       = 'page_http_caching';
	const TBL_NAME   = 'tbl_page_http_caching';

	public function install() {
		Symphony::Database()->query(
			'CREATE TABLE `' . self::TBL_NAME . '` (
				`page_id` INT(11) unsigned NOT NULL,
				`caching` VARCHAR(7) NOT NULL,
				`intermediary` VARCHAR(7) NOT NULL,
				`max_age` INT unsigned default NULL,
				PRIMARY KEY (`page_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
		);

		Symphony::Configuration()->setArray(array(
			self::NAME => array(
				'default_caching'       => 'off',
				'default_intermediary'  => 'no',
				'default_max_age'       => '60'
			)
		));
		Symphony::Configuration()->write();
	}

	public function uninstall() {
		Symphony::Configuration()->remove(self::NAME);
		Symphony::Configuration()->write();
		Symphony::Database()->query('DROP TABLE `' . self::TBL_NAME . '`');
	}
	
	public function getSubscribedDelegates() {
		return array(
			array(
				'page'      => '/system/preferences/',
				'delegate'  => 'AddCustomPreferenceFieldsets',
				'callback'  => 'appendPreferences'
			),
			/*array(
				'page'      => '/system/preferences/',
				'delegate'  => 'Save',
				'callback'  => 'validatePreferences'
			),*/
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
				'page'      => '/backend/',
				'delegate'  => 'InitaliseAdminPageHead',
				'callback'  => 'addCSS'
			),
			array(
				'page'      => '/frontend/',
				'delegate'  => 'FrontendPreRenderHeaders',
				'callback'  => 'updateHeaders'
			)
		);
	}

	public function appendPreferences($context) {
		$config = Symphony::Configuration()->get(self::NAME);

		// setting checked arrays in order to preselect radio buttons as appropriate
		$attr_checked = array('checked' => 'checked');
		${checked_caching_.$config['default_caching']} = $attr_checked;
		${checked_intermediary_.$config['default_intermediary']} = $attr_checked;

		// form elements
		$group = new XMLElement('fieldset', null, array('class' => 'settings ' . self::NAME));

		$group->appendChild(new XMLElement('legend', __(self::FULL_NAME)));

		//$group->appendChild(new XMLElement('p', __('A paragraph for short intructions.'), array('class' => 'help')));

		// default HTTP cache header
		$fieldset = new XMLElement('fieldset', null, array('class' => 'inline-options'));
		$fieldset->appendChild(new XMLElement('legend', __('Default: HTTP caching')));

		$input = Widget::Input('settings['.self::NAME.'][default_caching]', 'off', 'radio', $checked_caching_off);
		$label = Widget::Label(null, $input);
		$label->setValue(__('Off'), false);
		$fieldset->appendChild($label);

		$input = Widget::Input('settings['.self::NAME.'][default_caching]', 'on', 'radio', $checked_caching_on);
		$label = Widget::Label(null, $input);
		$label->setValue(__('On'), false);
		$fieldset->appendChild($label);

		$group->appendChild($fieldset);

		// default intermediary
		$fieldset = new XMLElement('fieldset', null, array('class' => 'inline-options'));
		$fieldset->appendChild(new XMLElement('legend', __('Default: allow intermediary caches (e.g., web proxies)')));

		$input = Widget::Input('settings['.self::NAME.'][default_intermediary]', 'no', 'radio', $checked_intermediary_no);
		$label = Widget::Label(null, $input);
		$label->setValue(__('No'), false);
		$fieldset->appendChild($label);

		$input = Widget::Input('settings['.self::NAME.'][default_intermediary]', 'yes', 'radio', $checked_intermediary_yes);
		$label = Widget::Label(null, $input);
		$label->setValue(__('Yes'), false);
		$fieldset->appendChild($label);

		$group->appendChild($fieldset);

		// Default max-age
		$input = Widget::Input(
			'settings['.self::NAME.'][default_max_age]',
			$config['default_max_age'],
			'text',
			array(
				'id' => 'page-http-caching-max-age'
			)
		);
		$label = Widget::Label(__('Default: max-age (seconds)'), $input, 'seconds');
		$group->appendChild($label);

		$context['wrapper']->appendChild($group);
	}

	public function appendPageSettings($context) {
		$page_id = $context['fields']['id'];
		$page_settings = $this->getPageSettings($page_id);

		// setting checked arrays in order to preselect radio buttons as appropriate
		$attr_checked = array('checked' => 'checked');

		if (empty($page_settings['caching'])) {
			$checked_caching_default = $attr_checked;
		} else {
			${checked_caching_.$page_settings['caching']} = $attr_checked;
		}

		if (empty($page_settings['intermediary'])) {
			$checked_intermediary_default = $attr_checked;
		} else {
			${checked_intermediary_.$page_settings['intermediary']} = $attr_checked;
		}

		// form elements
		$group = new XMLElement('fieldset', null, array('class' => 'settings ' . self::NAME));

		$group->appendChild(new XMLElement('legend', __(self::FULL_NAME)));

		// HTTP cache header
		$fieldset = new XMLElement('fieldset', null, array('class' => 'inline-options'));
		$fieldset->appendChild(new XMLElement('legend', __('HTTP Caching')));

		$input = Widget::Input(''.self::NAME.'[caching]', 'default', 'radio', $checked_caching_default);
		$label = Widget::Label(null, $input);
		$label->setValue(__('Default'), false);
		$fieldset->appendChild($label);

		$input = Widget::Input(''.self::NAME.'[caching]', 'off', 'radio', $checked_caching_off);
		$label = Widget::Label(null, $input);
		$label->setValue(__('Off'), false);
		$fieldset->appendChild($label);

		$input = Widget::Input(''.self::NAME.'[caching]', 'on', 'radio', $checked_caching_on);
		$label = Widget::Label(null, $input);
		$label->setValue(__('On'), false);
		$fieldset->appendChild($label);

		$group->appendChild($fieldset);

		// intermediary
		$fieldset = new XMLElement('fieldset', null, array('class' => 'inline-options'));
		$fieldset->appendChild(new XMLElement('legend', __('Allow intermediary caches (e.g., web proxies)')));

		$input = Widget::Input(''.self::NAME.'[intermediary]', 'default', 'radio', $checked_intermediary_default);
		$label = Widget::Label(null, $input);
		$label->setValue(__('Default'), false);
		$fieldset->appendChild($label);

		$input = Widget::Input(''.self::NAME.'[intermediary]', 'no', 'radio', $checked_intermediary_no);
		$label = Widget::Label(null, $input);
		$label->setValue(__('No'), false);
		$fieldset->appendChild($label);

		$input = Widget::Input(''.self::NAME.'[intermediary]', 'yes', 'radio', $checked_intermediary_yes);
		$label = Widget::Label(null, $input);
		$label->setValue(__('Yes'), false);
		$fieldset->appendChild($label);

		$group->appendChild($fieldset);

		// max-age
		$input = Widget::Input(
			self::NAME.'[max_age]',
			$page_settings['max_age'],
			'text',
			array(
				'id' => 'page-http-caching-max-age',
				'placeholder' => 'Leave empty to use default'
			)
		);
		$label = Widget::Label(__('max-age (seconds)'), $input);
		$group->appendChild($label);

		$context['form']->appendChild($group);
	}

	private function validatePreferences($context) {
		// @TODO: validate preferences function
		/*
		$preferences = $context['settings'][self::NAME];

		if ( // does this do what's intended?
			!is_null($preferences['default_caching']) &&
			!is_null($preferences['intermediary']) &&
			!is_null($preferences['max_age'])
		){
			return false; // return specific error message?
		}

		if (
			($preferences['default_caching'] != 'off') &&
			($preferences['default_caching'] != 'on')
		){
			return false; // return specific error message?
		}

		if (
			($preferences['default_intermediary'] != 'no') &&
			($preferences['default_intermediary'] != 'yes')
		){
			return false; // return specific error message?
		}

		if (!ctype_digit($preferences['default_max_age'])){
			return false; // return specific error message?
		}

		return true;
		*/
	}

	public function savePageSettings($context) {
		$settings_in = $_POST[self::NAME];
		$settings_out = array(
			'page_id'       => $context['page_id'],
			'caching'       => $settings_in['caching'],
			'intermediary'  => $settings_in['intermediary'],
			'max_age'       => $settings_in['max_age']
		);
		Symphony::Database()->insert($settings_out, self::TBL_NAME, true);
	}

	public function deletePageSettings($context) {
		foreach ($context['page_ids'] as $page_id) {
			Symphony::Database()->delete(self::TBL_NAME, sprintf("`page_id` = %d", $page_id));
		}
	}

	private function getPageSettings($page_id) {
		$result = Symphony::Database()->fetch(
			sprintf(
				'SELECT * FROM ' . self::TBL_NAME . ' WHERE `page_id` = %d',
				$page_id
			)
		);
		return $result[0];
	}

	public function addCSS($context) {
		Administration::instance()->Page->addStylesheetToHead(URL.'/extensions/'.self::NAME.'/assets/style.css');
	}

	public function updateHeaders() {

		if (Frontend::instance()->isLoggedIn()) {

			$page_params = Frontend::instance()->Page()->Params();

			// search for page settings
			//$page_data = Frontend::Page()->pageData();
			//$page_settings = $this->getPageSettings($page_data['id']);
			$page_settings = $this->getPageSettings($page_params['current-page-id']);

			$config = Symphony::Configuration()->get(self::NAME);

			// @TODO: validate preferences

			// if page HTTP caching is not desired
			if (($page_settings['caching'] != 'on' && $config['default_caching'] != 'on') ||
				($page_settings['caching'] == 'off')) {
				return false;
			}

			// page HTTP caching is desired
			$page_http_caching = array();

			if (!empty($page_settings['max_age'])) {
				$page_http_caching['max_age'] = $page_settings['max_age'];
			} elseif (!empty($config['default_max_age'])) {
				$page_http_caching['max_age'] = $config['default_max_age'];
			} else {
				return false;
			}

			if ($page_settings['intermediary'] == 'no') {
				$page_http_caching['intermediary'] = 'private';
			} elseif ($page_settings['intermediary'] == 'yes') {
				$page_http_caching['intermediary'] = 'public';
			} elseif ($config['default_intermediary'] == 'yes') {
				$page_http_caching['intermediary'] = 'public';
			} else {
				$page_http_caching['intermediary'] = 'private';
			}

			// remove unwanted/unnecessary headers
			if (version_compare($page_params['symphony-version'], '2.3.2', '<')) {
				// Symphony CMS 2.3 - 2.3.1: set the unwanted header values to be blank
				Frontend::Page()->addHeaderToPage('Expires', '');
				Frontend::Page()->addHeaderToPage('Last-Modified', '');
				Frontend::Page()->addHeaderToPage('Pragma', '');
			} else {
				// Symphony CMS 2.3.2+: completely remove headers with new removeHeaderFromPage method
				Frontend::Page()->removeHeaderFromPage('Expires');
				Frontend::Page()->removeHeaderFromPage('Last-Modified');
				Frontend::Page()->removeHeaderFromPage('Pragma');
			}

			// add HTTP cache header
			Frontend::Page()->addHeaderToPage(
				'Cache-Control',
				$page_http_caching['intermediary'] . ', max-age=' . $page_http_caching['max_age']
			);

		}

	}

}
