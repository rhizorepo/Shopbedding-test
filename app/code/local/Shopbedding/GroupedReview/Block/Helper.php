<?php
class Shopbedding_GroupedReview_Block_Helper extends Mage_Review_Block_Helper {

    protected $_raiting;

    protected $_availableTemplates = array(
        'default' => 'review/helper/summary.phtml',
        'short'   => 'review/helper/summary_short.phtml'
    );



    public function getRatingSummary()
    {
        if($this->getProduct()->getTypeId() === 'grouped') {
            $_associatedProducts = $this->getProduct()->getTypeInstance(true)->getAssociatedProducts($this->getProduct());
            $reviewCount = 0;
            foreach ($_associatedProducts as $as) {
              //echo $as->getRatingSummary()->getRatingSummary();
            }
            return $this->_raiting;
        }
        return parent::getRatingSummary();
    }

    public function getReviewsCount()
    {
        if($this->getProduct()->getTypeId() === 'grouped') {
            $_associatedProducts = $this->getProduct()->getTypeInstance(true)->getAssociatedProducts($this->getProduct());
            $reviewCount = 0; $avgSum =0; $avgCount = 0;
            foreach ($_associatedProducts as $as) {
                $_items2 = Mage::getModel('review/review')->getCollection()
                    ->addStoreFilter(Mage::app()->getStore()->getId())
                    ->addStatusFilter('approved')
                    ->addEntityFilter('product', $as->getId())
                    ->setDateOrder()
                    ->addRateVotes();

                $reviewCount += $_items2->count();
                /**
                 * Getting average of ratings/reviews
                 */

                $ratings = array();
                if ($_items2->count() > 0) {
                    foreach ($_items2->getItems() as $review) {
                        if($review->getRatingVotes())
                        foreach( $review->getRatingVotes() as $vote ) {
                            $ratings[] = $vote->getPercent();
                        }
                    }
                    $avgSum += array_sum($ratings);
                    $avgCount +=count($ratings);
            }
            }
            if($avgCount!=0){
            $this->_raiting = $avgSum/$avgCount;
            } else $this->_raiting = 0;
            return $reviewCount;
        }
            return parent::getReviewsCount();//$this->getProduct()->getRatingSummary()->getReviewsCount();
    }

    public function getReviewsUrl()
    {
        return Mage::getUrl('review/product/list', array(
            'id'        => $this->getProduct()->getId(),
            'category'  => $this->getProduct()->getCategoryId()
        ));
    }

    /**
     * Add an available template by type
     *
     * It should be called before getSummaryHtml()
     *
     * @param string $type
     * @param string $template
     */
    public function addTemplate($type, $template)
    {
        $this->_availableTemplates[$type] = $template;
    }
}