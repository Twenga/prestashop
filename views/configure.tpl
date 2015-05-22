<h2><img src="{$path}assets/img/logo.png" alt="" title="" /> {$title}</h2>
<form action="{$request_uri}" method="post">
	<fieldset>
	<legend>{l s='Settings' mod='example'}</legend>
	<p>{l s='Your subscrition link:' mod='example'} <a target="_blank" href="https://www.twenga-solutions.com/{$iso_code}/subscription/?m=ps&subscriptionlink={$base_url}">www.twenga-solutions.com</a></p>
	<label>{l s='Your hashkey' mod='example'}</label>
	<div class="margin-form">
		<input type="text" size="32" name="TWENGA_INSTALLED" value="{$TWENGA_INSTALLED}" />
		<p class="clear">{l s='mandatory' mod='example'}</p>
	</div>
	<center><input type="submit" name="{$submitName}" value="{l s='Save' mod='example'}" class="button" /></center>
	</fieldset>
</form>