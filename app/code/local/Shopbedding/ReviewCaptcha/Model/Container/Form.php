<?php
/**
 * @author David Joly <djoly@gorillachicago.com>
 */
class Shopbedding_ReviewCaptcha_Model_Container_Form extends Enterprise_PageCache_Model_Container_Abstract
{
    /**
     * Get cache identifier
     */
    protected function _getCacheId()
    {
        /**
         * Recreating placeholder definition to create definition hash.
         * This must match the following format:
         *
         * BUSINESSMODEL_COVERAGE container="Mmm_BusinessModel_Model_Container_Coverage" block="Mmm_BusinessModel_Block_Coverage" cache_id="b7df6c78d78b44329430edccc88af9caab4134fc" template="businessmodel/coverage.phtml"
         */
        $cacheId    = $this->_placeholder->getAttribute('cache_id');
        $container  = $this->_placeholder->getAttribute('container');
        $block      = $this->_placeholder->getAttribute('block');
        $template   = $this->_placeholder->getAttribute('template');
 
        if ($cacheId) {
 
            $id = 'SHOPBEDDING_REVIEWCAPTCHA_FORM'.
                md5(
                    'SHOPBEDDING_REVIEWCAPTCHA_FORM container="'.$container.'" ' .
                    'block="'.$block.'" cache_id="'.$cacheId.'"' .
                    'template="'.$template
                    );
            return $id;
        }
        return false;
    }
 
    /**
     * Save data to cache storage
     *
     * @param string $data
     * @param string $id
     * @param array $tags
     */
    protected function  _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return $this; //Not Saving block output;
    }
 
    /**
     * Render block content
     *
     * @return string
     */
    protected function _renderBlock()
    {
        $productId = $this->_placeholder->getAttribute('productId');
        Mage::app()->getRequest()->setParam('id', $productId);

        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');
        /** @var Gorilla_Fpcache_Block_Header $block */
        $block = new $block;
        $block->setTemplate($template);
        return $block->toHtml();
    }
 
}

