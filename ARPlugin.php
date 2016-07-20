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
			HookRegistry::register('ReviewerAction::recordRecommendation', array(&$this, 'email_thanks_reviewer'));
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

	function email_thanks_reviewer($hookName, $args) {
		var_dump($args);
		exit();
		import('lib.pkp.classes.mail.Mail');
		import('classes.article.log.ArticleLog');

		$email = new Mail();
		$email->setFrom(strtolower($request->getJournal()->getPath()) . '@ubiquity.press');
		$email->setReplyTo($request->getUser()->getEmail());
		$email->setSubject($subject);
		$email->setBody($text);
		$email->addRecipient($user->getEmail());
		$email->send();

		$articleEmailLogDao =& DAORegistry::getDAO('ArticleEmailLogDAO');
		$entry = $articleEmailLogDao->newDataObject();

		$entry->setEventType('ARTICLE_DECISION');
		$entry->setSubject($subject);
		$entry->setBody($text);
		$entry->setFrom($request->getUser()->getEmail());
		$entry->setRecipients($email->getRecipients);

		// Add log entry		
		$logEntryId = ArticleLog::logEmail($articleId, $entry, $request);
	}

}
