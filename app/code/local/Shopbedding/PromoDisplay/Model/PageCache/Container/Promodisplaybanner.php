<?php

class Shopbedding_PromoDisplay_Model_PageCache_Container_Promodisplaybanner extends Enterprise_PageCache_Model_Container_Abstract
{
    protected function _getCacheId()
    {
        $cacheId    = $this->_placeholder->getAttribute('cache_id');
        $container  = $this->_placeholder->getAttribute('container');
        $block      = $this->_placeholder->getAttribute('block');
        $template   = $this->_placeholder->getAttribute('template');

        if ($cacheId) {

            $id = 'PROMODISPLAY_HOME'.
                md5(
                    'PROMODISPLAY_HOME container="'.$container.'" ' .
                    'block="'.$block.'" cache_id="'.$cacheId.'"' .
                    'template="'.$template
                );
            return $id;
        }
        return false;
    }

    protected function  _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return $this; //Not Saving block output;
    }

    protected function _renderBlock()
    {
        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');
        $name = $this->_placeholder->getAttribute('name');
        /** @var $block Mage_Core_Block_Template */
        $block = new $block;
        $block->setTemplate($template);
        $block->setNameInLayout($name);
        $block->setLayout(Mage::app()->getLayout());
        return $block->toHtml();
    }

}