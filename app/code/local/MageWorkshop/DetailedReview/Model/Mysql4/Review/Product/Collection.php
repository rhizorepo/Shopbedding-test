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
class MageWorkshop_DetailedReview_Model_Mysql4_Review_Product_Collection extends Mage_Review_Model_Mysql4_Review_Product_Collection
{
    /**
     * @return $this
     */
    protected function _joinFields()
    {
        $resources = Mage::getSingleton('core/resource');
        $reviewTable = $resources->getTableName('review/review');
        $reviewDetailTable = $resources->getTableName('review/review_detail');
        $reviewHelpfulTable = $resources->getTableName('detailedreview/review_helpful');

        $this->addAttributeToSelect('name')
             ->addAttributeToSelect('small_image')
             ->addAttributeToSelect('sku');

        $this->getSelect()
             ->join(
                    array('rt' => $reviewTable),
                    'rt.entity_pk_value = e.entity_id',
                    array('rt.review_id', 'review_created_at' => 'rt.created_at', 'rt.entity_pk_value', 'rt.status_id')
             )
             ->join(
                    array('rdt' => $reviewDetailTable),
                    'rdt.review_id = rt.review_id',
                    array('*')
             );

        $countHelpful = clone $this->getSelect();
        $countUnHelpful = clone $this->getSelect();

        $countHelpful->reset();
        $countUnHelpful->reset();

        $countHelpful->from($reviewHelpfulTable, 'COUNT(review_id)')
                     ->where('is_helpful = 1 AND review_id = cur_rev_id');

        $countUnHelpful->from($reviewHelpfulTable, 'COUNT(review_id)')
                       ->where('is_helpful = 0 AND review_id = cur_rev_id');

        $this->getSelect()
              ->joinLeft(
                    array('rh' => $reviewHelpfulTable),
                    'rh.review_id = rt.review_id',
                    array(
                        'cur_rev_id' => 'rh.review_id',
                        'count_helpful' => "($countHelpful)",
                        'count_unhelpful' => "($countUnHelpful)",
                        'count_rh' => "(($countHelpful)-($countUnHelpful))"
                    )
              )
              ->group('rt.review_id');

        return $this;
    }

    /**
     * @inherit
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $select = clone $this->getSelect();

        $select->reset(Zend_Db_Select::ORDER)
               ->reset(Zend_Db_Select::LIMIT_COUNT)
               ->reset(Zend_Db_Select::LIMIT_OFFSET)
               ->reset(Zend_Db_Select::COLUMNS);
        $select->columns('rt.review_id');

        $countSelect = clone $this->getSelect();
        $countSelect->reset();
        $countSelect->from(array('virtual' => new Zend_Db_Expr("($select)")), 'COUNT(1)');

        return $countSelect;
    }
}
