{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}

<link href="http://localhost:8000/styles/bootstrap.min.css" rel="stylesheet">

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