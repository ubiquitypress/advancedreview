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

    function getRequest() {
        $application =& PKPApplication::getApplication();

        return $application->getRequest();
    }

    function recordDecision($request) {
        //classes/submission/sectionEditor
        import('classes.submission.sectionEditor.SectionEditorAction');
        $sectionEditorSubmissionDao =& DAORegistry::getDAO('SectionEditorSubmissionDAO');
        $articleId = $request->getUserVar('articleId');
        if ($articleId !== null) {
            $submission =& $sectionEditorSubmissionDao->getSectionEditorSubmission($articleId);
            
            $decision = $request->getUserVar('decision');

            switch ($decision) {
            case SUBMISSION_EDITOR_DECISION_DECLINE:
                SectionEditorAction::recordDecision($submission, $decision, $request);
                $articleDao =& DAORegistry::getDAO('ArticleDAO');
                $articleDao->changeArticleStatus(
                    $articleId,
                    STATUS_DECLINED);
                break;
            case SUBMISSION_EDITOR_DECISION_ACCEPT:
            case SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS:
            case SUBMISSION_EDITOR_DECISION_RESUBMIT:
                SectionEditorAction::recordDecision($submission, $decision, $request);
                break;
            }
            
        }
    }


    function handleRequest($hookName, $args) {
        $page =& $args[0];
        $op =& $args[1];
        $sourceFile =& $args[2];

        if (($page == 'editor' || $page == 'sectionEditor') && $op == 'recordDecision') {
            $request = $this->getRequest();
            $this->recordDecision($request);
            $router = $request->getRouter();
            $articleId = $request->getUserVar('articleId');
            $request->redirect($router->getContext($request), 'advancedreview', 'editor_decision', null, array('articleId' => $articleId));
        }
        
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
		return "Enables advanced review features.";
	}
	
	function getTemplatePath() {
		return parent::getTemplatePath() . 'templates/';
	}

	function generate_email($text, $user, $review) {

	}

	function email_thanks_reviewer($hookName, $args) {

		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userdao =& DAORegistry::getDAO('UserDAO');
		$journaldao =& DAORegistry::getDAO('JournalDAO');
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$arDao =& new ARDAO();

		$user =& $userdao->getById($args[0]->_data['reviewerId']);
		$assignment =& $reviewAssignmentDao->getReviewAssignmentById($args[0]->_data['reviewId']);
		$journal = $journaldao->getJournal($args[0]->_data['journalId']);
		$article =& $articleDao->getArticle($assignment->getSubmissionId());

		$ack_text = $arDao->get_setting($journal, 'peer_review_ack')->fields['setting_value'];

		import('lib.pkp.classes.mail.Mail');

		$reviewerName = $assignment->getReviewerFullName();
		$articleTitle = $article->getLocalizedTitle();

		$ack_text = str_replace("{reviewerName}", $reviewerName, $ack_text);
		$ack_text = str_replace("{articleTitle}", $articleTitle, $ack_text);

		$email = new Mail();
		$email->setFrom(strtolower($journal->getPath()) . '@ubiquity.press', $journal->getLocalizedTitle());
		$email->setSubject('Review Acknowledgement');
		$email->setBody($ack_text);
		$email->addRecipient($user->getEmail());
		$email->send();

		$assignment->setDateAcknowledged(Core::getCurrentDate());
		
	}

}
