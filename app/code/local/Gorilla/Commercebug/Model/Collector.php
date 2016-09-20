<?php
	class Gorilla_Commercebug_Model_Collector extends Mage_Core_Helper_Abstract
	{
		protected $controller;
		protected $layout;
		protected $request;
		
		protected $models = array();
		protected $blocks = array();

		protected $_singleCollectors=array();		
		public function registerSingleCollector(Gorilla_Commercebug_Model_Observingcollector $object)
		{
			if(!in_array($object, $this->_singleCollectors))
			{
				$this->_singleCollectors[] = $object;
			}
			return $this;
		}		
				
		//renders as json.
		public function asJson()
		{
			$json = new stdClass();
			
			foreach($this->_singleCollectors as $single_collector)
			{
				$json = $single_collector->addToObjectForJsonRender($json);	
			}
			
			$json = json_encode($json); 
			
			Mage::helper('commercebug/log')->log(
			__CLASS__ . 'Serialized:' . $json
			);
						
			return $json;			
		}
		
		private function getClassFile($className)
		{
			$r = new ReflectionClass($className);
			return $r->getFileName();		
		}
	}