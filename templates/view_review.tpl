{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}

<h3>Completed Review Assignments:</h3>
<ul>
	{foreach from=$review_assignments item=assignment}
	{if $assignment->getDateCompleted()}<li><a href="{$journal->getUrl()}/advancedreview/view_review?articleId=1&amp;reviewId={$assignment->getId()}">Review Assignment #{$assignment->getId()}</a></li>{/if}
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
{/if}

{include file="common/footer.tpl"}