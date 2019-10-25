{if $mimeType === 'text/plain'}
{capture assign='content'}{lang}wcf.user.oauthprovider.mail.html.headline{/lang}

{lang}wcf.user.oauthprovider.mail.text.intro{/lang}

{$scope}

{lang}wcf.user.oauthprovider.mail.text.outro{/lang}{/capture}
{include file='email_plaintext' application='wcf'}

{else}

{capture assign='content'}
    <h2>{lang}wcf.user.oauthprovider.mail.html.headline{/lang}</h2>
    {lang}wcf.user.oauthprovider.mail.html.intro{/lang}

    {lang}wcf.user.oauthprovider.mail.html.outro{/lang}
{/capture}
{include file='email_html'}
{/if}
