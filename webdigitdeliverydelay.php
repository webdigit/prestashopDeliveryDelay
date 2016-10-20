<?php
if (!defined('_PS_VERSION_'))
	exit();
	
class WebdigitDeliveryDelay extends Module {
	
	public function __construct(){
		$this->name = 'webdigitdeliverydelay';
		$this->tab = 'front_office_features';
		$this->version = '1.0.0';
		$this->author = 'WEBDIGIT sprl';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array(
				'min' => '1.6',
				'max' => _PS_VERSION_
		);
		$this->bootstrap = true;
		
		parent::__construct();
		
		$this->displayName = $this->l('Delivery Delay');
		$this->description = $this->l('Display delivery delay on product');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall ?');
		
		if(!Configuration::get('WEBDIGIT_DELIVERY_DELAY'))
			$this->warning = $this->l('No name provided');
	}
	
	public function install(){
		
		if(Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);
		
		if(!parent::install() ||
				!$this->alterTable('add') ||
				//!$this->registerHook('actionAdminControllerSetMedia') ||
				!$this->registerHook('displayHeader') ||
				!$this->registerHook('actionProductUpdate') ||
				!$this->registerHook('displayAdminProductsExtra') ||
				!Configuration::updateValue('MYMODULE_NAME','WEBDIGIT_STORE_ONLY'))
			return false;	
		return true;
	}
	
	public function uninstall(){
		
		return parent::uninstall() && $this->alterTable('remove') && Configuration::deleteByName('WEBDIGIT_STORE_ONLY');
	}
	
	public function alterTable($method){
		
		switch($method){
			case 'add':
				$sql = 'ALTER TABLE'. _DB_PREFIX_ .'product ADD `delivery_delay` TINYINT(1) unsigned NOT NULL DEFAULT \'0\'';
				break;
			case 'remove':
				$sql = 'ALTER TABLE'. _DB_PREFIX_ .'product DROP COLUMN `delivery_delay`';
				break;
		}
		
		if(!Db::getInstance()->Execute($sql))
			return false;
		return true;
	}
	
	public function hookDisplayHeader($params){
		
		$allowed_controllers = array(
				'index',
				'product',
				'category'
		);
		
		$_controller = $this->context->controller;
		
		if(isset($_controller->php_self) && in_array($_controller->php_self, $allowed_controllers)){
			$this->controller->addCss($this->_path . 'views/css/wddeliverydelay.css','all');
			$this->controller->addJs($this->_path . 'views/js/wddeliverydelay.js','all');
		}
		
		$sql = 'SELECT id_product, delivery_delay FROM ". _DB_PREFIX_ ."product WHERE active = "1";';
		
		if($result = Db::getInstance()->ExecuteS($sql)){
			$jsReturn = '';
			$jsReturn .= '<script type="text/javascript">';
			$jsReturn .= 'var deliveryDelay = [];';
			
			foreach($result as $row){
				$jsReturn .= 'deliveryDelay["'.$row['id_product'].'"]="'.$row['delivery_delay'].'";';
			}
			
			$jsReturn .= '</script>';
			return $jsReturn;
		}
	}
	
	public function hookDisplayAdminProductsExtra($params){
		
		if(Tools::isSubmit('submitDeliveryDelay')){
			$value = Tools::getValue('deliveryDelay');
			$id_product = Tools::getValue('id_product');
			
			if(!$value){
				$value = 0;
			}
			Db::getInstance()->update('product', array('delivery_delay'=>$value), 'id_product = '.$id_product);
			
		}else{
			$id_product = $_GET['id_product'];
			$sql = 'SELECT delivery_delay FROM'. _DB_PREFIX_ .'product WHERE id_product ='.$id_product;
			if(!$result = Db::getInstance()->getRow($sql)){
				die('Erreur etc.');
			}else{
				$value = $result['delivery_delay'];
			}
		}
		
		$checked = '';
		if($value || $value == 1){
			$checked = 'checked="checked"';
		}
		
		$this->smarty->assign(array(
			'checked' => $checked	
		));
		
		return $this->display(__FILE__,'wddeliverydelay.tpl');
	}
}
?>