<?php

class CompleteWeb_CustomFlatrate_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {

    protected $_code = 'completeweb_customflatrate';

    public function collectRates(
    Mage_Shipping_Model_Rate_Request $request
    ) {
        $result = Mage::getModel('shipping/rate_result');
        
		$cart = Mage::getModel('checkout/cart')->getQuote();
		
	//	$quote=Mage::getSingleton('checkout/session')->getQuote();
		
		
	//	$ruleIds = $quote->	();
	//	echo "ffff<pre>"; print_r($cart); exit;
		
		$rules = Mage::getResourceModel('salesrule/rule_collection')->load();

		/*foreach ($rules as $rule) {
			if ($rule->getIsActive()) { 
				echo "<pre>"; print_r($rule);
			}
		}
	exit;*/
	
		$applied_coupon_code = Mage::getSingleton('checkout/session')->getQuote()->getCouponCode(); 
		//echo "<pre>"; print_r(get_class_methods(Mage::getSingleton('checkout/session')->getQuote())); exit;
		
		$products = array();
		$categories = array();
		$quantity = 0;
		$rule = Mage::getResourceModel('salesrule/rule_collection')->load();
		$rule_data = $rule->getData();
		
		$flat_rate_shipping_module = false;
		$flat_rate_shipping_price = '';
		$flat_rate_shipping_title = '';
		foreach ($cart->getAllItems() as $item) {
			$ruleIds = $item->getAppliedRuleIds(); 
			
			$ruleIds = explode(',',$ruleIds);
			
			
			if(is_array($ruleIds)) {
				foreach($ruleIds as $ruleID) {
					
					foreach($rule_data as $r) {
						
						
						if($r['rule_id'] == $ruleID && $r['is_active'] == 1 && $r['flat_rate_amount'] != '' && (int) $r['flat_rate_amount'] > 0) {
							//echo "<pre>"; print_r($r); //exit;
							$flat_rate_shipping_module = true;
							$flat_rate_shipping_price = $r['flat_rate_amount'];
							$flat_rate_shipping_title = $r['flat_rate_title'];
							break;
						}
					}
					
				}
			}
			
			$quantity = $quantity + $item->getQty(); 
			
			$productid = $item->getProductId();
			$productsku = $item->getProduct()->getSku();
			$productName = $item->getProduct()->getName();
			$product_id = $item->getProduct()->getSku();
			$products[] = $productsku;
			//echo $item->getProduct()->getTypeId(); exit;
			if($item->getProduct()->getTypeId()=='simple') {
				$parentId = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($item->getProduct()->getId());
				
				if (isset($parentId[0])) {
					$product = Mage::getModel('catalog/product')->load($parentId[0]);
					$product_category_ids = $product->getCategoryIds();
					$categories[] = $product_category_ids;
				} else {
					$product_category_ids = $item->getProduct()->getCategoryIds();
					$categories[] = $product_category_ids;
				}
			} else {
				$product_category_ids = $item->getCategoryIds();
				$categories[] = $product_category_ids;
			}
			
			
		}
		
		$shipping_coupon = $this->getConfigData('coupon');
		if(isset($shipping_coupon) && !empty($shipping_coupon)) {
			$shipping_coupon_arr = explode(',',$shipping_coupon);
			if(isset($shipping_coupon_arr) && is_array($shipping_coupon_arr) && count($shipping_coupon_arr) > 0 ) {
				if(!in_array($applied_coupon_code,$shipping_coupon_arr)){
					return false;
				}
			}
				
		}
		$type = $this->getConfigData('type'); // exit;
		$enable_rule = $this->getConfigData('enable_rule');
		//echo $enable_rule;
		if($enable_rule == 'only_promotion' && !$flat_rate_shipping_module  ) {
			return false;
			
		}
		if($enable_rule == 'only_shipping' && $flat_rate_shipping_module) {
			$flat_rate_shipping_module = false;
			$flat_rate_shipping_price = '';
			$flat_rate_shipping_title = '';	
		}
		if($flat_rate_shipping_module) {
			
			$type = '';	
		}
		
		switch($type) {
			case 'product':
				$result->append($this->_getProductBasedShippingRate($products,$quantity));
				break;
			case 'category':
				$result->append($this->_getCategoryBasedShippingRate($categories,$quantity));
				break;
			default:
				$result->append($this->_getOrderBasedShippingRate('',$flat_rate_shipping_module,$flat_rate_shipping_price,$flat_rate_shipping_title));
				break;	
		}
		
		
        //return false;
        return $result;
    }

