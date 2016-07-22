{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}
<link href="http://localhost:8000/styles/bootstrap.min.css" rel="stylesheet">

<form method="POST" enctype="multipart/form-data" name="emailForm">

<div class="row">
	<div class="col-md-3">
		<button style="margin-top: 5px;" type="submit" name="send_notification" value="yes" class="btn btn-success btn-lg"><i class="fa fa-envelope-o">&nbsp;</i>Send notifications</button>
	</div>
	<div class="col-md-3">
		<div class="checkbox">
		 	<label><input type="checkbox" id="author" checked="checked" name="author">Send author email</label>
		</div>
		<div class="checkbox">
		 	<label><input type="checkbox" checked="checked" name="reviewer">Send BCC email to reviewers</label>
		</div>
	</div>
	<div class="col-md-6">
		<button class="btn btn-primary btn-m pull-right">Skip notifications</button>
	</div>
</div>

<hr style="margin-bottom: 0px;" />

	<h4>Decision notification email for {$article->getLocalizedTitle()}</h4><br />
	<p class="small">To, CC and BCC lists should be comma seperated eg. test@example.com, testing@example.com</p>
	<div class="row">
		<div class="col-md-2">
			<label for=""><p style="font-size: 14px;">To:</p></label>
		</div>
		<div class="col-md-6">
			<div class="form-group"><input style="height: 20px;" type="text" class="form-control" id="to" name="to" value="{if $to}{$to}{else}{$first_author}{/if}"></div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-2">
			<label for=""><p style="font-size: 14px;">CC:</p></label>
		</div>
		<div class="col-md-6">
			<div class="form-group"><input style="height: 20px;" type="text" class="form-control" id="cc" name="cc" value="{if $cc}{$cc}{else}{$editor_in_chief}{/if}"></div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-2">
			<label for=""><p style="font-size: 14px;">BCC:</p></label>
		</div>
		<div class="col-md-6">
			<div class="form-group"><input style="height: 20px;" type="text" class="form-control" id="bc" name="bc" value="{$bc}"></div>
		</div>
	</div>

	<br />

	<div class="row">
		<div class="col-md-2">
			<label for=""><p style="font-size: 14px;">Subject:</p></label>
		</div>
		<div class="col-md-6">
			<div class="form-group"><input style="height: 20px;" type="text" class="form-control" id="subject" name="subject" value="{$subject}"></div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-2">
			<label for=""><p style="font-size: 14px;">Body:</p></label>
		</div>
		<div class="col-md-6">
			<div class="form-group"><textarea class="form-control" name="body" rows="20">{$body|escape}</textarea></div>
		</div>
	</div>
	<input type="hidden" id="hiddenAuthor" name="hidennAuthor" value="" />
	<div class="row">
		<div class="col-md-2">
			<label for=""><p style="font-size: 14px;">Attachments:</p></label>
		</div>
		
		<div class="col-md-6">
			<div class="form-group"><input type="file" name="newAttachment" class="uploadField"> <input name="addAttachment" type="submit" class="btn btn-primary btn-sm" value="Upload"></div>

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