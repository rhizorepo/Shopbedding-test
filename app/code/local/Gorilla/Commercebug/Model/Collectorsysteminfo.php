<?php 
	class Gorilla_Commercebug_Model_Collectorsysteminfo extends Gorilla_Commercebug_Model_Observingcollector
	{
		protected $_items;
		public function collectInformation($observer)
		{
			$collection = $this->getCollector();
			$system_info = new stdClass();
			$system_info->ajax_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'commercebug/ajax';
			$this->_items['system_info'] = $system_info;
		}
		
		public function addToObjectForJsonRender($json)
		{
			$json->system_info = new stdClass();
			if(is_object($this->_items['system_info']))
			{
				$json->system_info = $this->_items['system_info'];
			}
			return $json;
		}
		
		public function createKeyName()
		{
			return 'systeminfo';
		}
	}