    protected function _getOrderBasedShippingRate($quantity = 1,$flat_rate_shipping_module = false, $flat_rate_shipping_price = '', $flat_rate_shipping_title = '') {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

        $rate->setCarrier($this->_code);
        /**
         * getConfigData(config_key) returns the configuration value for the
         * carriers/[carrier_code]/[config_key]
         */
		 //echo $this->getConfigData('title'); exit;
        $rate->setCarrierTitle($this->getConfigData('title'));

        $rate->setMethod('standard');
        if($flat_rate_shipping_title == '') {
			$flat_rate_shipping_title = $this->getConfigData('title');
		}
		//echo $flat_rate_shipping_title; exit;
		$rate->setMethodTitle($flat_rate_shipping_title);
		
		$handling_type = $this->getConfigData('handling_type');
		$handling_fee = $this->getConfigData('handling_fee');
//		echo $flat_rate_shipping_module . "<HR>" . $flat_rate_shipping_price; exit;
		if($flat_rate_shipping_module && $flat_rate_shipping_price !='') {
			$price = $flat_rate_shipping_price;
		} else {
			$price = $this->getConfigData('price');
		}
		
		
		$price = $this->_calculateHandlingFee($price,$handling_type , $handling_fee);
		
		//$price = $quantity * $price; // CHM ATIQ

        $rate->setPrice($price);
        $rate->setCost(0);
		//echo "<pre>"; print_r($rate);
        return $rate;
    }
	
	protected function _getProductBasedShippingRate($products,$quantity) {
        
		$allowed_products = $this->getConfigData('product_sku');
		$allowed_products = str_replace(' ','',$allowed_products);
		$allowed_products_arr = explode(',',$allowed_products);
		//echo "<pre>"; print_r($allowed_products_arr); exit;
		foreach($allowed_products_arr as $allowed_sku) {
			//echo $allowed_sku . "<HR>";
			$productObject = Mage::getResourceModel('catalog/product_collection')
                  ->addAttributeToSelect('*')
                  ->addAttributeToFilter('sku', array('eq' => $allowed_sku))
                  ->load();
			$product_obj_data = $productObject->getData(); //exit;
			//echo "<pre>"; print_r($product_obj_data); //exit;
			$product_id = $product_obj_data['0']['entity_id']; //exit;
			
			//echo $product_obj_data['0']['type_id'] exit;
			if($product_obj_data['0']['type_id']=='simple') {
				
				$parentId = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($product_id);
				
				if (isset($parentId[0])) {
					$product = Mage::getModel('catalog/product')->load($parentId[0]);
					if(!in_array($product->getSku(),$allowed_products_arr)) {
						$allowed_products_arr[] = $product->getSku();
					}
				} 
				
				$productOBJ = Mage::getModel('catalog/product')->load($parentId[0]);
				$childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$productOBJ);

				foreach($childProducts as $child) {
					if(!in_array($child->getSku(),$allowed_products_arr)) {
						$allowed_products_arr[] = $child->getSku();
					}
					
					//echo $child->getSku();  // You can use any of the magic get functions on this object to get the value
				}
				
			} else {
				//$childIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($product_id);
				//echo "<pre>"; print_r($childIds); exit;
				
				$productOBJ = Mage::getModel('catalog/product')->load($product_id);
				$childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$productOBJ);

				foreach($childProducts as $child) {
					if(!in_array($child->getSku(),$allowed_products_arr)) {
						$allowed_products_arr[] = $child->getSku();
					}
					//echo $child->getSku();  // You can use any of the magic get functions on this object to get the value
				}
				//exit;
			}
		}
		
		
		
		if(isset($products) && is_array($products) && count($products) > 0) {
			$condition_check = false;
			foreach($products as $pid) {
				if(in_array($pid,$allowed_products_arr)) {
					//echo "fahad";
					$condition_check = true;
				} else {
					$condition_check = false;
					break;
				}
			}
			
			if($condition_check) {
				return $this->_getOrderBasedShippingRate($quantity);	
			} else {
				return '';	
			}
		} else {
			return '';
		}
    }
	
	protected function _getCategoryBasedShippingRate($categories,$quantity) {
       // echo "<pre>"; print_r($categories); 
		$allowed_categories = $this->getConfigData('categories');
		//echo $allowed_categories;
		//exit;
		

		$allowed_categories_arr = explode(',',$allowed_categories);
		//echo "<pre>"; print_r($allowed_categories_arr);
		
		if(isset($categories) && is_array($categories) && count($categories) > 0) {
			
			$condition_check = false;
			foreach($categories as $cids) {
				if(isset($cids) && is_array($cids) && count($cids) > 0) {
					//echo "<pre>"; print_r($cids); exit;
					foreach($cids as $cid) {
						if(in_array($cid,$allowed_categories_arr)) {
							$condition_check = true;
							break;
						}						
					}
					if(!$condition_check){
						break;
					}
				}
				
			}
			//echo $condition_check; exit;
			if($condition_check) {
				return $this->_getOrderBasedShippingRate($quantity);	
			} else {
				return '';	
			}
		} else {
			return '';
		}
    }
	
	
	
	protected function _calculateHandlingFee($cost,$handling_type , $handling_fee) {
		
		if($handling_type == 'F') {
			return $cost + $handling_fee;
		} else {
			return $cost + ($cost * $handling_fee / 100);
		}
	}

 
    public function getAllowedMethods() {
        return array(
            'standard' => 'standard',
        );
    }

}
