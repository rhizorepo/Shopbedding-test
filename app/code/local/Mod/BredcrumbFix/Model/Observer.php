<?php

class Mod_BredcrumbFix_Model_Observer
{

    public function checkCrumbs($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if( $block instanceof Mage_Page_Block_Html_Breadcrumbs){

            if (!is_array($block->_crumbs)) {
                $block->addCrumb('home', array(
                    'label'=>Mage::helper('catalog')->__('Home'),
                    'title'=>Mage::helper('catalog')->__('Go to Home Page'),
                    'link'=>Mage::getBaseUrl()
                ));

                $path  = Mage::helper('catalog')->getBreadcrumbPath();

                foreach ($path as $name => $breadcrumb) {
                    $block->addCrumb($name, $breadcrumb);
                }
            }
        }
    }
}
