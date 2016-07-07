{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}

<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-T8Gy5hrqNKT+hzMclPo118YTQO6cYprQmhrYwIiQ/3axmI1hQomh7Ud2hPOy8SP1" crossorigin="anonymous">


<table class="table">
	<tr>
		<th>Setting Name</th>
		<th>Setting Value</th>
		<th>Edit</th>
	</tr>

	{foreach from=$settings item=setting}
	<tr>
		<td>{$setting.setting_name}</td>
		<td>{if $setting.setting_name == 'editor_in_chief'}User ID: {/if}{$setting.setting_value|nl2br}</td>
		<td><a href="{$journal->getUrl()}/advancedreview/settings/?edit={$setting.setting_name}">Edit Setting</a></td>
	</tr>
	{/foreach}
</table>
{$users}
{if $setting_to_edit}
<hr />
	<form method="POST">
	{if $editor_in_chief}
	<h4>Current user: {$editor_in_chief->getFirstName()} {$editor_in_chief->getLastName()}</h4>
	{else}
	<h4>Editing: {$setting_to_edit->fields.setting_name}</h4><br />
	{/if}
	
	<textarea class="form-control" name="setting" rows="10">{$setting_to_edit->fields.setting_value}</textarea>
	<br />
	<button type="submit" class="btn btn-success">Submit</button>
	</form>
	

{/if}
{include file="common/footer.tpl"}