<?php 
	class Gorilla_Commercebug_Model_Collectorcollections extends Gorilla_Commercebug_Model_Observingcollector
	{
		protected $_collections=array();
		public function collectInformation($observer)
		{
			$collector = $this->getCollector();
			$this->_collections[] = $observer->getEvent()->getCollection();			
		}
		
		public function addToObjectForJsonRender($json)
		{
			$json->collections						= array();
			$json->collectionFiles					= array();
			$json->collectionModels					= array();
			foreach($this->_collections as $model)
			{
				$class = get_class($model);
				if(!array_key_exists($class,$json->collections))
				{
					$json->collections[$class] = 0;
				}
				$json->collections[$class]++;
				$json->collectionFiles[$class] = $this->getClassFile($class);
				$json->collectionModels[$class] = $model->getModelName();
			}					
			return $json;
		}
		
		public function createKeyName()
		{
			return 'collections';
		}
	}
