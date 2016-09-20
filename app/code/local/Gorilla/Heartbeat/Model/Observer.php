<?php

class Gorilla_Heartbeat_Model_Observer
{
    protected $_warnings;

    public function processMailQueue()
    {
        $schedules = Mage::getModel('heartbeat/system_config_source_schedule')->toArray();
        $config = Mage::getStoreConfig('heartbeat/general/email_schedule');
        $hours = $schedules[$config];
        $receiver = Mage::getStoreConfig('heartbeat/general/email');
        if (empty($receiver)) {
            return;
        }
        $lastSent = $this->getLastSentStamp();
        if ($lastSent && (now() < $lastSent + $hours*3600)) {
            return;
        }
        $warnings = $this->getWarnings();
        if (!$warnings) {
            return;
        }
        // send email

        /** @var Mage_Core_Model_Email_Template_Mailer $mailer */
        $mailer = Mage::getModel('core/email_template_mailer');
        /** @var Mage_Core_Model_Email_Info $emailInfo */
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($receiver, '');
        $mailer->addEmailInfo($emailInfo);

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig('trans_email/ident_support/email', 0));
        $mailer->setStoreId(0);
        $mailer->setTemplateId('heartbeat_warning_email_template');
        $mailer->setTemplateParams(array(
                'warnings' => $this->_getWarningHtml()
            )
        );
        try {
            $mailer->send();
            $this->_setLastSentStamp();
            $this->_truncateWarnings();
        } catch (Exception $e) {
            //
        }
    }

    public function getLastSentStamp()
    {
        try {
            /** @var Mage_Core_Model_Resource $resource */
            $resource = Mage::getModel('core/resource');
            $connection = $resource->getConnection('core_read');
            $select = $connection->select()
                ->from($resource->getTableName('core_config_data'), array('value'))
                ->where('path = ?', 'heartbeat/warnings/last_sent_stamp');
            return $connection->fetchOne($select);
        } catch (Exception $e) {
            return null;
        }
    }

    protected function _setLastSentStamp()
    {
        try {
            /** @var Mage_Core_Model_Resource $resource */
            $resource = Mage::getModel('core/resource');
            $connection = $resource->getConnection('core_write');
            $connection->insertOnDuplicate($resource->getTableName('core_config_data'), array(
                'scope' => 'default',
                'scope_id' => '0',
                'path' => 'heartbeat/warnings/last_sent_stamp',
                'value' => time(),
            ), array());
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    protected function _getWarningHtml()
    {
        $html = '<ul>';
        foreach ($this->getWarnings() as $warning) {
            $html .= "<li>{$warning}</li>";
        }
        $html .= '</ul>';
        return $html;
    }


    public function getWarnings()
    {
        if (!$this->_warnings) {
            try {
                /** @var Mage_Core_Model_Resource $resource */
                $resource = Mage::getModel('core/resource');
                $connection = $resource->getConnection('core_read');
                $select = $connection->select()->from($resource->getTableName('heartbeat_warnings'), array('warning'));
                $this->_warnings = $connection->fetchCol($select);
            } catch (Exception $e) {
                $this->_warnings = array();
            }
        }
        return $this->_warnings;
    }

    protected function _truncateWarnings()
    {
        try {
            /** @var Mage_Core_Model_Resource $resource */
            $resource = Mage::getModel('core/resource');
            $connection = $resource->getConnection('core_write');
            $sql = "DELETE FROM {$resource->getTableName('heartbeat_warnings')}";
            $connection->query($sql);
        } catch (Exception $e) {}
    }
}