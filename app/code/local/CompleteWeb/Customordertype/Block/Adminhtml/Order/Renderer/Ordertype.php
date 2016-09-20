<?php 
class CompleteWeb_Customordertype_Block_Adminhtml_Order_Renderer_Ordertype
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value = $row->getData('order_type');
		if($value == 1){
			return "Phone Order";
		}else{
			return "Web Order";
		}
 
        
    }
}
?>