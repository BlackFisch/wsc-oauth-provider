{include file='header' pageTitle='wcf.acp.oauthclients.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.oauthclients.list{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='OauthclientsAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.link.oauthclients.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="OauthclientsList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox" id="oauthclientsTableContainer">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnOauthclientsID{if $sortField == 'oauthclientID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='OauthclientsList'}pageNo={@$pageNo}&sortField=oauthclientID&sortOrder={if $sortField == 'oauthclientID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.oauthclients.clientID{/lang}</a></th>
					<th class="columnTitle columnFirstName{if $sortField == 'name'} active {@$sortOrder}{/if}"><a href="{link controller='OauthclientsList'}pageNo={@$pageNo}&sortField=name&sortOrder={if $sortField == 'name' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.oauthclients.name{/lang}</a></th>
					<th class="columnTitle columnLastName{if $sortField == 'redirectUrl'} active {@$sortOrder}{/if}"><a href="{link controller='OauthclientsList'}pageNo={@$pageNo}&sortField=redirectUrl&sortOrder={if $sortField == 'redirectUrl' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.oauthclients.redirectUrl{/lang}</a></th>
					<th class="columnTitle columnLastName{if $sortField == 'lastModified'} active {@$sortOrder}{/if}"><a href="{link controller='OauthclientsList'}pageNo={@$pageNo}&sortField=lastModified&sortOrder={if $sortField == 'lastModified' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.oauthclients.lastModified{/lang}</a></th>
					
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=oauthclients}
					<tr class="jsOauthclientsRow">
						<td class="columnIcon">
							<a href="{link controller='OauthclientsEdit' object=$oauthclients}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{$oauthclients->oauthclientID}" data-confirm-message-html="{lang __encode=true}wcf.acp.oauthclients.delete.confirmMessage{/lang}"></span>
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{$oauthclients->oauthclientID}</td>
						<td class="columnTitle columnFirstName"><a href="{link controller='OauthclientsEdit' object=$oauthclients}{/link}">{$oauthclients->name}</a></td>
						<td class="columnTitle columnLastName"><a href="{link controller='OauthclientsEdit' object=$oauthclients}{/link}">{$oauthclients->redirectUrl}</a></td>
						<td class="columnTitle columnLastName">{@$oauthclients->lastModified|time}</td>
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
		<nav class="contentFooterNavigation">
			<ul>
				<li><a href="{link controller='OauthclientsAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.link.oauthclients.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

<script data-relocate="true">
	$(function() {
		new WCF.Action.Delete('wcf\\data\\oauthclient\\OauthclientAction', '.jsOauthclientsRow');
		
		var options = { };
		{if $pages > 1}
			options.refreshPage = true;
			{if $pages == $pageNo}
				options.updatePageNumber = -1;
			{/if}
		{else}
			options.emptyMessage = '{lang}wcf.global.noItems{/lang}';
		{/if}
		
		new WCF.Table.EmptyTableHandler($('#oauthclientsTableContainer'), 'jsOauthclientsRow', options);
	});
</script>

{include file='footer'}
