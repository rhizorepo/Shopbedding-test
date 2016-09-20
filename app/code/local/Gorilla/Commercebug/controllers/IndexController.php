<?php
	class Gorilla_Commercebug_IndexController extends Mage_Core_Controller_Front_Action
	{
		public function indexAction()
		{
			var_dump(
				Mage::getModel('catalog/product')
				->getCollection()
				->addAttributeToSelect('*')
				->getFirstItem()
				->getData()
			);
		}
	}