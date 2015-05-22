<div>
	<p>{l s='Your hashkey:' mod='example'} {$TWENGA_INSTALLED}</p>
	<p>{l s='Login:'} <a target="_blank" href="https://www.twenga-solutions.com/{$iso_code}/login/?m=ps">www.twenga-solutions.com</a></p>
	<p>{l s='Your feed URL:'} <a target="_blank" href="{$base_url}/modules/twenga/export.php">{$base_url}/modules/twenga/export.php</a></p>

	<form action="{$request_uri}" method="post">
		<fieldset>
			<legend>{l s='Settings tracking' mod='example'}</legend>
			<center><input type="submit" name="{$submitName}" value="{$submitValue}" class="button" /></center>
		</fieldset>
	</form>

</div>