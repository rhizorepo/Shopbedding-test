<?php

/**
*
* Add on shopbedding/heckout/etc/config.xml
<events>
	<controller_action_predispatch>
		<observers>
			<Shopbedding_Checkout>
				<class>Shopbedding_Checkout_Model_Observer</class>
				<method>processPreDispatch</method>
			</Shopbedding_Checkout>
		</observers>
	</controller_action_predispatch>
</events>

*/


class Shopbedding_Checkout_Model_Observer
{
    public function processPreDispatch(Varien_Event_Observer $observer)
	{
		$action = $observer->getEvent()->getControllerAction();

		// Check to see if $action is a Product controller
		if ($action instanceof Mage_Checkout_CartController) {
			Mage::app()->getCacheInstance()->flush();
			Mage::app()->cleanCache();
			
			try {
				$allTypes = Mage::app()->useCache();
				foreach($allTypes as $type => $blah) {
					echo '.. | ';
					echo Mage::app()->getCacheInstance()->cleanType($type) ? "[OK]" : "[ERROR]";
					echo Mage::app()->getCacheInstance()->clean($blah["tags"]) ? "[OK]" : "[ERROR]";
					echo "\n\n";
				}
			} catch (Exception $e) {
				// do something
				error_log($e->getMessage());
			}
			
			try {
				echo "Cleaning stored cache... ";
				flush();
				echo Mage::app()->getCacheInstance()->clean() ? "[OK]" : "[ERROR]";
				echo "\n\n";
			} catch (exception $e) {
				die("[ERROR:" . $e->getMessage() . "]");
			}
			
			$cache = Mage::app()->getCacheInstance();
			// Tell Magento to 'ban' the use of FPC for this request
			$cache->banUse('full_page');
			
			echo 'Done';
		}
	}
}
