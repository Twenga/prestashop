<?php
/**
 * build the feed required by Twenga.
 */
class BuildCatalog
{
	private $_aConfiguration = array();
	private $_sVersionGenerated;
	private $_aProductDone = array();
	
    public function __construct()
    {
		$this->_aConfiguration = Configuration::getMultiple(
			array(
				'PS_TAX_ADDRESS_TYPE', 
				'PS_CARRIER_DEFAULT',
				'PS_COUNTRY_DEFAULT',
				'PS_LANG_DEFAULT', 
				'PS_SHIPPING_FREE_PRICE', 
				'PS_SHIPPING_HANDLING', 
				'PS_SHIPPING_METHOD', 
				'PS_SHIPPING_FREE_WEIGHT',
				'SHOPPING_FLUX_IMAGE',
				'SHOPPING_FLUX_CARRIER'
			)
		);
    }

    /**
     * Get version catalog
     */
    public function getVersionGenerated()
	{
		return $this->_sVersionGenerated;
	}
    
	/**
	 * build the feed xml.
	 */
	public function _buildXML()
	{
		$bNoBreadcrumb = Tools::getValue('no_breadcrumb');
		$iDLang = Tools::getValue('lang');
		$this->_aConfiguration['PS_LANG_DEFAULT'] = !empty($iDLang) ? Language::getIdByIso($iDLang) : $this->_aConfiguration['PS_LANG_DEFAULT'];
		
		$oCarrier = Carrier::getCarrierByReference((int)$this->_aConfiguration['SHOPPING_FLUX_CARRIER']);
		$oCarrier = is_object($oCarrier) ? $oCarrier : new Carrier((int)$this->_aConfiguration['SHOPPING_FLUX_CARRIER']);
		
		$aProducts = $this->_getSimpleProducts($this->_aConfiguration['PS_LANG_DEFAULT']);
		$this->_sVersionGenerated = md5($aProducts);
		
		$oLink = new Link();

		echo '<?xml version="1.0" encoding="utf-8"?>';
		echo '<catalog country="'.Context::getContext()->country->iso_code.'">';
		if($aProducts && !empty($aProducts)){
			foreach ($aProducts as $aProduct)
			{
				if(isset($this->_aProductDone[$aProduct['merchant_id']])) continue;
				echo '<product>';
				echo $this->_getBaseData($aProduct, $oLink, $oCarrier, $bNoBreadcrumb);
				echo '</product>';
				$this->_aProductDone[$aProduct['merchant_id']] = true;
				ob_flush();
				flush();
			}
		}
		echo '</catalog>';
	}
	
