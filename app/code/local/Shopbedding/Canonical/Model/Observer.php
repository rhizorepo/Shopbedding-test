<?php

class Shopbedding_Canonical_Model_Observer
{
    public function checkCanonical($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if(Mage::helper('shop_canonical')->isEnabled() && $block instanceof Mage_Page_Block_Html_Head){

            foreach($block->getItems() as $item){

                if($item['type'] === 'link_rel' && $item['params'] === 'rel="canonical"'){

                    $parts = parse_url($item['name']);
                    $rule = Mage::getModel('shop_canonical/rule')->getCollection()->addFieldToFilter('source', array('eq' => trim($parts['path'], '/')))->getFirstItem();

                    if($rule->getId()){
                        $parts['path'] = '/'.$rule->getTarget();
                        $block->removeItem($item['type'], $item['name']);
                        $item['name'] = $this->_buildUrl($parts);
                        $block->addItem($item['type'], $item['name'], $item['params'], $item['if'], $item['cond']);
                    }
                }
            }
        }
    }

    protected function _buildUrl($parsed_url) {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}