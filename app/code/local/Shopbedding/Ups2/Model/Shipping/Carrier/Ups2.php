<?php
/**
 * UPS shipping rates estimation - the copy of original one
 *
 * @category   Shopbedding
 * @package    Shopbedding_Ups2
 */
class Shopbedding_Ups2_Model_Shipping_Carrier_Ups2 extends Mage_Usa_Model_Shipping_Carrier_Ups
{

    protected $_code = 'ups2';



    protected function _parseCgiResponse($response)
    {
        $costArr = array();
        $priceArr = array();
        $errorTitle = Mage::helper('usa')->__('Unknown error');
        if (strlen(trim($response))>0) {
            $rRows = explode("\n", $response);
            $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));
            foreach ($rRows as $rRow) {
                $r = explode('%', $rRow);
                switch (substr($r[0],-1)) {
                    case 3: case 4:
                        if (in_array($r[1], $allowedMethods)) {
                            $responsePrice = Mage::app()->getLocale()->getNumber($r[8]);
                            $costArr[$r[1]] = $responsePrice;
                            $priceArr[$r[1]] = $this->getMethodPrice($responsePrice, $r[1]);
                        }
                        break;
                    case 5:
                        $errorTitle = $r[1];
                        break;
                    case 6:
                        if (in_array($r[3], $allowedMethods)) {
                            $responsePrice = Mage::app()->getLocale()->getNumber($r[10]);
                            $costArr[$r[3]] = $responsePrice;
                            $priceArr[$r[3]] = $this->getMethodPrice($responsePrice, $r[3]);
                        }
                        break;
                }
            }
            asort($priceArr);
        }

        $result = Mage::getModel('shipping/rate_result');
        $defaults = $this->getDefaults();
        if (empty($priceArr)) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('ups2');
            $error->setCarrierTitle($this->getConfigData('title'));
            //$error->setErrorMessage($errorTitle);
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method=>$price) {
                $rate = Mage::getModel('shipping/rate_result_method');
                $rate->setCarrier('ups2');
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $method_arr = $this->getCode('method', $method);
                $rate->setMethodTitle(Mage::helper('usa')->__($method_arr));
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                $result->append($rate);
            }
        }
