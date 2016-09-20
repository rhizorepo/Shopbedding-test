<?php

class Shopbedding_ReviewText_Model_Observer
{
    public function setText($observer)
    {
        $text = $observer->getText();
        $text->setValue(Mage::helper('review')->__('Your Review Has Been Submitted. Thank you!'));
    }
}