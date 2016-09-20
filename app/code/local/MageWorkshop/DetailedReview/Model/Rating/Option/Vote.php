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
class MageWorkshop_DetailedReview_Model_Rating_Option_Vote extends Mage_Rating_Model_Rating_Option_Vote
{
    public function getQtyMarks($reviewsIds)
    {
        $result = array();
        if (!$reviewsIds) {
            return $result;
        }

        /** @var Mage_Rating_Model_Resource_Rating_Option_Vote $resourceModel */
        $resourceModel = $this->getResource();
        /** @var Varien_Db_Adapter_Pdo_Mysql $readConnection */
        $readConnection = $resourceModel->getReadConnection();
        $select = new Zend_Db_Select($readConnection);
        $nestedSelect = clone $select;

        $nestedSelect->from($resourceModel->getTable('rating/rating_option_vote'), array('review_id', 'value' => 'ROUND(AVG(value), 0)'))
            ->where('review_id IN (' . implode(',', $reviewsIds) . ' )')
            ->group('review_id');

        $select->from(array('rov' => new Zend_Db_Expr("($nestedSelect)")),array(
                'val'=>'rov.value',
                'amount'=>'count(rov.value)'
            ))
            ->group('val')
            ->order('val DESC');

        foreach ($readConnection->fetchAll($select) as $value) {
            $result[$value['val']] = $value['amount'];
        }
        return $result;
    }
}
