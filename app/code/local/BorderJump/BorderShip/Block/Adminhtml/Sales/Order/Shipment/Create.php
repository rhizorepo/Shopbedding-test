<?php
class BorderJump_BorderShip_Block_Adminhtml_Sales_Order_Shipment_Create extends Mage_Adminhtml_Block_Sales_Order_Abstract {    
    public function getHubAddressHtml($order) {
        $responseBody = Mage::getModel('bordership/carrier_bordership')->apiOrderShipto($order);
        $addr = $responseBody['hubAddress'];
        if ($addr) {
            $html = array (
                $addr['company'],
                $responseBody['partnerOrderNumber'],
                $addr['street1'],
                $addr['city'] . ' ' . $addr['region'] . ' ' . $addr['postalCode'],
            );
            return '<address>' . implode('<br>', $html) . '</address>';
        }
        return false;
    }
    
    public function getOrder() {
        $order = null;
        
        if (Mage::registry('current_shipment')) {
            $order = Mage::registry('current_shipment')->getOrder();
        } elseif (Mage::registry('current_invoice')) {
            $order = Mage::registry('current_invoice')->getOrder();
        } else {
            $order = parent::getOrder();
        }
        
        return $order;
    }
    
}
