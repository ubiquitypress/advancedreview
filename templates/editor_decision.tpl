{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-T8Gy5hrqNKT+hzMclPo118YTQO6cYprQmhrYwIiQ/3axmI1hQomh7Ud2hPOy8SP1" crossorigin="anonymous">

<form method="POST" enctype="multipart/form-data" name="emailForm">

<div class="row">
	<div class="col-md-3">
		<button style="margin-top: 5px;" type="submit" class="btn btn-success btn-lg"><i class="fa fa-envelope-o">&nbsp;</i>Send notifications</button>
	</div>
	<div class="col-md-3">
		<div class="checkbox">
		 	<label><input type="checkbox" checked="checked" value="author">Send author email</label>
		</div>
		<div class="checkbox">
		 	<label><input type="checkbox" checked="checked" value="reviewer">Send BCC email to reviewers</label>
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
			<div class="form-group"><input style="height: 20px;" type="text" class="form-control" id="cc" name="cc" value="{$cc}"></div>
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
{include file="common/footer.tpl"}