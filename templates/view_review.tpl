{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}

<h3>Completed Review Assignments:</h3>
<ul>
	{foreach from=$review_assignments item=assignment}
	{if $assignment->getDateCompleted() and $assignment->getRecommendation()}<li><a href="{$journal->getUrl()}/advancedreview/view_review?articleId={$article->getId()}&amp;reviewId={$assignment->getId()}">Review Assignment #{$assignment->getId()}</a></li>{/if}
	{/foreach}
</ul>

{if $view_reivew}
	<h3>Viewing Assignment {$view_reivew->getId()}</h3>

	{foreach from=$article_comments item=comment}
		{if $comment->getViewable()}
			{$comment->getComments()}
		{/if}
	{/foreach}

	<h4>Peer Review Form</h4>
	{if $body}
	{$body|nl2br}
	{else}
	<p>Nothing to display</p>
	{/if}

	<h4>Files</h4>
	{foreach from=$view_reivew->getReviewerFileRevisions() item=reviewerFile key=key}
		{if $reviewerFile->getViewable()}
		<tr valign="top">
			<td valign="middle">
				<a href="{$journal->getUrl()}/advancedreview/download_file?articleId={$article->getId()}&amp;reviewId={$assignment->getId()}&amp;fileId={$reviewerFile->getFileId()}&amp;revision={$reviewerFile->getRevision()}">{$reviewerFile->getFileName()|escape}</a>
				
			</td>
		</tr>
		{else}
		<tr valign="top">
			<td>{translate key="common.none"}</td>
		</tr>
		{/if}
		{foreachelse}
		<tr valign="top">
			<td>{translate key="common.none"}</td>
		</tr>
	{/foreach}
{/if}

{include file="common/footer.tpl"}