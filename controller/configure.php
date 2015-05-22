<?php

class Configure extends \Twenga
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		if (Tools::isSubmit('submit'.Tools::ucfirst($this->name)))
		{
		    $sHashKey = Tools::getValue('TWENGA_INSTALLED');
		    if ($this->_checkHashkey($sHashKey)){
		        Configuration::updateValue('TWENGA_INSTALLED', $sHashKey);
		    	return parent::redirect('dashboard');
		    }else{
		    	$this->errors[] = $this->l('Bad config key.');
		    }
		}

		$this->context->smarty->assign('title', $this->displayName);
		$this->context->smarty->assign('iso_code', $this->getIsoCode());
		$this->context->smarty->assign('base_url', urlencode(_PS_BASE_URL_));
		$this->context->smarty->assign('request_uri', Tools::safeOutput($_SERVER['REQUEST_URI']));
		$this->context->smarty->assign('path', $this->_path);
		$this->context->smarty->assign('TWENGA_INSTALLED', pSQL(Tools::getValue('TWENGA_INSTALLED', Configuration::get('TWENGA_INSTALLED'))));
		$this->context->smarty->assign('submitName', 'submit'.Tools::ucfirst($this->name));
		
		return parent::displayForm('configure');
	}

	private function _checkHashkey($sHashKey)
	{
		if(!empty($sHashKey) && strlen($sHashKey) == '32'){
			return true;
		}
		return false;
	}
}