{include file='header'}

{include file='formError'}

<form action="" method="post">
	<section class="section">
	<div>
	{lang}wcf.page.oauthclients.scope.intro{/lang}
	</div>
	<dl class="wide">
					<dt><label>Produkte</label></dt>
					<dd>
						<ol class="containerList oauthScopeList">
							<li data-object-id="1">
								<div class="box32">
									<span class="icon icon32 fa-check-circle"></span>
									
									<div class="details">
										<div class="containerHeadline">
											<h3>Identify</h3>
										</div>
										<ul class="inlineList commaSeparated">
											<li>{lang}wcf.page.oauthclients.scope.identify{/lang}</li>
										</ul>
									</div>
								</div>
							</li>
							
							{if $oauth_scopes.email}
							<li data-object-id="1">
								<div class="box32">
									<span class="icon icon32 fa-check-circle"></span>
									
									<div class="details">
										<div class="containerHeadline">
											<h3>Email</h3>
										</div>
										<ul class="inlineList commaSeparated">
											<li>{lang}wcf.page.oauthclients.scope.email{/lang}</li>
										</ul>
									</div>
								</div>
							</li>
							{/if}
							
							{if $oauth_scopes.profile}
							<li data-object-id="1">
								<div class="box32">
									<span class="icon icon32 fa-check-circle"></span>
									
									<div class="details">
										<div class="containerHeadline">
											<h3>Profile</h3>
										</div>
										<ul class="inlineList commaSeparated">
											<li>{lang}wcf.page.oauthclients.scope.profile{/lang}</li>
										</ul>
									</div>
								</div>
							</li>
							{/if}
							<li data-object-id="1">
								<div class="box32">
									<span class="icon icon32 fa-times-circle"></span>
									
									<div class="details">
										<ul class="inlineList commaSeparated">
											<li>{lang}wcf.page.oauthclients.scope.noaccess{/lang}</li>
										</ul>
									</div>
								</div>
							</li>
						</ol>
						
					</dd>
				</dl>
	
			
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="hide" value="1"> {lang}wcf.page.oauthclients.dismiss{/lang}</label>
				</dd>
			</dl>
	</section>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.page.oauthclients.authorize{/lang}" accesskey="s">
		<button class="button buttonSecondary" type="submit" name="cancel">{lang}wcf.page.oauthclients.cancel{/lang}</button>
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}