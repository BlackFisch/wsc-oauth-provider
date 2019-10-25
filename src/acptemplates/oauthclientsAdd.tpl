{include file='header' pageTitle='wcf.acp.oauthclients.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.oauthclients.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='OauthclientsList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.oauthclients.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='OauthclientsAdd'}{/link}{else}{link controller='OauthclientsEdit' object=$oauthclients}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'name'} class="formError"{/if}>
			<dt><label for="name">{lang}wcf.oauthclients.name{/lang}</label></dt>
			<dd>
				<input type="text" id="name" name="name" value="{$name}" required autofocus maxlength="255" class="long">
				{if $errorField == 'name'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.oauthclients.name.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'redirectUrl'} class="formError"{/if}>
			<dt><label for="redirectUrl">{lang}wcf.oauthclients.redirectUrl{/lang}</label></dt>
			<dd>
				<input type="url" id="redirectUrl" name="redirectUrl" value="{$redirectUrl}" required maxlength="255" class="long" placeholder="https://example.com">
				{if $errorField == 'redirectUrl'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.oauthclients.redirectUrl.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		<dl>
			<dt><label for="showSignature">{lang}wcf.acp.oauthclients.implicit{/lang}</label></dt>
			<dd><ol class="flexibleButtonGroup optionTypeBoolean">
					<li>
						<input type="radio" id="allowImplicit" name="implicit" value="1" {if $implicit}checked{/if}>
						<label for="allowImplicit" class="green"><span class="icon icon16 fa-check"></span> {lang}wcf.acp.option.type.boolean.yes{/lang}</label>
					</li>
					<li>
						<input type="radio" id="allowImplicit_no" name="implicit" value="0" {if !$implicit}checked{/if}>
						<label for="allowImplicit_no" class="red"><span class="icon icon16 fa-times"></span> {lang}wcf.acp.option.type.boolean.no{/lang}</label>
					</li>
				</ol>
				<small>{lang}wcf.acp.oauthclients.implicit.description{/lang}</small>
			</dd>
		</dl>
		<dl>
			<dt><label for="showSignature">{lang}wcf.acp.oauthclients.password{/lang}</label></dt>
			<dd><ol class="flexibleButtonGroup optionTypeBoolean">
					<li>
						<input type="radio" id="allowpassword" name="password" value="1" {if $password}checked{/if}>
						<label for="allowpassword" class="green"><span class="icon icon16 fa-check"></span> {lang}wcf.acp.option.type.boolean.yes{/lang}</label>
					</li>
					<li>
						<input type="radio" id="allowpassword_no" name="password" value="0" {if !$password}checked{/if}>
						<label for="allowpassword_no" class="red"><span class="icon icon16 fa-times"></span> {lang}wcf.acp.option.type.boolean.no{/lang}</label>
					</li>
				</ol>
				<small>{lang}wcf.acp.oauthclients.password.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='dataFields'}
	</div>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
