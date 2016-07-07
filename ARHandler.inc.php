<?php

/**
 *
 * Plugin for exporting data for ingestion by AR.
 * Written by Andy Byers, Ubiquity Press
 *
 */

import('classes.handler.Handler');
require_once('ARDAO.inc.php');

function redirect($url) {
	header("Location: ". $url); // http://www.example.com/"); /* Redirect browser */
	/* Make sure that code below does not get executed when we redirect. */
	exit;
}

function raise404($msg='404 Not Found') {
	header("HTTP/1.0 404 Not Found");
	fatalError($msg);
	return;
}

function clean_string($v) {
	// strips non-alpha-numeric characters from $v	
	return preg_replace('/[^\-a-zA-Z0-9]+/', '',$v);
}

function login_required($user) {
	if ($user === NULL) {
		redirect($journal->getUrl() . '/login/signIn?source=' . $_SERVER['REQUEST_URI']);
	}
}

class ARHandler extends Handler {

	public $dao = null;

	function ARHandler() {
		parent::Handler();
		$this->dao = new ARDAO();
	}

	# Checks if the current user is a journal manager.
	function journal_manager_required($request) {
		$user = $request->getUser();
		$journal = $request->getJournal();

		// If we have no user, redirect to index
		if ($user == NULL) {
			$request->redirect(null, 'index');
		}

		// If we have a user, grab their roles from the DAO
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$roles =& $roleDao->getRolesByUserId($user->getId(), $journal->getId());


		// Loop through the roles to check if the user is a Journal Manager
		$check = false;
		foreach ($roles as $role) {
			if ($role->getRoleId() == ROLE_ID_JOURNAL_MANAGER) {
				$check = true;
			}
		}

		// If user is a journal manager, return the user, if not, redirect to the user page.
		if ($check) {
			return $user;
		} else {
			$request->redirect(null, 'user');
		}

	}
	
	/* sets up the template to be rendered */
	function display($fname, $page_context=array()) {
		// setup template
		AppLocale::requireComponents(LOCALE_COMPONENT_OJS_MANAGER, LOCALE_COMPONENT_PKP_MANAGER);
		parent::setupTemplate();
		
		// setup template manager
		$templateMgr =& TemplateManager::getManager();
		
		// default page values
		$context = array(
			"page_title" => "Advanced Review"
		);
		foreach($page_context as $key => $val) {
			$context[$key] = $val;
		}

		$plugin =& PluginRegistry::getPlugin('generic', AR_PLUGIN_NAME);
		$tp = $plugin->getTemplatePath();
		$context["template_path"] = $tp;
		$context["article_select_template"] = $tp . "article_select_snippet.tpl";
		$context["article_pagination_template"] = $tp . "article_pagination_snippet.tpl";
		$context["disableBreadCrumbs"] = true;
		$templateMgr->assign($context); // http://www.smarty.net/docsv2/en/api.assign.tpl

		// render the page
		$templateMgr->display($tp . $fname);
	}

	//
	// views
	//
	
	/* handles requests to:
		/AR/
		/AR/index/
	*/
	function index($args, &$request) {

	}

	function editor_decision($args, &$request) {
		$userDao =& DAORegistry::getDAO('UserDAO');
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$articleCommentDao =& DAORegistry::getDAO('ArticleCommentDAO');
		$sectionEditorSubmissionDao =& DAORegistry::getDAO('SectionEditorSubmissionDAO');

		$user =& $request->getUser();
		$journal =& $request->getJournal();
		$article_id = $_GET["articleId"];
		$article =& $articleDao->getArticle($article_id);
		$sectionEditorSubmission =& $sectionEditorSubmissionDao->getSectionEditorSubmission($article->getId());

		import('classes.mail.ArticleMailTemplate');

		$decisionTemplateMap = array(
			SUBMISSION_EDITOR_DECISION_ACCEPT => 'EDITOR_DECISION_ACCEPT',
			SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => 'EDITOR_DECISION_REVISIONS',
			SUBMISSION_EDITOR_DECISION_RESUBMIT => 'EDITOR_DECISION_RESUBMIT',
			SUBMISSION_EDITOR_DECISION_DECLINE => 'EDITOR_DECISION_DECLINE'
		);

		$decisions = $sectionEditorSubmission->getDecisions();
		$decisions = array_pop($decisions); // Rounds
		$decision = array_pop($decisions);
		$decisionConst = $decision?$decision['decision']:null;

		$email = new ArticleMailTemplate(
			$sectionEditorSubmission,
			isset($decisionTemplateMap[$decisionConst])?$decisionTemplateMap[$decisionConst]:null
		);

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$body = $_POST['body'];
			$subject = $_POST['subject'];
			$to = $_POST['to'];
			$cc = $_POST['cc'];
			$bc = $_POST['bc'];
		} else {
			$authorUser =& $userDao->getById($article->getUserId());
			$authorEmail = $authorUser->getEmail();
			$email->assignParams(array(
				'editorialContactSignature' => $user->getContactSignature(),
				'authorName' => $authorUser->getFullName(),
				'journalTitle' => $journal->getLocalizedTitle()
			));
			$body = $email->getBody();
			$subject = $email->getSubject();
		}

		$journal =& $request->getJournal();

		$context = array(
			'article' => $article,
			'journal' => $journal, 
			'email' => $email,
			'body' => $body,
			'subject' => $subject,
			'first_author' => $authorEmail,
			'to' => $to,
			'cc' => $cc,
			'bc' => $bc,
		);

		$this->display('editor_decision.tpl', $context);
	}

	function settings($args, &$request) {
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user = $this->journal_manager_required($request);
		$journal = $request->getJournal();

		$settings = $this->dao->get_ar_settings($journal);

		$edit = $_GET['edit'];

		if ($edit) {
			$setting_to_edit = $this->dao->get_setting($journal, $edit);
			if ($setting_to_edit->fields['setting_name'] == 'editor_in_chief') {
				$editor_in_chief = $userDao->getById($setting_to_edit->fields['setting_value']);
				$users = $this->dao->get_users($journal);
			}
		} else {
			$setting_to_edit = false;
			$editor_in_chief = false;
			$users = false;
		}

		if ($edit && $_SERVER['REQUEST_METHOD'] == 'POST') {
			$setting_value = $_POST['setting'];
			$update = $this->dao->update_ar_setting($journal, $setting_to_edit->fields['setting_name'], $setting_value);
			redirect($_SERVER['REQUEST_URI']);
		}

		$context = array(
			'user' => $user,
			'journal' => $journal,
			'settings' => $settings,
			'page_title' => 'Advanded Review Feature Settings',
			'setting_to_edit' => $setting_to_edit,
			'editor_in_chief' => $editor_in_chief,
			'users', $users,
		);

		$this->display('settings.tpl', $context);
	}

}

?>