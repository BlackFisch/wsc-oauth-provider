{capture assign='pageTitle'}{lang}wcf.user.menu.security.oauthApplicationList{/lang}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{include file='userMenuSidebar'}

{include file='header' __sidebarLeftHasMenu=true}

<script data-relocate="true">
	$(function() {
		new WCF.Action.Delete('wcf\\data\\oauthauthorize\\OauthauthorizeAction', $('.jsOauthApplicationRow'));
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.user.menu.security.oauthApplicationList{/lang}</h1>
	</div>
</header>

{hascontent}
	<div class="paginationTop">
		{content}
			{pages print=true assign='pagesLinks' controller='OauthApplicationsList' link="pageNo=%d"}
		{/content}
	</div>
{/hascontent}

{if $objects|count > 0}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th></th>
					<th>{lang}wcf.page.oauthApplicationList.table.client{/lang}</th>
					<th>{lang}wcf.page.oauthApplicationList.table.scope{/lang}</th>
					<th>{lang}wcf.page.oauthApplicationList.table.lastUsed{/lang}</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$objects item=oauthauthorize}
					<tr class="jsOauthApplicationRow">
						<td class="columnIcon">
							<span class="icon icon16 fa-remove pointer jsDeleteButton jsTooltip" title="{lang}wcf.page.oauthApplicationList.app_delete_title{/lang}" data-object-id="{@$oauthauthorize->oauthauthorizeID}" data-confirm-message="{lang}wcf.page.oauthApplicationList.app_delete_question{/lang}"></span>
						</td>
						<td class="columnText">{$oauthauthorize->getClientName()}</td>
						<td class="columnText oauthApplicationListScopes">{@$oauthauthorize->getScopesHtml()}
                            
                        </td>
						<td class="columnDate">{@$oauthauthorize->lastUsed|time}</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

<footer class="contentFooter">
	{hascontent}
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}

	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}{event name='contentFooterNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}