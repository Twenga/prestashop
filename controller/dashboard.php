<?php

class Dashboard extends \Twenga
{

	private $_bTrackingInstalled = false;

	public function __construct()
	{
		parent::__construct();

		if ($this->isRegisteredInHook('displayPayment') || $this->isRegisteredInHook('Payment'))
		{
			$this->_bTrackingInstalled = true;
		}else{
			$this->_bTrackingInstalled = false;
		}

		if (Tools::isSubmit('submit'.Tools::ucfirst($this->name)))
		{
			if($this->_bTrackingInstalled){
				$this->_uninstallTracking();
				$this->_bTrackingInstalled = false;
			}else{
				$this->_installTracking();
				$this->_bTrackingInstalled = true;
			}
		}
	}

	public function index()
	{
		$this->context->smarty->assign('iso_code', $this->getIsoCode());
		$this->context->smarty->assign('base_url', _PS_BASE_URL_);
		$this->context->smarty->assign('request_uri', Tools::safeOutput($_SERVER['REQUEST_URI']));
		$this->context->smarty->assign('TWENGA_INSTALLED', pSQL(Tools::getValue('TWENGA_INSTALLED', Configuration::get('TWENGA_INSTALLED'))));
		$this->context->smarty->assign('submitName', 'submit'.Tools::ucfirst($this->name));
		$this->context->smarty->assign('submitValue', ($this->_bTrackingInstalled === true)? $this->l('Uninstall Smart Tracker') : $this->l('Install Smart Tracker'));
		return parent::displayForm('dashboard');
	}

	public function _installTracking()
	{
		$this->installHook();
	}

	public function _uninstallTracking()
	{
		$this->uninstallHook();
	}
}