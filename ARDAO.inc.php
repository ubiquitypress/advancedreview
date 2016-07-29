<?php

/**
 *
 * Plugin for exporting data for ingestion by AR.
 * Written by Andy Byers, Ubiquity Press
 * Funded by INASP
 *
 */


class ARDAO extends DAO {

	function get_ar_settings($journal) {
		$sql = <<< EOF
			SELECT * FROM plugin_settings
			WHERE journal_id = ?
			AND plugin_name = 'arplugin';
EOF;
		return $this->retrieve($sql, array($journal->getId()));
	}

	function get_setting($journal, $setting_name) {
		$sql = <<< EOF
			SELECT * FROM plugin_settings
			WHERE journal_id = ?
			AND setting_name = ?;
EOF;
		return $this->retrieve($sql, array($journal->getId(), $setting_name));
	}

	function update_ar_setting($journal, $setting_name, $setting_value) {
		$sql = <<< EOF
			UPDATE plugin_settings 
			SET
			setting_value = ?
			WHERE
			plugin_name = ? AND journal_id = ? AND setting_name = ?
EOF;
		$commit = $this->update($sql, array($setting_value, 'arplugin', $journal->getId(), $setting_name));

		return $commit;
	}

	function get_users($journal){
		$sql = <<< EOF
			SELECT u.* FROM users AS u
			JOIN roles as r ON u.user_id = r.user_id
			WHERE r.journal_id = ? and r.role_id = 256
			GROUP BY u.user_id
			ORDER BY u.last_name
EOF;
		return $this->retrieve($sql, array($journal->getId()));
	}

	function user_review_check($user_id, $article_id, $review_round) {
		$sql = <<< EOF
		SELECT * FROM review_assignments
		WHERE reviewer_id = ? and submission_id = ? and round = ?
EOF;

		$check = $this->retrieve($sql, array($user_id, $article_id, $review_round));

		if($check->_numOfRows > 0) {
			return true;
		} else {
			return false;
		}
	}
}

