<?php

/**
* Twenga Client API
*/
class TwengaClientApi extends \Twenga
{
	private $_sUserName;
	private $_sUserPwd;

	public function __construct()
	{
		parent::__construct();

		$this->_sUserName = Configuration::deleteByName('TWENGA_USER_NAME');
		$this->_sUserPwd = Configuration::deleteByName('TWENGA_PASSWORD');
	}

	private static function call($sQuery, $aParams = array(), $bAuthentication = true)
	{
		$aDefaultParams = array(
			CURLOPT_HEADER => TRUE,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLINFO_HEADER_OUT => TRUE,
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
		);

		if ($bAuthentication)
		{
			$aDefaultParams[CURLOPT_USERPWD] = md5($this->_sUserName).':'.md5($this->_sUserPwd);
		}

		$session = curl_init($sQuery);
		$aOption = $aDefaultParams + $aParams;
		curl_setopt_array($session, $aOption);
		$response = curl_exec($session);

		$status_code = (int)curl_getinfo($session, CURLINFO_HTTP_CODE);
		if ($status_code === 0)
		throw new TwengaException('CURL Error: '.curl_error($session));

		$response = explode("\r\n\r\n", $response);
		$header = $response[0];
		$response = $response[1];	

		curl_close($session);
		return array('status_code' => $status_code, 'response' => $response);
	}
}