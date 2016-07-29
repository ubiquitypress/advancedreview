{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}

<form method="POST" enctype="multipart/form-data" name="emailForm">

<link href="http://sta.ubiquity.press/jms/global/styles/low_fat_bootstrap.css" rel="stylesheet">

<div class="row">
	<div class="col-md-3">
		<button style="margin-top: 5px;" type="submit" name="send_notification" value="yes" class="btn btn-success btn"><i class="fa fa-envelope-o">&nbsp;</i>Send notifications</button>
	</div>
	<div class="col-md-3">
		<div class="checkbox">
		 	<label><input type="checkbox" id="author" checked="checked" name="author">Send author email</label>
		</div>
		<div class="checkbox">
		 	<label><input type="checkbox" checked="checked" name="reviewer">Send copy of email to reviewers</label>
		</div>
	</div>
	<div class="col-md-6">
		<a href="{$journal->getUrl()}/editor/submissionEditing/{$article->getId()}" class="btn btn-primary btn-m pull-right">Skip notifications</a>
	</div>
</div>
<br /><br />
	<div class="row">
		<div class="col-md-12">
			<h4>Decision notification email for {$article->getLocalizedTitle()}</h4><br />
			<p class="small">To, CC and BCC lists should be comma seperated eg. test@example.com,testing@example.com</p>
		</div>
	</div>
	<div class="row">
	<div class="col-md-12">
		<div class="col-md-1">
			<label for=""><p style="font-size: 14px;">To:</p></label>
		</div>
		<div class="col-md-6">
			<div class="form-group"><input type="text" class="form-control" id="to" name="to" value="{if $to}{$to}{else}{$first_author}{/if}"></div>
		</div>
	</div>
	</div>

	<div class="row">
	<div class="col-md-12">
		<div class="col-md-1">
			<label for=""><p style="font-size: 14px;">CC:</p></label>
		</div>
		<div class="col-md-6">
			<div class="form-group"><input type="text" class="form-control" id="cc" name="cc" value="{if $cc}{$cc}{else}{$editor_in_chief}{/if}"></div>
		</div>
	</div>
	</div>

	<div class="row">
	<div class="col-md-12">
		<div class="col-md-1">
			<label for=""><p style="font-size: 14px;">BCC:</p></label>
		</div>
		<div class="col-md-6">
			<div class="form-group"><input type="text" class="form-control" id="bc" name="bc" value="{$bc}"></div>
		</div>
	</div>
	</div>

	<div class="row">
	<div class="col-md-12">
		<div class="col-md-1">
			<label for=""><p style="font-size: 14px;">Subject:</p></label>
		</div>
		<div class="col-md-6">
			<div class="form-group"><input type="text" class="form-control" id="subject" name="subject" value="{$subject}"></div>
		</div>
	</div>
	</div>

	<div class="row">
	<div class="col-md-12">
		<div class="col-md-1">
			<label for=""><p style="font-size: 14px;">Body:</p></label>
		</div>
		<div class="col-md-6">
			<div class="form-group"><textarea class="form-control" name="body" rows="20">{$body|escape}</textarea></div>
		</div>
	</div>
	</div>

	<input type="hidden" id="hiddenAuthor" name="hidennAuthor" value="" />
	<div class="row">
	<div class="col-md-12">
		<div class="col-md-2">
			<label for=""><p style="font-size: 14px;">Attachments:</p></label>
		</div>
		
		<div class="col-md-4">
			<div class="form-group"><br /><input type="file" name="newAttachment" class="uploadField"> </div>

			{assign var=attachmentNum value=1}
			{foreach from=$email->persistAttachments item=temporaryFile}
				{$attachmentNum|escape}.&nbsp;{$temporaryFile->getOriginalFileName()|escape}&nbsp;
				({$temporaryFile->getNiceFileSize()})&nbsp;
				<a href="javascript:deleteAttachment({$temporaryFile->getId()})" class="action">{translate key="common.delete"}</a>
				<br/>
				{assign var=attachmentNum value=$attachmentNum+1}
			{/foreach}

			{if $attachmentNum != 1}<br/>{/if}
			<input type="hidden" name="deleteAttachment" value="" />
			{foreach from=$email->persistAttachments item=temporaryFile}
				<input type="hidden" name="persistAttachments[]" value="{$temporaryFile->getId()}" />
			{/foreach}
		</div>
		<div class="col-md-2">
			<input style="margin-top: 10px;" name="addAttachment" type="submit" class="btn btn-primary btn-xs" value="Upload">
		</div>
	</div>
	</div>
</form>

<script type="text/javascript">
	{literal}
	$("#author").change(function() {
	    if(this.checked) {
	        $('#to').val($('#hiddenAuthor').val());
	        $('#hiddenAuthor').val('');
	    } else {
	    	$('#hiddenAuthor').val($('#to').val());
	    	$('#to').val('');
	    }
	});
	{/literal}
</script>

{include file="common/footer.tpl"}