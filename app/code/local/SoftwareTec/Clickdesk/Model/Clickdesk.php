<?php

class SoftwareTec_Clickdesk_Model_Clickdesk extends Mage_Core_Model_Abstract
{

    protected $_widgetid = '';

    public function _construct()
    {
        parent::_construct();
        $this->_init('clickdesk/clickdesk');
    }

    public function getWidgetId()
    {
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $result = $db->query('SELECT * FROM clickdesk LIMIT 1');
        if ($result) {

            if ($row = $result->fetch()) {
                $this->_widgetid = $row['widgetid'];
                return $row['widgetid'];
            }
        }
        return '';
    }

    public function getWidgetCurl()
    {
        define('LIVILY_SERVER_URL', 'http://wp-1.contactuswidget.appspot.com/');
        define('LIVILY_DASHBOARD_URL', LIVILY_SERVER_URL . 'widgets.jsp?wpurl=');

        $Path = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $_SERVER['REQUEST_URI'];
        $Path = urlencode($Path);

        $cdURL = LIVILY_DASHBOARD_URL;
        return $cdURL;
    }
}
