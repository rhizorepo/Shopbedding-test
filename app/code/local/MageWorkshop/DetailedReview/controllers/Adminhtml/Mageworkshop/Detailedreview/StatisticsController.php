<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */
class MageWorkshop_DetailedReview_Adminhtml_Mageworkshop_Detailedreview_StatisticsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * View statistics
     */
    public function indexAction()
    {
        $helper = Mage::helper('detailedreview');
        $this->_title($helper->__('Reviews Statistics'));

        $this->loadLayout();
        $this->_setActiveMenu('statistics');
        $this->_addBreadcrumb($helper->__('Reviews Statistics'), $helper->__('Reviews Statistics'));
        $this->renderLayout();
    }

    /**
     * Render statistics via ajax
     */
    public function ajaxBlockAction()
    {
        $output   = '';
        $blockTab = $this->getRequest()->getParam('block');
        if (in_array($blockTab, array('tab_activity'))) {
            $output = $this->getLayout()->createBlock('detailedreview/adminhtml_statistics_' . $blockTab)->toHtml();
        }
        $this->getResponse()->setBody($output);
        return;
    }

    /**
     * Render statistics via ajax
     */
    public function ajaxMostHelpfulBlockAction()
    {
        $output = $this->getLayout()->createBlock('detailedreview/adminhtml_statistics_grid_mostHelpfulReview')->toHtml();
        $this->getResponse()->setBody($output);
        return;
    }

    /**
     * Get some information from GA? Not sure that this is needed here
     */
    public function tunnelAction()
    {
        $httpClient = new Varien_Http_Client();
        $gaData = $this->getRequest()->getParam('ga');
        $gaHash = $this->getRequest()->getParam('h');
        if ($gaData && $gaHash) {
            $newHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
            if ($newHash == $gaHash) {
                $params = json_decode(base64_decode(urldecode($gaData)), true);
                if ($params) {
                    $response = $httpClient->setUri(Mage_Adminhtml_Block_Dashboard_Graph::API_URL)
                        ->setParameterGet($params)
                        ->setConfig(array('timeout' => 5))
                        ->request('GET');

                    $headers = $response->getHeaders();

                    $this->getResponse()
                        ->setHeader('Content-type', $headers['Content-type'])
                        ->setBody($response->getBody());
                }
            }
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/reviews/statistics');
    }
}
