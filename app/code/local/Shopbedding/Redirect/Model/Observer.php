<?php

class Shopbedding_Redirect_Model_Observer
{
    public function makeProductRedirect($observer)
    {
        $action = $observer->getEvent()->getControllerAction();

        if ($action->getRequest()->getModuleName() == 'catalog'
            && $action->getRequest()->getControllerName() == 'product'
            && $action->getRequest()->getActionName() == 'view') {
            $currentUrlToCompare = $action->getRequest()->getRequestString();

            $productId = (int)$action->getRequest()->getParam('id');
            if ($productId) {
                $productModel = Mage::getModel('catalog/product')->load($productId);

                if ($productModel->getId()) {
                    $productUrlToCompare = str_replace(Mage::getBaseUrl(), '', $productModel->getProductUrl());
                    if ($currentUrlToCompare != '/' . $productUrlToCompare) {
                        //$action->getResponse()->setRedirect(Mage::getBaseUrl() . $productUrlToCompare, 301);
                        header("HTTP/1.1 301 Moved Permanently");
                        header("Location: " . Mage::getBaseUrl() . $productUrlToCompare);
                        exit();
                    }
                }
            }
        }

    }
}