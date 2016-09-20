<?php
	class Gorilla_Commercebug_Helper_Cacheclearer
	{
		public function clearCache()
		{			
			Mage::app()->cleanCache();
		}
	}