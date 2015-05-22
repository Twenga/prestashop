<?php
if (!defined('_PS_VERSION_'))
    exit;

if (!defined('_MYSQL_ENGINE_'))
    define('_MYSQL_ENGINE_', 'MyISAM');
 
class Twenga extends Module
{
	public function __construct()
	{
		$this->name = 'twenga';
		$this->tab = 'smart_shopping';
		$this->version = '3.0.0';
		$this->author = 'Twenga';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
		$this->bootstrap = true;
        $this->dependencies = array();
        $this->limited_countries = array();

		parent::__construct();

		$this->displayName = $this->l('Smart Tracking');
		$this->description = $this->l('By using Smart Tracking, the merchant is informed and accepts that Twenga stocks a sample of tracked pages for settings purposes and for no longer than 7 days. This sample cannot exceed 1% of all tracked pages during the period.');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if ($this->active && Configuration::get('TWENGA_INSTALLED') == '')
            $this->warning = $this->l('You have to configure your module');

        $this->errors = array();
	}

    public function install()
    {
        $sql = array();
        include(dirname(__FILE__).'/model/install.php');
        foreach ($sql as $s)
            if (!Db::getInstance()->execute($s))
            return false;

        if (Shop::isFeatureActive()) Shop::setContext(Shop::CONTEXT_ALL);

        if (!parent::install() ||
            !Configuration::updateValue('TWENGA_INSTALLED', '')
        ){
            return false;
        }

        return true;
    }

	public function uninstall()
	{
        $sql = array();
        include(dirname(__FILE__).'/sql/uninstall.php');
        foreach ($sql as $s)
            if (!Db::getInstance()->execute($s))
            return false;

        if (!parent::uninstall() ||
            !Configuration::deleteByName('TWENGA_INSTALLED')
        ){
            return false;
        }

        return true;
	}

    public function getContent($controller = null)
    {
        $controller = ($controller)? : $this->router();
        include(dirname(__FILE__).'/controller/'.strtolower($controller).'.php');
        $oController = new $controller($this);
        return $oController->index();
    }

    public function displayForm($view)
    {
        $output = '';
        if (isset($this->errors) && count($this->errors)){
            $output .= $this->displayError(implode('<br />', $this->errors));
        }
        return $output.$this->display(__FILE__, 'views/'.strtolower($view).'.tpl');
    }

    public function router()
    {
        $controller = 'Configure';
        if(Configuration::get('TWENGA_INSTALLED') != ''){
            $controller = 'Dashboard';
        }
        return $controller;
    }

    public function redirect($controller = '')
    {
        return $this->getContent($controller);
    }

    public function getIsoCode()
    {
        $iso_code = strtolower($this->context->country->iso_code);
        if($iso_code == 'gb') $iso_code = 'en';
        return $iso_code;
    }

    public function getDomain()
    {
        $iso_code = strtolower($this->context->country->iso_code);
        if($iso_code == 'gb') $iso_code = 'co.uk';
        return $iso_code;
    }

    public function installHook()
    {
        $this->registerHook('displayHome');
        $this->registerHook('displayProductButtons');
        $this->registerHook('displayShoppingCart');
        $this->registerHook('displayPayment');
        $this->registerHook('payment');
        $this->registerHook('orderConfirmation');
    }

    public function uninstallHook()
    {
        $this->unregisterHook('displayHome');
        $this->unregisterHook('displayProductButtons');
        $this->unregisterHook('displayShoppingCart');
        $this->unregisterHook('displayPayment');
        $this->unregisterHook('payment');
        $this->unregisterHook('orderConfirmation');
    }

    /**
    * Function HOOK Home
    */
    public function hookDisplayHome($params)
    {
        return $this->doHook($params, 'home');
    }

    /**
    * Function HOOK Product
    */
    public function hookDisplayProductButtons($params)
    {
        return $this->doHook($params, 'product');
    }

    /**
    * Function HOOK Basket
    */
    public function hookDisplayShoppingCart($params)
    {
        return $this->doHook($params, 'basket');
    }

    /**
    * Function HOOK Transaction
    */
    public function hookPayment($params)
    {
        return $this->doHook($params, 'basket');
    }

    public function hookOrderConfirmation($params){
        return $this->doHook($params, 'transaction');
    }

    /**
    * Function DO HOOK
    */
    public function doHook($aParams, $sEvent = '')
    {
        $aParams['event'] = $sEvent;
        include(dirname(__FILE__).'/service/tracking.php');
        $oTracking = new Tracking();     
        return $oTracking->getScript($aParams); 
    }
}