	/**
	 * Default data
	 */
	private function _getBaseData($aProduct, $oLink, $oCarrier, $bNoBreadcrumb)
	{
		$sStructXml = '';
		$sStructXml .= '<merchant_id><![CDATA['.htmlentities($aProduct['merchant_id'], ENT_QUOTES, 'UTF-8').']]></merchant_id>';
		$sStructXml .= '<merchant_ref><![CDATA['.htmlentities($aProduct['merchant_ref'], ENT_QUOTES, 'UTF-8').']]></merchant_ref>';
		$sStructXml .= '<upc_ean><![CDATA['.htmlentities($aProduct['upc_ean'], ENT_QUOTES, 'UTF-8').']]></upc_ean>';
		$sStructXml .= '<manufacturer_id><![CDATA['.htmlentities($aProduct['manufacturer_id'], ENT_QUOTES, 'UTF-8').']]></manufacturer_id>';
		$sProductUrl = $oLink->getProductLink($aProduct['id_product']);
		if(!empty($aProduct['id_product'])) $sProductUrl .= '#/'.str_replace(array(': ',', '),array('-','/'),strtolower($aProduct['product_attribute']));
		$sStructXml .= '<product_url><![CDATA['.htmlentities($sProductUrl, ENT_QUOTES, 'UTF-8').']]></product_url>';
		$iImageId = (!empty($aProduct['id_attr_image']))? $aProduct['id_attr_image'] : $aProduct['id_image'];
		$sStructXml .= '<image_url><![CDATA['.htmlentities('http://'.$oLink->getImageLink($aProduct['link_rewrite'], $aProduct['id_product'].'-'.$iImageId, $this->_aConfiguration['SHOPPING_FLUX_IMAGE']), ENT_QUOTES, 'UTF-8').']]></image_url>';
		$sStructXml .= '<price><![CDATA['.htmlentities($aProduct['price_ati'], ENT_QUOTES, 'UTF-8').']]></price>';
		$sStructXml .= '<price_et><![CDATA['.htmlentities($aProduct['price_et'], ENT_QUOTES, 'UTF-8').']]></price_et>';
		$sStructXml .= '<shipping_cost><![CDATA['.htmlentities($aProduct['additional_shipping_cost'], ENT_QUOTES, 'UTF-8').']]></shipping_cost>';
		$sStructXml .= '<designation><![CDATA['.htmlentities($aProduct['designation'], ENT_QUOTES, 'UTF-8').']]></designation>';
		$sStructXml .= '<description><![CDATA['.htmlentities($aProduct['description'], ENT_QUOTES, 'UTF-8').']]></description>';
		$sStructXml .= '<category><![CDATA['.htmlentities(empty($bNoBreadcrumb) ? $this->_buildFilAriane($aProduct['id_product'], $this->_aConfiguration['PS_LANG_DEFAULT']) : $aProduct['cat_name'], ENT_QUOTES, 'UTF-8').']]></category>';
		$sStructXml .= '<brand><![CDATA['.htmlentities($aProduct['brand'], ENT_QUOTES, 'UTF-8').']]></brand>';
		$sStructXml .= '<in_stock><![CDATA['.htmlentities($aProduct['quantity'] > 0 ? 'Y' : 'N', ENT_QUOTES, 'UTF-8').']]></in_stock>';
		$sStructXml .= '<stock_detail><![CDATA['.htmlentities($aProduct['quantity'], ENT_QUOTES, 'UTF-8').']]></stock_detail>';
		$sStructXml .= '<item_display><![CDATA['.htmlentities($aProduct['visibility'] == 'none' ? 0 : 1, ENT_QUOTES, 'UTF-8').']]></item_display>';
		$sStructXml .= '<condition><![CDATA['.htmlentities($aProduct['condition'] == 'new' ? 0 : 1, ENT_QUOTES, 'UTF-8').']]></condition>';
		$sStructXml .= '<shipping_delay><![CDATA['.htmlentities($oCarrier->delay[$this->_aConfiguration['PS_LANG_DEFAULT']], ENT_QUOTES, 'UTF-8').']]></shipping_delay>';
		$sStructXml .= '<ecotax><![CDATA['.htmlentities($aProduct['ecotax'], ENT_QUOTES, 'UTF-8').']]></ecotax>';
		$sStructXml .= '<vat><![CDATA['.htmlentities($this->_getTaxRate($aProduct['id_tax_rules_group']), ENT_QUOTES, 'UTF-8').']]></vat>';
		$sStructXml .= '<unit_price><![CDATA['.htmlentities($aProduct['unit_price'], ENT_QUOTES, 'UTF-8').']]></unit_price>';
		$sStructXml .= '<merchant_margin><![CDATA['.htmlentities($aProduct['wholesale_price_attr'] ? (float)($aProduct['price_et']-$aProduct['wholesale_price_attr']) : (float)($aProduct['price_et']-$aProduct['wholesale_price']), ENT_QUOTES, 'UTF-8').']]></merchant_margin>';
		return $sStructXml;
	}	
	
	/**
	 * Get TAX RATE
	 */
	private function _getTaxRate($iIdTaxRulesGroup)
	{	
		$oAddressInit = Address::initialize();
		$oTaxManager = TaxManagerFactory::getManager($oAddressInit, $iIdTaxRulesGroup);
		$oTaxCalculator = $oTaxManager->getTaxCalculator();
		return $oTaxCalculator->getTotalRate();	
	}
	
