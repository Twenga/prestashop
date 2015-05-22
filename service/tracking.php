<?php

/**
* build the tracking by Twenga.
*/
class Tracking extends \Twenga
{

	public function __construct()
	{
		parent::__construct();
	}

	public function getScript($aParamsToTwenga)
	{
		$tracking_code = '';
		try{
	        $oCustomer = new Customer($aParams['cart']->id_customer);
	        $oCurrency = new Currency($aParams['cart']->id_currency);
	        $aAddress = $oCustomer->getAddresses($aParams['cart']->id_lang);

	        $aAddress = $aAddress[0];
	        $sUserCountry = '';

	        if(isset($aAddress['id_country']) && !empty($aAddress['id_country'])){
	        	$sUserCountry = Country::getIsoById($aAddress['id_country']);
	        }

			$tax = ($aParams['objOrder']->total_paid_tax_incl-$aParams['objOrder']->total_shipping_tax_incl) - ($aParams['objOrder']->total_paid_tax_excl-$aParams['objOrder']->total_shipping_tax_excl);
		    $tva = $aParams['objOrder']->carrier_tax_rate;
	       
	        $aParamsToTwenga['user_id'] = $aParams['cart']->id_customer;
	        $aParamsToTwenga['user_global_id'] = md5($oCustomer->email);
	        $aParamsToTwenga['user_email'] = $oCustomer->email;
	        $aParamsToTwenga['user_firstname'] = $oCustomer->firstname;
	        $aParamsToTwenga['user_city'] = ($aParams['cart']->id_customer)? $aAddress['city'] : '';
	        $aParamsToTwenga['user_state'] = ($aParams['cart']->id_customer)? $aAddress['state'] : '';
	        $aParamsToTwenga['user_country'] = ($aParams['cart']->id_customer)? $sUserCountry : '';
	        $aParamsToTwenga['user_segment'] = '';
	        $aParamsToTwenga['user_is_customer'] = 1;
	        $aParamsToTwenga['ecommerce_platform'] = 'Prestashop';
	        $aParamsToTwenga['tag_platform'] = '';
	        $aParamsToTwenga['basket_id'] = $aParams['cart']->id;
	        $aParamsToTwenga['currency'] = $oCurrency->iso_code;
	        $aParamsToTwenga['total_ht'] = $aParams['objOrder']->total_paid_tax_excl->$aParams['objOrder']->total_shipping_tax_excl;
	        $aParamsToTwenga['tva'] = ($tva !== false) ? Tools::ps_round($tva, 2) : '';
	        $aParamsToTwenga['total_ttc'] = $aParams['objOrder']->total_paid_tax_incl->$aParams['objOrder']->total_shipping_tax_incl;
	        $aParamsToTwenga['shipping'] = $aParams['objOrder']->total_shipping_tax_incl;
	        $aParamsToTwenga['tax'] = $tax;

	        if(isset($aParams['objOrder']) && !empty($aParams['objOrder'])){
	        	$aParamsToTwenga['order_id'] = $aParams['objOrder']->id;
	        }

	        $aParamsToTwenga['items'] = array();

	        if($sEvent == 'product' && (isset($_POST['id_product'])) || isset($_GET['id_product'])){
		        $iIdProduct = (isset($_POST['id_product'])) ? $_POST['id_product'] : $_GET['id_product'];
		        $oProduct = new Product($iIdProduct);
		        if($oProduct){
		        	$oCategory = new Category($oProduct->id_category_default);
		        	if($oCategory){
				        $arr_item = array();
				        $arr_item['price'] = $oProduct->price;
				        $arr_item['quantity'] = '';
				        $arr_item['ref_id'] = $oProduct->id_product;
				        $arr_item['item_id'] = $iIdProduct;
				        $arr_item['name'] = $oProduct->name[1];
				        $arr_item['category_name'] = $oCategory->name;
				        $aParamsToTwenga['items'][] = $arr_item;
			        }
		        }
	        }elseif(isset($aParams['objOrder']) && !empty($aParams['objOrder'])){
		        foreach ($aParams['objOrder']->getProducts() as $product)
		        {
			        $oCategory = new Category($product['id_category_default']);
			        $arr_item = array();
			        if ($product['unit_price_tax_excl']!= '')
			        	$arr_item['price'] = (float)$product['unit_price_tax_excl'];
			        if ($product['product_quantity'] != '')
			        	$arr_item['quantity'] = (int)$product['product_quantity'];
			        if ($product['reference'] != '')
			        	$arr_item['ref_id'] = (string)$product['id_product'].'D'.$product['id_product_attribute'];
			        if ($product['id_product'] != '')
			        	$arr_item['item_id'] = (string)$product['id_product'];
			        if ($product['product_name'] != '')
			        	$arr_item['name'] = (string)$product['product_name'];
			        if (isset($oCategory) && !empty($oCategory))
			        	$arr_item['category_name'] = $oCategory->name;
			        $aParamsToTwenga['items'][] = $arr_item;
		        }   
	        }else{	
				if($aParams['cart']){
			        foreach ($aParams['cart']->getProducts() as $product)
			        {
				        $arr_item = array();
				        if ($product['price']!= '')
				        $arr_item['price'] = (float)$product['price'];
				        if ($product['cart_quantity'] != '')
				        $arr_item['quantity'] = (int)$product['cart_quantity'];
				        if ($product['reference'] != '')
				        $arr_item['ref_id'] = $product['id_product'].'D'.$product['id_product_attribute'];  
				        if ($product['id_product'] != '')
				        $arr_item['item_id'] = (string)$product['id_product'];
				        if ($product['name'] != '')
				        $arr_item['name'] = (string)$product['name'];
				        if ($product['category'])
				        $arr_item['category_name'] = (string)$product['category'];
				        $aParamsToTwenga['items'][] = $arr_item;
			        }
				}
	        }

	        $aParamsToTwenga = array_filter($aParamsToTwenga);
	        $tracking_code = $this->_buildScript($aParamsToTwenga);
	    }catch (Exception $e) {
    		return '';
		}

        return $tracking_code;
	}

