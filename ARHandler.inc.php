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

	function append_links_to_body($request, $body, $sectionEditorSubmission){
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignments =& $reviewAssignmentDao->getBySubmissionId($sectionEditorSubmission->getId(), $sectionEditorSubmission->getCurrentRound());

		$new_body = "\n---------------------------------\n\nReview Links\n\nBelow you will find links to view your reviews within the JMS. You will be asked to login.\n\n";

		$index = 1;
		foreach ($reviewAssignments as $reviewAssignment) {
			var_dump($reviewAssignment);
			echo "<br /><br />";
			$url = $request->getJournal()->getUrl() . 'advancedreview/view_review?articleId=' . $sectionEditorSubmission->getId() . '&reviewId=' . $reviewAssignment->getId();
			$new_body = $new_body . 'Review #' . $index . ': ' . $url . "\n";
			$index++;
		}

		$body = $body . $new_body;

		return $body;
	}

	function login_required($request){
		$user = $request->getUser();
		if ($user === NULL) {
			redirect($request->getJournal()->getUrl() . '/login/signIn?source=' . $_SERVER['REQUEST_URI']);
		}
	}

	function check_and_get_article($request) {
		$article_id = $request->_requestVars['articleId'];
		$user = $request->getUser();

		if (!$article_id){
			raise404();
		}

		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$article =& $articleDao->getArticle($article_id);

		if (!$article || $article->getUserId() != $user->getId()){
			raise404("You are not the owner of this article.");
		}

		return $article;
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
		$this->login_required($request);

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

			$body = $this->append_links_to_body($request, $body, $sectionEditorSubmission);

			$eic_setting = $this->dao->get_setting($journal, 'editor_in_chief');
			$editor_in_chief = $userDao->getById($eic_setting->fields['setting_value']);
		}

		$journal =& $request->getJournal();

		$context = array(
			'article' => $article,
			'journal' => $journal, 
			'email' => $email,
			'body' => $body,
			'subject' => $subject,
			'first_author' => $authorEmail,
			'editor_in_chief' => $editor_in_chief->getEmail(),
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

	function view_review($args, &$request) {
		$this->login_required($request);
		$article = $this->check_and_get_article($request);
		$journal = $request->getJournal();

		$sectionEditorSubmissionDao =& DAORegistry::getDAO('SectionEditorSubmissionDAO');
		$sectionEditorSubmission =& $sectionEditorSubmissionDao->getSectionEditorSubmission($article->getId());

		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignments =& $reviewAssignmentDao->getBySubmissionId($sectionEditorSubmission->getId(), $sectionEditorSubmission->getCurrentRound());

		if ($_GET['reviewId']) {
			$review_id = $_GET['reviewId'];
			$view_reivew =& $reviewAssignmentDao->getReviewAssignmentById($review_id);

			$articleCommentDao =& DAORegistry::getDAO('ArticleCommentDAO');
			$article_comments =& $articleCommentDao->getArticleComments($sectionEditorSubmission->getId(), COMMENT_TYPE_PEER_REVIEW, $view_reivew->getId());

			$body = '';

			if ($view_reivew->getReviewFormId()) {
				$reviewFormId = $view_reivew->getReviewFormId();
				$reviewId = $view_reivew->getId();
				$reviewFormResponseDao =& DAORegistry::getDAO('ReviewFormResponseDAO');
				$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');
				$reviewFormElements =& $reviewFormElementDao->getReviewFormElements($reviewFormId);

				foreach ($reviewFormElements as $reviewFormElement) {
					$body .= String::html2text($reviewFormElement->getLocalizedQuestion()) . ": \n";
					$reviewFormResponse = $reviewFormResponseDao->getReviewFormResponse($reviewId, $reviewFormElement->getId());

					if ($reviewFormResponse) {
						$possibleResponses = $reviewFormElement->getLocalizedPossibleResponses();
						if (in_array($reviewFormElement->getElementType(), $reviewFormElement->getMultipleResponsesElementTypes())) {
							if ($reviewFormElement->getElementType() == REVIEW_FORM_ELEMENT_TYPE_CHECKBOXES) {
								foreach ($reviewFormResponse->getValue() as $value) {
									$body .= "\t" . String::html2text($possibleResponses[$value-1]['content']) . "\n";
								}
							} else {
								$body .= "\t" . String::html2text($possibleResponses[$reviewFormResponse->getValue()-1]['content']) . "\n";
							}
							$body .= "\n";
						} else {
							$body .= "\t" . $reviewFormResponse->getValue() . "\n\n";
						}
					}
				}
				$body .= "------------------------------------------------------\n\n";
			}
		}

		$context = array(
			'user' => $request->getUser(),
			'journal' => $journal,
			'page_title' => "Reviews for " . $article->getLocalizedTitle(),
			'review_assignments' => $reviewAssignments,
			'article' => $article,
			'view_reivew' => $view_reivew,
			'article_comments' => $article_comments,
			'body' => $body,
		);

		$this->display('view_review.tpl', $context);

	}

}

?>