<?php

/**
 *
 * Plugin for handling some advanced review features.
 * Written by Andy Byers, Ubiquity Press
 * Funded by INASP
 *
 */

import('lib.pkp.classes.plugins.GenericPlugin');
require_once('ARDAO.inc.php');

class ARPlugin extends GenericPlugin {
	function register($category, $path) {
		if(!parent::register($category, $path)) {
			return false;
		}
		if($this->getEnabled()) {
			HookRegistry::register("LoadHandler", array(&$this, "handleRequest"));
			$tm =& TemplateManager::getManager();
			$tm->assign("AREnabled", true);
			define('AR_PLUGIN_NAME', $this->getName());
		}
		return true;
	}

	function handleRequest($hookName, $args) {
		$page =& $args[0];
		$op =& $args[1];
		$sourceFile =& $args[2];

		if ($page == 'advancedreview') {
			$this->import('ARHandler');
			Registry::set('plugin', $this);
			define('HANDLER_CLASS', 'ARHandler');
			return true;
		}
		return false;
	}

	function getDisplayName() {
		return "Advanced Review Features";
	}
	
	function getDescription() {
		return "Enables some advanced review features.";
	}
	
	function getTemplatePath() {
		return parent::getTemplatePath() . 'templates/';
	}

}
