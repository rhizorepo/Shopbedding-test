<?php
/**
 * Created by PhpStorm.
 * User: arybitskiy
 * Date: 7/23/15
 * Time: 2:31 PM
 */ 
class Shopbedding_Reviews_Block_Review_Product_View_List extends Mage_Review_Block_Product_View_List {

    protected function _beforeToHtml()
    {
        $this->getReviewsCollection()
            ->join(
                array('rating'=>'rating/rating_option_vote'), 'main_table.review_id=rating.review_id',
                array('percent'=>'percent',)
            )
            ->load()
            //->addRateVotes()
        ;
        return Mage_Review_Block_Product_View::_beforeToHtml();
    }

}