<?php
class BorderJump_BorderShip_System_ConfigController extends Mage_Adminhtml_Controller_Action
{
    //~ public function exportTableratesAction() {
        //~ $fileName   = 'bordership_tablerates.csv';
        //~ /** @var $gridBlock Mage_Adminhtml_Block_Shipping_Carrier_Tablerate_Grid */
        //~ $gridBlock  = $this->getLayout()->createBlock('bordership/adminhtml_shipping_carrier_tablerate_grid');
        //~ $website    = Mage::app()->getWebsite($this->getRequest()->getParam('website'));
        //~ if ($this->getRequest()->getParam('conditionName')) {
            //~ $conditionName = $this->getRequest()->getParam('conditionName');
        //~ } else {
            //~ $conditionName = $website->getConfig('carriers/tablerate/condition_name');
        //~ }
        //~ $gridBlock->setWebsiteId($website->getId())->setConditionName($conditionName);
        //~ $content    = $gridBlock->getCsvFile();
        //~ $this->_prepareDownloadResponse($fileName, $content);
    //~ }
    
    public function exportTableratesAction()
    {
        $websiteModel = Mage::app()->getWebsite($this->getRequest()->getParam('website'));
        
        $tableratesCollection = Mage::getResourceModel('bordership/carrier_tablerate_collection');
        $tableratesCollection->setWebsiteFilter($websiteModel->getId());
        $tableratesCollection->load();
        
        $csv = '';
        
        $csvHeader = array('"'.Mage::helper('adminhtml')->__('Weight').'"','"'.Mage::helper('adminhtml')->__('Shipping Price').'"');
        $csv .= implode(',', $csvHeader)."\n";
        
        foreach ($tableratesCollection->getItems() as $item) {
            if ($item->getData('weight') == '') {
                $weight = '*';
            } else {
                $weight = $item->getData('weight');
            }

            $csvData = array($weight, $item->getData('price'));
            foreach ($csvData as $cell) {
                $cell = '"'.str_replace('"', '""', $cell).'"';
            }
            $csv .= implode(',', $csvData)."\n";
        }
        
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        header("Content-type: application/octet-stream");
        header("Content-disposition: attachment; filename=bordership_tablerates.csv");
        echo $csv;
        exit;
    }
}