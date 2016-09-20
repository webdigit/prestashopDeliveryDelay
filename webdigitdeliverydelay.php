<?php
if (! defined ( '_PS_VERSION_' ))
	exit ();

class WebdigitDeliveryDelay extends Module {
	
	public function __construct() {
		$this->name = 'webdigitdeliverydelay';
		$this->tab = 'front_office_features';
		$this->version = '1.0.0';
		$this->author = 'WEBDIGIT sprl';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array (
				'min' => '1.6',
				'max' => _PS_VERSION_
		);
		$this->bootstrap = true;
		
		parent::__construct ();
		
		$this->displayName = $this->l ( 'Delivery Delay' );
		$this->description = $this->l ( 'Give possibility to set a simple information about delivery delay to a specific product' );
		
		$this->confirmUninstall = $this->l ( 'Are you sure you want to uninstall ?' );
		
		if (! Configuration::get ( 'WEBDIGIT_DELIVERY_DELAY' ))
			$this->warning = $this->l ( 'No name provided' );
	}
	
	public function install() {
		if (Shop::isFeatureActive ())
			Shop::setContext ( Shop::CONTEXT_ALL );
		
		if (! parent::install() || ! $this->alterTable( 'add' ) || ! $this->registerHook( 'actionAdminControllerSetMedia' ) || ! $this->registerHook( 'actionProductUpdate' ) || ! $this->registerHook( 'displayAdminProductsExtra' ) || ! Configuration::updateValue( 'MYMODULE_NAME', 'WEBDIGIT_DELIVERY_DELAY' ) )
			return false;
		
		return true;
	}
	
	public function uninstall() {
		return parent::uninstall() && $this->alterTable( 'remove' ) && Configuration::deleteByName ( 'WEBDIGIT_DELIVERY_DELAY' );
	}
	
	public function alterTable($method) {
		switch ($method) {
			case 'add':
				$sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'product ADD `delivery_delay` TEXT NOT NULL';
				break;
				
			case 'remove':
				$sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'product DROP COLUMN `delivery_delay`';
				break;
		}
		
		if (! Db::getInstance()->Execute($sql))
			return false;
		return true;
	}
	
	public function hookDisplayAdminProductsExtra($params){
		if (Validate::isLoadedObject($product = new Product((int)Tools::getValue( 'id_product' )))) {
			return $this->display(__FILE__, 'webdigitdeliverydelay.tpl');
		}
	}
	
}