	/**
	 * Get Product
	 */
	private function _getSimpleProducts($iDLang)
	{
		$sql = '
			SELECT
			p.id_product as id_product,
			p.id_product as merchant_ref,
			IF(	pa.id_product_attribute,
			   	CONCAT(p.id_product,"D",pa.id_product_attribute),
			   	p.id_product ) as merchant_id,
			IF(pa.ean13,pa.ean13,
				IF(pa.upc,pa.upc,
					IF(p.ean13,p.ean13,
						IF(p.upc,p.upc,""
			)))) AS upc_ean,
			IF(pa.supplier_reference!="",pa.supplier_reference,s.name) as manufacturer_id,
			IF(pa.price,p.price+pa.price,p.price) AS price_et, 
			IF(pa.price,
			(p.price+pa.price)+(((p.price+pa.price)*tax.rate)/100), 
			(p.price)+(((p.price)*tax.rate)/100))
			AS price_ati,
			p.additional_shipping_cost, 
			pl.NAME as designation,
			CONCAT(pl.description_short," ",attr.product_attribute) as description,
			attr.product_attribute,
			catlang.name AS category,
			m.name as brand,
			IF(stockattr.quantity>0,stockattr.quantity,stock.quantity) AS quantity,
			p.visibility,
			p.condition,
			p.ecotax,
			pl.link_rewrite,
			ishop.id_image,
			ai.id_image as id_attr_image,
			catlang.name AS cat_name,
			IF(pa.unit_price_impact,
				IF(pa.price,
				(((p.price+pa.price)+(((p.price+pa.price)*tax.rate)/100))/p.unit_price_ratio)+pa.unit_price_impact, 
				(((p.price)+(((p.price)*tax.rate)/100))/p.unit_price_ratio)+pa.unit_price_impact
				),
				IF(pa.price,
				((p.price+pa.price)+(((p.price+pa.price)*tax.rate)/100))/p.unit_price_ratio, 
				((p.price)+(((p.price)*tax.rate)/100))/p.unit_price_ratio
				)
			) AS unit_price,
			p.unit_price_ratio,	
			pa.wholesale_price as wholesale_price_attr,
			p.wholesale_price
			FROM '._DB_PREFIX_.'product p
			INNER JOIN '._DB_PREFIX_.'product_shop product_shop ON (product_shop.id_product = p.id_product AND product_shop.id_shop = 1)
			INNER JOIN '._DB_PREFIX_.'stock_available stock ON (stock.id_product = p.id_product AND stock.id_product_attribute = 0)
			LEFT JOIN (SELECT rate, id_tax_rules_group FROM '._DB_PREFIX_.'tax INNER JOIN '._DB_PREFIX_.'tax_rule USING(id_tax) LIMIT 1) tax ON tax.id_tax_rules_group = p.id_tax_rules_group
			LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (p.id_product = pl.id_product AND pl.id_shop = 1 ) 
			LEFT JOIN '._DB_PREFIX_.'image i ON i.id_product = p.id_product AND i.cover = 1 
			LEFT JOIN '._DB_PREFIX_.'image_shop ishop ON ishop.id_image = i.id_image 
			LEFT JOIN '._DB_PREFIX_.'category_lang catlang ON (catlang.id_category = p.id_category_default)
			LEFT JOIN '._DB_PREFIX_.'supplier s ON (s.id_supplier = p.id_supplier)  
			LEFT JOIN '._DB_PREFIX_.'manufacturer m ON (m.id_manufacturer = p.id_manufacturer) 
			LEFT JOIN '._DB_PREFIX_.'product_attribute pa ON (pa.id_product = p.id_product) 
			LEFT JOIN '._DB_PREFIX_.'product_attribute_image ai ON (ai.id_product_attribute = pa.id_product_attribute) 
			LEFT JOIN (
			SELECT 
			pac.id_product_attribute, 
			GROUP_CONCAT(CONCAT(agl.name,": ",al.name) SEPARATOR ", ") AS product_attribute
			FROM '._DB_PREFIX_.'product_attribute_combination pac 
			INNER JOIN '._DB_PREFIX_.'attribute a ON a.id_attribute = pac.id_attribute
			INNER JOIN '._DB_PREFIX_.'attribute_lang al ON al.id_attribute = a.id_attribute
			INNER JOIN '._DB_PREFIX_.'attribute_group ag ON ag.id_attribute_group = a.id_attribute_group
			INNER JOIN '._DB_PREFIX_.'attribute_group_lang agl ON agl.id_attribute_group = ag.id_attribute_group 
			GROUP BY pac.id_product_attribute
			) attr ON attr.id_product_attribute = pa.id_product_attribute
			LEFT JOIN '._DB_PREFIX_.'stock_available stockattr ON stockattr.id_product_attribute = pa.id_product_attribute
			WHERE pl.id_lang = 1 
			AND p.active = 1 
			AND p.available_for_order = 1 
			AND product_shop.visibility IN ("none", "both", "catalog", "search") 
			GROUP BY p.id_product, pa.id_product_attribute
			ORDER BY pl.name				
		';
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
	}
	
	/**
	 * Build Fil Ariane
	 */
	private function _buildFilAriane($iProductId, $iIdLang)
	{
		$category = '';
		foreach ($this->_getCatFilAriane($iProductId, $iIdLang) as $categories)
		{
			$category .= $categories.' > ';
		}
		$ret = Tools::substr($category, 0, -3);
		return $ret;
	}

	/**
	 * Get Category Fil Ariane
	 */
	private function _getCatFilAriane($iIdProduct, $iIdLang)
	{
		$ret = array();
		$id_parent = '';
		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT cl.`name`, p.`id_category_default` as id_category, c.`id_parent` FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (p.`id_category_default` = cl.`id_category`)
		LEFT JOIN `'._DB_PREFIX_.'category` c ON (p.`id_category_default` = c.`id_category`)
		LEFT JOIN `'._DB_PREFIX_.'category` cp ON (cp.`id_category` = c.`id_parent`)
		WHERE p.`id_product` = '.(int)$iIdProduct.'
		AND cl.`id_lang` = '.(int)$iIdLang);
		
		foreach ($row as $val)
		{
			$ret[$val['id_category']] = $val['name'];
			$id_parent = $val['id_parent'];
			$id_category = $val['id_category'];
		}
		
		while ($id_parent != 0 && $id_category != $id_parent)
		{
			$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT cl.`name`, c.`id_category`, c.`id_parent` FROM `'._DB_PREFIX_.'category_lang` cl
			LEFT JOIN `'._DB_PREFIX_.'category` c ON (c.`id_category` = '.(int)$id_parent.')
			WHERE cl.`id_category` = '.(int)$id_parent.'
			AND cl.`id_lang` = '.(int)$iIdLang);
			foreach ($row as $val)
			{
				if($val['id_category'] != 1 && $val['id_category'] != 2){
					$ret[$val['id_category']] = $val['name'];
				}
				$id_parent = $val['id_parent'];
				$id_category = $val['id_category'];
			}
		}
	
		$ret = array_reverse($ret);
		return $ret;
	}
}