<?php

class Shopbedding_Title_Block_Html_Head extends Mage_Page_Block_Html_Head
{
    public function setTitle($title)
    {
        $page = Mage::app()->getFrontController()->getRequest()->getRouteName();
        $titleSuffix = Mage::getStoreConfig('design/head/title_suffix');

        if($page === 'cms'){
            $cmsSingletonIdentifier = Mage::getSingleton('cms/page')->getIdentifier();
            $homeIdentifier = Mage::app()->getStore()->getConfig('web/default/cms_home_page');
            if($cmsSingletonIdentifier === $homeIdentifier){
                $titleSuffix =  '';
            }
        }

        $this->_data['title'] = Mage::getStoreConfig('design/head/title_prefix') . ' ' . $title
            . ' ' . $titleSuffix;
        return $this;
    }
}