	private function _buildScript($aParamsToTwenga)
	{
		$sHashKey = Configuration::get('TWENGA_INSTALLED');
		if(empty($sHashKey) || strlen($sHashKey) < 32){
			return '';
		}

		if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' && Configuration::get('PS_SSL_ENABLED')!= '0') {
			$sProtocol = 'https';
		}else{
			$sProtocol = 'http';
		}

		$sDomain = strtolower($this->getDomain());
		
		$sTrackingScript = '<script async="true" language="javascript" type="text/javascript" src="'.$sProtocol.'://tracker.twenga.'.$sDomain.'/tracker.js"></script>';
		$sTrackingScript .= '<div id="twcm_main" style="display:none;">';

		$sTrackingScript .= '
		<div class="twcm_key">'.$sHashKey.'</div>
		<div class="twcm_event">'.$aParamsToTwenga['event'].'</div>
		<div class="twcm_user_id">'.$aParamsToTwenga['user_id'].'</div>
		<div class="twcm_user_global_id">'.$aParamsToTwenga['user_global_id'].'</div>
		<div class="twcm_user_firstname">'.$aParamsToTwenga['user_firstname'].'</div>
		<div class="twcm_user_city">'.$aParamsToTwenga['user_city'].'</div>
		<div class="twcm_user_state">'.$aParamsToTwenga['user_state'].'</div>
		<div class="twcm_user_country">'.$aParamsToTwenga['user_country'].'</div>
		<div class="twcm_user_segment">'.$aParamsToTwenga['user_segment'].'</div>
		<div class="twcm_user_is_customer">'.$aParamsToTwenga['user_is_customer'].'</div>
		<div class="twcm_ecommerce_platform">'.$aParamsToTwenga['ecommerce_platform'].'</div>
		<div class="twcm_tag_platform">'.$aParamsToTwenga['tag_platform'].'</div>
		<div class="twcm_basket_id">'.$aParamsToTwenga['basket_id'].'</div>
		<div class="twcm_order_id">'.$aParamsToTwenga['order_id'].'</div>
		<div class="twcm_order_currency">'.$aParamsToTwenga['order_currency'].'</div>
		<div class="twcm_order_amount_et">'.$aParamsToTwenga['order_amount_et'].'</div>
		<div class="twcm_order_amount_tax">'.$aParamsToTwenga['order_amount_tax'].'</div>
		<div class="twcm_order_amount_ati">'.$aParamsToTwenga['order_amount_ati'].'</div>
		<div class="twcm_order_amount_shipping">'.$aParamsToTwenga['order_amount_shipping'].'</div>
		<div class="twcm_order_tax_rate">'.$aParamsToTwenga['order_tax_rate'].'</div>
		';
		
		if(is_array($aParamsToTwenga['items']) && !empty($aParamsToTwenga['items'])){
			foreach($aParamsToTwenga['items'] as $aInfoItem){
				$sTrackingScript .= '
				<div class="twcm_item">
				<div class="twcm_ref_id">'.((isset($aInfoItem['ref_id'])) ? $aInfoItem['ref_id'] : '').'</div>
				<div class="twcm_item_id">'.((isset($aInfoItem['item_id'])) ? $aInfoItem['item_id'] : '').'</div>
				<div class="twcm_item_name">'.((isset($aInfoItem['name'])) ? $aInfoItem['name'] : '').'</div>
				<div class="twcm_item_price_et">'.((isset($aInfoItem['price'])) ? $aInfoItem['price'] : '').'</div>
				<div class="twcm_item_quantity">'.((isset($aInfoItem['quantity'])) ? $aInfoItem['quantity'] : '').'</div>
				</div>
				';		
			}
		}

		$sTrackingScript .= '</div>';

		return str_replace("\t", "", $sTrackingScript);
	}
}