#echo "<pre>".print_r($result,1)."</pre>";
        return $result;
    }


    protected function _parseXmlResponse($xmlResponse)
    {
        $costArr = array();
        $priceArr = array();
        if (strlen(trim($xmlResponse))>0) {
            $xml = new Varien_Simplexml_Config();
            $xml->loadString($xmlResponse);
            $arr = $xml->getXpath("//RatingServiceSelectionResponse/Response/ResponseStatusCode/text()");
            $success = (int)$arr[0];
            if($success===1){
                $arr = $xml->getXpath("//RatingServiceSelectionResponse/RatedShipment");
                $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));

                // Negotiated rates
                $negotiatedArr = $xml->getXpath("//RatingServiceSelectionResponse/RatedShipment/NegotiatedRates");
                $negotiatedActive = $this->getConfigFlag('negotiated_active')
                    && $this->getConfigData('shipper_number')
                    && !empty($negotiatedArr);

                foreach ($arr as $shipElement){
                    $code = (string)$shipElement->Service->Code;
                    #$shipment = $this->getShipmentByCode($code);
                    if (in_array($code, $allowedMethods)) {

                        if ($negotiatedActive) {
                            $cost = $shipElement->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;
                        } else {
                            $cost = $shipElement->TotalCharges->MonetaryValue;
                        }

                        $costArr[$code] = $cost;
                        $priceArr[$code] = $this->getMethodPrice(floatval($cost),$code);
                    }
                }
            } else {
                $arr = $xml->getXpath("//RatingServiceSelectionResponse/Response/Error/ErrorDescription/text()");
                $errorTitle = (string)$arr[0][0];
                $error = Mage::getModel('shipping/rate_result_error');
                $error->setCarrier('ups2');
                $error->setCarrierTitle($this->getConfigData('title'));
                //$error->setErrorMessage($errorTitle);
                $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            }
        }

        $result = Mage::getModel('shipping/rate_result');
        $defaults = $this->getDefaults();
        if (empty($priceArr)) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('ups2');
            $error->setCarrierTitle($this->getConfigData('title'));
            if(!isset($errorTitle)){
                $errorTitle = Mage::helper('usa')->__('Cannot retrieve shipping rates');
            }
            //$error->setErrorMessage($errorTitle);
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method=>$price) {
                $rate = Mage::getModel('shipping/rate_result_method');
                $rate->setCarrier('ups2');
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $method_arr = $this->getShipmentByCode($method);
                $rate->setMethodTitle($method_arr);
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                $result->append($rate);
            }
        }
        return $result;
    }


    protected function _getCgiTracking($trackings)
    {
        //ups no longer support tracking for data streaming version
        //so we can only reply the popup window to ups.
        $result = Mage::getModel('shipping/tracking_result');
        $defaults = $this->getDefaults();
        foreach($trackings as $tracking){
            $status = Mage::getModel('shipping/tracking_result_status');
            $status->setCarrier('ups2');
            $status->setCarrierTitle($this->getConfigData('title'));
            $status->setTracking($tracking);
            $status->setPopup(1);
            $status->setUrl("http://wwwapps.ups.com/WebTracking/processInputRequest?HTMLVersion=5.0&error_carried=true&tracknums_displayed=5&TypeOfInquiryNumber=T&loc=en_US&InquiryNumber1=$tracking&AgreeToTermsAndConditions=yes");
            $result->append($status);
        }

        $this->_result = $result;
        return $result;
    }


    protected function _parseXmlTrackingResponse($trackingvalue, $xmlResponse)
    {
        $errorTitle = 'Unable to retrieve tracking';
        $resultArr = array();
        $packageProgress = array();

        if ($xmlResponse) {
            $xml = new Varien_Simplexml_Config();
            $xml->loadString($xmlResponse);
            $arr = $xml->getXpath("//TrackResponse/Response/ResponseStatusCode/text()");
            $success = (int)$arr[0][0];

            if($success===1){
                $arr = $xml->getXpath("//TrackResponse/Shipment/Service/Description/text()");
                $resultArr['service'] = (string)$arr[0];

                $arr = $xml->getXpath("//TrackResponse/Shipment/PickupDate/text()");
                $resultArr['shippeddate'] = (string)$arr[0];

                $arr = $xml->getXpath("//TrackResponse/Shipment/Package/PackageWeight/Weight/text()");
                $weight = (string)$arr[0];

                $arr = $xml->getXpath("//TrackResponse/Shipment/Package/PackageWeight/UnitOfMeasurement/Code/text()");
                $unit = (string)$arr[0];

                $resultArr['weight'] = "{$weight} {$unit}";

                $activityTags = $xml->getXpath("//TrackResponse/Shipment/Package/Activity");
                if ($activityTags) {
                    $i=1;
                    foreach ($activityTags as $activityTag) {
                        $addArr=array();
                        if (isset($activityTag->ActivityLocation->Address->City)) {
                            $addArr[] = (string)$activityTag->ActivityLocation->Address->City;
                        }
                        if (isset($activityTag->ActivityLocation->Address->StateProvinceCode)) {
                            $addArr[] = (string)$activityTag->ActivityLocation->Address->StateProvinceCode;
                        }
                        if (isset($activityTag->ActivityLocation->Address->CountryCode)) {
                            $addArr[] = (string)$activityTag->ActivityLocation->Address->CountryCode;
                        }
                        $dateArr = array();
                        $date = (string)$activityTag->Date;//YYYYMMDD
                        $dateArr[] = substr($date,0,4);
                        $dateArr[] = substr($date,4,2);
                        $dateArr[] = substr($date,-2,2);

                        $timeArr = array();
                        $time = (string)$activityTag->Time;//HHMMSS
                        $timeArr[] = substr($time,0,2);
                        $timeArr[] = substr($time,2,2);
                        $timeArr[] = substr($time,-2,2);

                        if($i==1){
                           $resultArr['status'] = (string)$activityTag->Status->StatusType->Description;
                           $resultArr['deliverydate'] = implode('-',$dateArr);//YYYY-MM-DD
                           $resultArr['deliverytime'] = implode(':',$timeArr);//HH:MM:SS
                           $resultArr['deliverylocation'] = (string)$activityTag->ActivityLocation->Description;
                           $resultArr['signedby'] = (string)$activityTag->ActivityLocation->SignedForByName;
                           if ($addArr) {
                            $resultArr['deliveryto']=implode(', ',$addArr);
                           }
                        }else{
                           $tempArr=array();
                           $tempArr['activity'] = (string)$activityTag->Status->StatusType->Description;
                           $tempArr['deliverydate'] = implode('-',$dateArr);//YYYY-MM-DD
                           $tempArr['deliverytime'] = implode(':',$timeArr);//HH:MM:SS
                           if ($addArr) {
                            $tempArr['deliverylocation']=implode(', ',$addArr);
                           }
                           $packageProgress[] = $tempArr;
                        }
                        $i++;
                    }
                    $resultArr['progressdetail'] = $packageProgress;
                }
            } else {
                $arr = $xml->getXpath("//TrackResponse/Response/Error/ErrorDescription/text()");
                $errorTitle = (string)$arr[0][0];
            }
        }

        if (!$this->_result) {
            $this->_result = Mage::getModel('shipping/tracking_result');
        }

        $defaults = $this->getDefaults();

        if ($resultArr) {
            $tracking = Mage::getModel('shipping/tracking_result_status');
            $tracking->setCarrier('ups2');
            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->setTracking($trackingvalue);
            $tracking->addData($resultArr);
            $this->_result->append($tracking);
        } else {
            $error = Mage::getModel('shipping/tracking_result_error');
            $error->setCarrier('ups2');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setTracking($trackingvalue);
            $error->setErrorMessage($errorTitle);
            $this->_result->append($error);
        }
        return $this->_result;
    }

    public function getCode($type, $code='')
        {
            $codes = array(
                'action'=>array(
                    'single'=>'3',
                    'all'=>'4',
                ),

                'originShipment'=>array(
                    // United States Domestic Shipments
                    'United States Domestic Shipments' => array(
                        '01' => Mage::helper('usa')->__('Next Day Air'),
                        '02' => Mage::helper('usa')->__('Second Day Air'),
                        '03' => Mage::helper('usa')->__('Standard (2-6 Business Days)'),
                        '07' => Mage::helper('usa')->__('Worldwide Express'),
                        '08' => Mage::helper('usa')->__('Worldwide Expedited'),
                        '11' => Mage::helper('usa')->__('Standard'),
                        '12' => Mage::helper('usa')->__('Three-Day Select'),
                        '13' => Mage::helper('usa')->__('Next Day Air Saver'),
                        '14' => Mage::helper('usa')->__('Next Day Air Early A.M.'),
                        '54' => Mage::helper('usa')->__('Worldwide Express Plus'),
                        '59' => Mage::helper('usa')->__('Second Day Air A.M.'),
                        '65' => Mage::helper('usa')->__('Saver'),
                    ),
                    // Shipments Originating in United States
                    'Shipments Originating in United States' => array(
                        '01' => Mage::helper('usa')->__('Next Day Air'),
                        '02' => Mage::helper('usa')->__('Second Day Air'),
                        '03' => Mage::helper('usa')->__('Standard (2-6 Business Days)'),
                        '07' => Mage::helper('usa')->__('Worldwide Express'),
                        '08' => Mage::helper('usa')->__('Worldwide Expedited'),
                        '11' => Mage::helper('usa')->__('Standard'),
                        '12' => Mage::helper('usa')->__('Three-Day Select'),
                        '14' => Mage::helper('usa')->__('Next Day Air Early A.M.'),
                        '54' => Mage::helper('usa')->__('Worldwide Express Plus'),
                        '59' => Mage::helper('usa')->__('Second Day Air A.M.'),
                        '65' => Mage::helper('usa')->__('Saver'),
                    ),
                    // Shipments Originating in Canada
                    'Shipments Originating in Canada' => array(
                        '01' => Mage::helper('usa')->__('Express'),
                        '02' => Mage::helper('usa')->__('Expedited'),
                        '07' => Mage::helper('usa')->__('Worldwide Express'),
                        '08' => Mage::helper('usa')->__('Worldwide Expedited'),
                        '11' => Mage::helper('usa')->__('Standard (2-6 Business Days)'),
                        '12' => Mage::helper('usa')->__('Three-Day Select'),
                        '14' => Mage::helper('usa')->__('Express Early A.M.'),
                        '65' => Mage::helper('usa')->__('Saver'),
                    ),
                    // Shipments Originating in the European Union
                    'Shipments Originating in the European Union' => array(
                        '07' => Mage::helper('usa')->__('Express'),
                        '08' => Mage::helper('usa')->__('Expedited'),
                        '11' => Mage::helper('usa')->__('Standard (2-6 Business Days)'),
                        '54' => Mage::helper('usa')->__('Worldwide Express PlusSM'),
                        '65' => Mage::helper('usa')->__('Saver'),
                    ),
                    // Polish Domestic Shipments
                    'Polish Domestic Shipments' => array(
                        '07' => Mage::helper('usa')->__('Express'),
                        '08' => Mage::helper('usa')->__('Expedited'),
                        '11' => Mage::helper('usa')->__('Standard (2-6 Business Days)'),
                        '54' => Mage::helper('usa')->__('Worldwide Express Plus'),
                        '65' => Mage::helper('usa')->__('Saver'),
                        '82' => Mage::helper('usa')->__('Today Standard'),
                        '83' => Mage::helper('usa')->__('Today Dedicated Courrier'),
                        '84' => Mage::helper('usa')->__('Today Intercity'),
                        '85' => Mage::helper('usa')->__('Today Express'),
                        '86' => Mage::helper('usa')->__('Today Express Saver'),
                    ),
                    // Puerto Rico Origin
                    'Puerto Rico Origin' => array(
                        '01' => Mage::helper('usa')->__('Next Day Air'),
                        '02' => Mage::helper('usa')->__('Second Day Air'),
                        '03' => Mage::helper('usa')->__('Standard (2-6 Business Days)'),
                        '07' => Mage::helper('usa')->__('Worldwide Express'),
                        '08' => Mage::helper('usa')->__('Worldwide Expedited'),
                        '14' => Mage::helper('usa')->__('Next Day Air Early A.M.'),
                        '54' => Mage::helper('usa')->__('Worldwide Express Plus'),
                        '65' => Mage::helper('usa')->__('Saver'),
                    ),
                    // Shipments Originating in Mexico
                    'Shipments Originating in Mexico' => array(
                        '07' => Mage::helper('usa')->__('Express'),
                        '08' => Mage::helper('usa')->__('Expedited'),
                        '54' => Mage::helper('usa')->__('Express Plus'),
                        '65' => Mage::helper('usa')->__('Saver'),
                    ),
                    // Shipments Originating in Other Countries
                    'Shipments Originating in Other Countries' => array(
                        '07' => Mage::helper('usa')->__('Express'),
                        '08' => Mage::helper('usa')->__('Worldwide Expedited'),
                        '11' => Mage::helper('usa')->__('Standard (2-6 Business Days)'),
                        '54' => Mage::helper('usa')->__('Worldwide Express Plus'),
                        '65' => Mage::helper('usa')->__('Saver')
                    )
                ),

                'method'=>array(
                    '1DM'    => Mage::helper('usa')->__('Next Day Air Early AM'),
                    '1DML'   => Mage::helper('usa')->__('Next Day Air Early AM Letter'),
                    '1DA'    => Mage::helper('usa')->__('Next Day Air'),
                    '1DAL'   => Mage::helper('usa')->__('Next Day Air Letter'),
                    '1DAPI'  => Mage::helper('usa')->__('Next Day Air Intra (Puerto Rico)'),
                    '1DP'    => Mage::helper('usa')->__('Next Day Air Saver'),
                    '1DPL'   => Mage::helper('usa')->__('Next Day Air Saver Letter'),
                    '2DM'    => Mage::helper('usa')->__('2nd Day Air AM'),
                    '2DML'   => Mage::helper('usa')->__('2nd Day Air AM Letter'),
                    '2DA'    => Mage::helper('usa')->__('2nd Day Air'),
                    '2DAL'   => Mage::helper('usa')->__('2nd Day Air Letter'),
                    '3DS'    => Mage::helper('usa')->__('3 Day Select'),
                    'GND'    => Mage::helper('usa')->__('Standard (2-6 Business Days)'),
                    'GNDCOM' => Mage::helper('usa')->__('Ground Commercial'),
                    'GNDRES' => Mage::helper('usa')->__('Ground Residential'),
                    'STD'    => Mage::helper('usa')->__('Canada Standard'),
                    'XPR'    => Mage::helper('usa')->__('Worldwide Express'),
                    'WXS'    => Mage::helper('usa')->__('Worldwide Express Saver'),
                    'XPRL'   => Mage::helper('usa')->__('Worldwide Express Letter'),
                    'XDM'    => Mage::helper('usa')->__('Worldwide Express Plus'),
                    'XDML'   => Mage::helper('usa')->__('Worldwide Express Plus Letter'),
                    'XPD'    => Mage::helper('usa')->__('Worldwide Expedited'),
                ),

                'pickup'=>array(
                    'RDP'    => array("label"=>'Regular Daily Pickup',"code"=>"01"),
                    'OCA'    => array("label"=>'On Call Air',"code"=>"07"),
                    'OTP'    => array("label"=>'One Time Pickup',"code"=>"06"),
                    'LC'     => array("label"=>'Letter Center',"code"=>"19"),
                    'CC'     => array("label"=>'Customer Counter',"code"=>"03"),
                ),

                'container'=>array(
                    'CP'     => '00', // Customer Packaging
                    'ULE'    => '01', // Letter Envelope
                    'UT'     => '03', // Tube
                    'UEB'    => '21', // Express Box
                    'UW25'   => '24', // Worldwide 25 kilo
                    'UW10'   => '25', // Worldwide 10 kilo
                ),

                'container_description'=>array(
                    'CP'     => Mage::helper('usa')->__('Customer Packaging'),
                    'ULE'    => Mage::helper('usa')->__('Letter Envelope'),
                    'UT'     => Mage::helper('usa')->__('Tube'),
                    'UEB'    => Mage::helper('usa')->__('Express Box'),
                    'UW25'   => Mage::helper('usa')->__('Worldwide 25 kilo'),
                    'UW10'   => Mage::helper('usa')->__('Worldwide 10 kilo'),
                ),

                'dest_type'=>array(
                    'RES'    => '01', // Residential
                    'COM'    => '02', // Commercial
                ),

                'dest_type_description'=>array(
                    'RES'    => Mage::helper('usa')->__('Residential'),
                    'COM'    => Mage::helper('usa')->__('Commercial'),
                ),

                'unit_of_measure'=>array(
                    'LBS'   =>  Mage::helper('usa')->__('Pounds'),
                    'KGS'   =>  Mage::helper('usa')->__('Kilograms'),
                ),

            );

            if (!isset($codes[$type])) {
    //            throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid UPS CGI code type: %s', $type));
                return false;
            } elseif (''===$code) {
                return $codes[$type];
            }

            if (!isset($codes[$type][$code])) {
    //            throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid UPS CGI code for type %s: %s', $type, $code));
                return false;
            } else {
                return $codes[$type][$code];
            }
        }

}
