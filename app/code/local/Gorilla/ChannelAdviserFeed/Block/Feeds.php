<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Csv
 *
 * @author blobdell
 */


class Gorilla_ChannelAdviserFeed_Block_Feeds extends Mage_Core_Block_Template {



    function __construct()
    {

        parent::__construct();

        $this->TitleFeedArray();


    }

    private $feedarray = array();
    private $template = array();
    private $delimiter = "\t";
    private $newline = "\n";

    private $simpleproducts = array();


    private function TitleFeedArray()
    {

        $this->template = Array(
            "Auction Title"=>"",
            "Inventory Number"=>"",
            "Quantity Update Type"=>"ABSOLUTE",
            "Quantity"=>1,
            "Starting Bid"=>"",
            "Reserve"=>"",
            "Weight"=>"",
            "ISBN"=>"",
            "UPC"=>"",
            "EAN"=>"",
            "ASIN"=>"",
            "MPN"=>"",
            "Short Description"=>"",
            "Description"=>"",
            "Manufacturer"=>"ShopBedding",
            "Brand"=>"",
            "Condition"=>"New",
            "Warranty"=>"",
            "Seller Cost"=>"",
            "Product Margin"=>"",
            "Buy It Now Price"=>"",
            "Retail Price"=>"",
            "Second Chance Offer Price"=>"",
            "Picture URLs"=>"",
            "TaxProductCode"=>"",
            "Supplier Code"=>"",
            "Supplier PO"=>"",
            "Warehouse Location"=>"",
            "Received In Inventory"=>"",
            "Inventory Subtitle"=>"",
            "Relationship Name"=>"",
            "Variation Parent SKU"=>"",
            "Ad Template Name"=>"",
            "Posting Template Name"=>"",
            "Schedule Name"=>"",
            "eBay Category List"=>"",
            "eBay Store Category Name"=>"",
            "Labels"=>"",
            "DC Code"=>"",
            "Do Not Consolidate"=>"",
            "ChannelAdviser Store Title"=>"Shop Bedding",
            "ChannelAdviser Store Description"=>"",
            "Store Meta Description"=>"",
            "ChannelAdviser Store Price"=>"",
            "ChannelAdviser Store Category ID"=>"",
            "Classification"=>"",
            "Harmonized Code"=>"",
            "Height"=>"",
            "Length"=>"",
            "Width"=>"",
            "Ship Zone Name"=>"",
            "Ship Carrier Code"=>"",
            "Ship Class Code"=>"",
            "Ship Handling First Item"=>"",
            "Ship Rate Additional Item"=>"",
            "Ship Handling Additional Item"=>""
            );

            // "Attribute1Name"=>"Color",
            // "Attribute1Value"=>"",
            // "Attribute2Name"=>"Size"
            // "Attribute2Value=>""
            // and so on

        




    }





    public function toProductXml() {

        $product = Mage::getModel('catalog/product');
        $resources = $product->getCollection()->addAttributeToSelect('*');
        echo $resources->toXml();
    }



    
    public function ArrayToTabDl()
    {

        $str = "";

        foreach ( $this->feedarray as $row )
        {
            $first = true;
            foreach( $row as $cell)
            {
                if ($first)
                {
                    $str .= $cell;
                    $first = false;
                }
                else
                    $str .= $this->delimiter.$cell;
            }

            $str .= $this->newline;

        }

        return $str;

    }



    public function ArrayToHtml()
    {

        $str = "<table border=\"1\">\n";
        
        foreach ( $this->feedarray as $row )
        {
            $str .= "  <tr>\n";
            foreach( $row as $cell)
            {
                $str .= "      <td>".$cell."</td>\n";
            }
            $str .= "  </tr>\n";
            
        }
        
        $str .= "</table>\n";

        return $str;
    }


    private function Wash($In)
    {
        
        // replace delimter and newline characters with a space
        return trim(str_replace($this->newline," ",str_replace($this->delimiter," ",$In)));
        
    }





    public function MakeSizeColorArray() {

	$productModel = Mage::getModel('catalog/product');
        $productlist = $productModel->getCollection()->addAttributeToFilter('type_id', array('eq' => 'configurable'));

	$simpleproducts = array();

	foreach ( $productlist as $product ) {

	    $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$product);

	    $sizes = array();
	    $colors = array();
    
	    foreach ( $childProducts as $childProduct ) {
	      
	      $sizes[$childProduct->getAttributeText('size')] = 1;
	      $colors[$childProduct->getAttributeText('color')] = 1;
	      
	    }

	    foreach ( $childProducts as $childProduct ) {
    
	      $simpleproducts[$childProduct->getId()] = array( "sizes"=> implode(",",array_keys( $sizes ) ), "colors"=> implode(",",array_keys( $colors ) ) );
    
	    }


	}

	$this->simpleproducts = $simpleproducts;

    }













    
    public function MakeComparisonShoppingFeed(){

	$this->MakeSizeColorArray();
	

        $data = array();
        $parentUrls = array();

        $row = array(
            "Model" => "",
            "Manufacturer" => "",
            "ManufacturerModel" => "",
            "UPC" => "",
            "MerchantCategory" => "",
            "Brand" => "",
            "Regular Price" => "",
            "Current Price" => "",
            "InStock" => "",
            "ReferenceImageURL" => "",
            "OfferName" => "",
            "OfferDescription" => "",
            "ActionURL" => "",
            "PromotionalText" => "",
            "Vendor" => "",
            "ShippingPrice" => "",
            "Weight" => "",
            "Condition" => "",
            "StockDescription" => "",
            "Keywords" => "",
            "AvailableSizes" => "",
            "AvailableColors" => "",
            "Material" => "",
            "PaymentAccepted" => "",
            "ProductCost" => "",
            "ProductMargin" => "" );


        $header = implode($this->delimiter, array_keys($row));
        $header.= $this->newline;
        $data[] = $header;

        $resourceModel = Mage::getResourceModel('catalog/product');
        $productModel = Mage::getModel('catalog/product');
        $productlist = $productModel->setStoreId(1)->getCollection()->addAttributeToFilter('type_id', array('eq' => 'simple'));                 // ->addAttributeToFilter('type_id', array('eq' => 'configurable'))
                           /*         ->addAttributeToSelect('visibility')
                                    ->addAttributeToSelect('sku')
                                    ->addAttributeToSelect('ccf_manufacturer')
                                    ->addAttributeToSelect('ccf_upc')
                                    ->addAttributeToSelect('ccf_merchantcategory') 
                                    ->addAttributeToSelect('ccf_brand')
                                    ->addAttributeToSelect('price')
                                    ->addAttributeToSelect('special_price')
                                    ->addAttributeToSelect('image')
                                    ->addAttributeToSelect('name')
                                    ->addAttributeToSelect('description')
                                    ->addAttributeToSelect('ccf_actionurl')
                                    ->addAttributeToSelect('ccf_promotionaltext')
                                    ->addAttributeToSelect('ccf_vendor')
                                    ->addAttributeToSelect('ccf_shippingprice')
                                    ->addAttributeToSelect('weight')
                                    ->addAttributeToSelect('ccf_condition')
                                    ->addAttributeToSelect('ccf_stockdescription')
                                    ->addAttributeToSelect('ccf_keywords')
                                    ->addAttributeToSelect('ccf_availablesizes')
                                    ->addAttributeToSelect('ccf_availablecolors')
                                    ->addAttributeToSelect('material')
                                    ->addAttributeToSelect('ccf_paymentaccepted')
                                    ->addAttributeToSelect('ccf_productioncost')
                                    ->addAttributeToSelect('ccf_productmargin')
                                    ->addAttributeToSelect('color')
                                    ->addAttributeToSelect('size')
                                    ->addUrlRewrite()
                                    ;
*/


        $productlist->load();

	// copy ids into an array

	$productIds = array();

	foreach ( $productlist as $productIdx )
	  $productIds[] = round($productIdx->getId());
      
	unset( $productlist );
	unset( $productIdx );
	


        $i=0;
        foreach ( $productIds as $productIdx )
        {

	    $product = Mage::getModel('catalog/product')->load($productIdx);


            $i++;
            
            $row["Model"] = $this->Wash($product->getSku());
            $row["Manufacturer"] = $this->Wash($product->getCcfManufacturer());
            $row["ManufacturerModel"] = $this->Wash($product->getSku());
            $row["UPC"] = $this->Wash($product->getCcfUpc());
            // $row["MerchantCategory"] = $this->Wash($product->getCcfMerchantcategory());
            $row["MerchantCategory"] = $this->Wash($this->getCategoryString($product));
            $row["Brand"] = $this->Wash($product->getCcfBrand());
            $row["RegularPrice"] = $this->Wash($product->getPrice());
            $row["CurrentPrice"] = $this->Wash($product->getSpecialPrice());
            $row["InStock"] = "1";
            $row["ReferenceImageURL"] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$product->getImage();
            $row["OfferName"] = $this->Wash($product->getName());
            $row["OfferDescription"] = $this->Wash($this->getDescriptionAfterTheUlTag($product));

            $productUrl = $product->getProductUrl();
            if ($product->getVisibility() == 1) {
                $parentIds = $resourceModel->getParentProductIds($product);
                if (!empty($parentIds)) {
                    if (array_key_exists($parentIds[0], $parentUrls)) {
                        $productUrl = $parentUrls[$parentIds[0]];
                    } else {
                        $parent = Mage::getModel('catalog/product')->load($parentIds[0]);
                        $productUrl = $parent->getProductUrl();
                        $parentUrls[$parentIds[0]] = $productUrl;
                    }
                }
            }
            $row["ActionURL"] = $this->Wash($productUrl);

            $row["PromotionalText"] = $this->Wash($product->getCcfPromotionaltext());
            $row["Vendor"] = $this->Wash($product->getCcfVendor());
            $row["ShippingPrice"] = $this->Wash($product->getCcfShippingprice());
            $row["Weight"] = $this->Wash($product->getWeight());
            $row["Condition"] = $this->Wash($product->getCcfCondition());
            $row["StockDescription"] = $this->Wash($product->getCcfStockdescription());
            $row["Keywords"] = $this->Wash($product->getCcfKeywords());

            $attributes = $this->getAllColorsAndSizesOfParentIfItExists($product);
            $row["AvailableColors"] = $this->Wash( $attributes["colors"] );
            $row["AvailableSizes"] = $this->Wash( $attributes["sizes"] );

            $row["Material"] = $this->Wash($product->getMaterial());
            $row["PaymentAccepted"] = $this->Wash($product->getCcfPaymentaccepted());
            $row["ProductionCost"] = $this->Wash($product->getCcfProductioncost());
            $row["ProductMargin"] = $this->Wash($product->getCcfProductmargin());


            $data[] = $this->_outputRow($row);

	    unset($product);
	    

        }

        return $data;

    }





    private function getAllColorsAndSizesOfParentIfItExists($product)
    {

	$Id = $product->getId();

	if ( isset ( $this->simpleproducts[$Id] ) )
	{

	    $results["colors"] = $this->simpleproducts[$Id]["colors"];
	    $results["sizes"] = $this->simpleproducts[$Id]["sizes"];
	}
	else
	{

	    $results["colors"] = $product->getAttributeText('color');
	    $results["sizes"] = $product->getAttributeText('size');
	}

	return $results;


       
    }




    private function getColorAndSizeList($product)
    {

        // Leaving this here in case Aaron and/or Rachel change their mind.
        
        if ( $product->isConfigurable() )
        {

            $colors = array();
            $sizes = array();

            $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$product);
            foreach ( $childProducts as $childProduct )
            {
                $colors[$childProduct->getAttributeText('color')] = 1;
                $sizes[$childProduct->getAttributeText('size')] = 1;
            }

            $result["colors"] = implode(",",array_keys($colors));
            $result["sizes"] = implode(",",array_keys($sizes));

            return $result;

        }

        $result["colors"] = $product->getAttributeText('color');
        $result["sizes"] = $product->getAttributeText('size');

        return $result;


    }



    private function getDescriptionAfterTheUlTag($product)
    {

        try {

            $pieces = preg_split("/<\/[Uu][Ll]>/", $product->getDescription());

            $tagregex = "/<([\/A-Za-z][\/A-Z0-9a-z ]*)>/";
            $newpiece = preg_replace( $tagregex, "", $pieces[1]);

            return trim( $newpiece );

        }
        catch ( Exception $ex )
        {
            Mage::logException($ex);
            return "";
        }

    }





    private function getTidyDescription($product)
    {

        // Keep this in case Aaron and Rachel change their mind.

        try {
        // get stuff between <ul> and delete all else

            $pieces = preg_split( "/<\/[Uu][Ll]>/", $product->getDescription() );
            $piece = $pieces[0];

            $pieces = preg_split( "/<[Uu][Ll]>/", $piece);
            $piece = $pieces[1];

            $tagregex = "/<([\/A-Za-z][A-Z0-9a-z]*)>/";

            $newpiece = preg_replace( $tagregex, "", $piece);

    //        echo trim( $newpiece );

            return trim( $newpiece );

        }
        catch ( Exception $ex )
        {
            Mage::logException($ex);
            return "";
        }

    }




    private function getCategoryString($product)
    {

        $firstCatId = false;
        $firstSubCatId = false;

        $categories = $product->getCategoryCollection();

        foreach ( $categories as $category )
        {
            $catIds = explode( "/", $category->getPath() );

            if ( count($catIds) == 4 )
            {
                $firstCatId = $catIds[2];
                $firstSubCatId = $catIds[3];
                break;
            }
            else if ( ( count( $catIds) == 3 ) & ! $firstCatId )
            {
                $firstCatId = $catIds[2];
            }


        }


        $categorystring = "";

        if ( $firstCatId )
        {
            $categorystring = Mage::getModel('catalog/category')->load( $firstCatId )->getName();

            if ( $firstSubCatId )
            {
                $categorystring = $categorystring.", ".Mage::getModel('catalog/category')->load( $firstSubCatId )->getName();
            }

        }

        return $categorystring;

    }


    // comparison shopping
    private function _outputRow($row)
    {

        $str = "";

        $str.= $row["Model"].$this->delimiter;
        $str.=     $row["Manufacturer"].$this->delimiter;
        $str.=     $row["ManufacturerModel"].$this->delimiter;
        $str.=     $row["UPC"].$this->delimiter;
        $str.=     $row["MerchantCategory"].$this->delimiter;
        $str.=     $row["Brand"].$this->delimiter;
        $str.=     $row["RegularPrice"].$this->delimiter;
        $str.=     $row["CurrentPrice"].$this->delimiter;
        $str.=     $row["InStock"].$this->delimiter;
        $str.=     $row["ReferenceImageURL"].$this->delimiter;
        $str.=     $row["OfferName"].$this->delimiter;
        $str.=     $row["OfferDescription"].$this->delimiter;
        $str.=     $row["ActionURL"].$this->delimiter;
        $str.=     $row["PromotionalText"].$this->delimiter;
        $str.=     $row["Vendor"].$this->delimiter;
        $str.=     $row["ShippingPrice"].$this->delimiter;
        $str.=     $row["Weight"].$this->delimiter;
        $str.=     $row["Condition"].$this->delimiter;
        $str.=     $row["StockDescription"].$this->delimiter;
        $str.=     $row["Keywords"].$this->delimiter;
        $str.=     $row["AvailableSizes"].$this->delimiter;
        $str.=     $row["AvailableColors"].$this->delimiter;
        $str.=     $row["Material"].$this->delimiter;
        $str.=     $row["PaymentAccepted"].$this->delimiter;
        $str.=     $row["ProductionCost"].$this->delimiter;
        $str.=     $row["ProductMargin"].$this->delimiter;

        $str .= $this->newline;

        return $str;

    }









    
    public function MakeMarketPlaceFeed()
    {
        return $this->GenerateCAArray();
        //return $this->ArrayToHtml();
       
    }



    public function GenerateCAArray()
    {
        $response = array();

        $response[] = $this->_outputMktPlceHeader();

        
        $productModel = Mage::getModel('catalog/product');
        $productlist = $productModel->getCollection()->addAttributeToSelect('sku')
                ->addAttributeToSelect('weight')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('description')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('special_price')

                ->addAttributeToSelect('mpf_startingbid')
                ->addAttributeToSelect('mpf_reserve')
                ->addAttributeToSelect('mpf_upc')
                ->addAttributeToSelect('mpf_manufacturer')
                ->addAttributeToSelect('mpf_brand')
                ->addAttributeToSelect('mpf_condition')
                ->addAttributeToSelect('mpf_warranty')
                ->addAttributeToSelect('mpf_sellercost')
                ->addAttributeToSelect('mpf_productmargin')
                ->addAttributeToSelect('mpf_buyitnowprice')
                ->addAttributeToSelect('mpf_secondchanceofferprice')
                ->addAttributeToSelect('mpf_taxproductcode')
                ->addAttributeToSelect('mpf_suppliercode')
                ->addAttributeToSelect('mpf_warehouselocation')
                ->addAttributeToSelect('mpf_receivedininventory')
                ->addAttributeToSelect('mpf_inventorysubtitle')
                ->addAttributeToSelect('mpf_relationshipname')
                ->addAttributeToSelect('mpf_adtemplatename')
                ->addAttributeToSelect('mpf_postingtemplatename')
                ->addAttributeToSelect('mpf_schedulename')
                ->addAttributeToSelect('mpf_ebaycategorylist')
                ->addAttributeToSelect('mpf_ebaystorecategoryname')
                ->addAttributeToSelect('mpf_labels')
                ->addAttributeToSelect('mpf_dccode')
                ->addAttributeToSelect('mpf_donotconsolidate')
                ->addAttributeToSelect('mpf_channeladviserstoretitle')
                ->addAttributeToSelect('mpf_channeladviserstoredescription')
                ->addAttributeToSelect('mpf_storemetadescription')
                ->addAttributeToSelect('mpf_channeladviserstoreprice')
                ->addAttributeToSelect('mpf_channeladviserstorecategoryid')
                ->addAttributeToSelect('mpf_classification')
                ->addAttributeToSelect('mpf_harmonizedcode')
                ->addAttributeToSelect('mpf_height')
                ->addAttributeToSelect('mpf_length')
                ->addAttributeToSelect('mpf_width')
                ->addAttributeToSelect('mpf_shipzonename')
                ->addAttributeToSelect('mpf_shipcarriercode')
                ->addAttributeToSelect('mpf_chipclasscode')   //note: typo in attribute in db. reflected here (so it still works)
                ->addAttributeToSelect('mpf_shiphandlingfirstitem')
                ->addAttributeToSelect('mpf_shiprateadditionalitem')
                ->addAttributeToSelect('mpf_shiphandlingadditionalitem');



        $parentmodel = Mage::getModel('catalog/product');

        $productlist->load();

        foreach ( $productlist as $product )
        {

            // die($product->getMpfStartingbid());
            $this->template["Auction Title"] = $product->getName();
            $this->template["Inventory Number"] = $product->getSku();
            $this->template["Quantity Update Type"] = "ABSOLUTE";
            $this->template["Quantity"] = "1";
            $this->template["Starting Bid"] = $product->getMpfStartingbid();
            $this->template["Reserve"] = $product->getMpfReserve();
            $this->template["Weight"] = $product->getWeight();
            
            $this->template["ISBN"] = "";
            $this->template["UPC"] = $product->getMpfUpc();
            $this->template["EAN"] = "";
            $this->template["ASIN"] = "";
            $this->template["MPN"] = "";
            $this->template["Short Description"] = $product->getName();
            $this->template["Description"] = $product->getDescription();
            $this->template["Manufacturer"] = $product->getMpfManufacturer();

            
            $this->template["Brand"] = $product->getMpfBrand();
            $this->template["Condition"] = $product->getMpfCondition();
            $this->template["Warranty"] = $product->getMpfWarranty();
            $this->template["Seller Cost"] = $product->getMpfSellercost();
            $this->template["Product Margin"] = $product->getMpfProductmargin();
            $this->template["Buy It Now Price"] = $product->getMpfBuyitnowprice();
            $this->template["Retail Price"] = $product->getPrice();
            $this->template["Second Chance Offer Price"] = $product->getMpfSecondchanceofferprice();
            $this->template["Picture URLs"] =  Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$product->getImage();
            $this->template["TaxProductCode"] = $product->getMpfTaxproductcode();
            $this->template["Supplier Code"] = $product->getMpfSuppliercode();
            $this->template["Supplier PO"] = $product->getMpfSuppliercode();
            $this->template["Warehouse Location"] = $product->getMpfWarehouselocation();
            $this->template["Received In Inventory"] = $product->getMpfReceivedininventory();
            $this->template["Inventory Subtitle"] = $product->getMpfInventorysubtitle();
            $this->template["Relationship Name"] = $product->getMpfRelationshipname();                                // what will I put here?ARENT" : $product->loadParentProductIds()->getData('parent_product_ids');
            $this->template["Ad Template Name"] = $product->getMpfAdtemplatename();                                 // a

            

            if ( $product->getTypeId() == "configurable" )
            {
                $this->template["Variation Parent SKU"] = "PARENT";
            }
            else
            {
               //$this->template["Variation Parent SKU"] = print_r( $product->getData(), true );
               //Mage::log( print_r( $product->getData(), true ) );

                $product->loadParentProductIds();
                $parentids = $product->getParentProductIds();

                if ( count( $parentids ) > 0 )
                {
                    $parentid = $parentids[0];

                    // next, get parent sku

                    $parentmodel->load($parentid);
                    $this->template["Variation Parent SKU"] = $parentmodel->getSku();

                }
                else
                {
                    // Simple object is not part of a Configurable object
                    $this->template["Variation Parent SKU"] = "";
                }
            }

            $this->template["Posting Template Name"] = $product->getMpfPostingtemplatename();                         // and here?
            $this->template["Schedule Name"] = $product->getMpfSchedulename();                                 // and here?
            $this->template["eBay Category List"] = $product->getMpfEbaycategorylist();
            $this->template["eBay Store Category Name"] = $product->getMpfEbaystorecategoryname();
            $this->template["Labels"] = $product->getMpfLabels();                                          // and here?
            $this->template["DC Code"] = $product->getMpfDccode();
            $this->template["Do Not Consolidate"] = $product->getMpfDonotconsolidate();
            $this->template["ChannelAdviser Store Title"] = $product->getMpfChanneladviserstoretitle();
            $this->template["ChannelAdviser Store Description"] = $product->getMpfChanneladviserstoredescription();
            $this->template["Store Meta Description"] = $product->getMpfStoremetadescription();
            $this->template["ChannelAdviser Store Price"] = $product->getSpecialPrice();
            $this->template["ChannelAdviser Store Category ID"] = $product->getMpfChanneladviserstorecategoryid();                  // and here?
            $this->template["Classification"] = $product->getMpfClassification();                              // and here?
            $this->template["Harmonized Code"] = $product->getMpfHarmonizedcode();
            $this->template["Height"] = $product->getMpfHeight();
            $this->template["Length"] = $product->getMpfLength();
            $this->template["Width"] = $product->getMpfWidth();
            $this->template["Ship Zone Name"] = $product->getMpfShipzonename();
            $this->template["Ship Carrier Code"] = $product->getMpfShipcarriercode();
            $this->template["Ship Class Code"] = $product->getMpfChipclasscode(); //note: typo in attribute in db. reflected here (so it still works)
            $this->template["Ship Handling First Item"] = $product->getMpfShiphandlingfirstitem();
            $this->template["Ship Rate Additional Item"] = $product->getMpfShiprateadditionalitem();
            $this->template["Ship Handling Additional Item"] = $product->getMpfShiphandlingadditionalitem();

            
            $response[] = $this->_outputMktPlceRow();
                        
        }


        return $response;

    }




    private function _outputMktPlceHeader()
    {
        $str = "";

            $str .= "Auction Title".$this->delimiter;
            $str .= "Inventory Number".$this->delimiter;
            $str .= "Quantity Update Type".$this->delimiter;
            $str .= "Quantity".$this->delimiter;
            $str .= "Starting Bid".$this->delimiter;
            $str .= "Reserve".$this->delimiter;
            $str .= "Weight".$this->delimiter;
            $str .= "ISBN".$this->delimiter;
            $str .= "UPC".$this->delimiter;
            $str .= "EAN".$this->delimiter;
            $str .= "ASIN".$this->delimiter;
            $str .= "MPN".$this->delimiter;
            $str .= "Short Description".$this->delimiter;
            $str .= "Description".$this->delimiter;
            $str .= "Manufacturer".$this->delimiter;
            $str .= "Brand".$this->delimiter;
            $str .= "Condition".$this->delimiter;
            $str .= "Warranty".$this->delimiter;
            $str .= "Seller Cost".$this->delimiter;
            $str .= "Product Margin".$this->delimiter;
            $str .= "Buy It Now Price".$this->delimiter;
            $str .= "Retail Price".$this->delimiter;
            $str .= "Second Chance Offer Price".$this->delimiter;
            $str .= "Picture URLs".$this->delimiter;
            $str .= "TaxProductCode".$this->delimiter;
            $str .= "Supplier Code".$this->delimiter;
            $str .= "Supplier PO".$this->delimiter;
            $str .= "Warehouse Location".$this->delimiter;
            $str .= "Received In Inventory".$this->delimiter;
            $str .= "Inventory Subtitle".$this->delimiter;
            $str .= "Relationship Name".$this->delimiter;
            $str .= "Variation Parent SKU".$this->delimiter;
            $str .= "Ad Template Name".$this->delimiter;
            $str .= "Posting Template Name".$this->delimiter;
            $str .= "Schedule Name".$this->delimiter;
            $str .= "eBay Category List".$this->delimiter;
            $str .= "eBay Store Category Name".$this->delimiter;
            $str .= "Labels".$this->delimiter;
            $str .= "DC Code".$this->delimiter;
            $str .= "Do Not Consolidate".$this->delimiter;
            $str .= "ChannelAdviser Store Title".$this->delimiter;
            $str .= "ChannelAdviser Store Description".$this->delimiter;
            $str .= "Store Meta Description".$this->delimiter;
            $str .= "ChannelAdviser Store Price".$this->delimiter;
            $str .= "ChannelAdviser Store Category ID".$this->delimiter;
            $str .= "Classification".$this->delimiter;
            $str .= "Harmonized Code".$this->delimiter;
            $str .= "Height".$this->delimiter;
            $str .= "Length".$this->delimiter;
            $str .= "Width".$this->delimiter;
            $str .= "Ship Zone Name".$this->delimiter;
            $str .= "Ship Carrier Code".$this->delimiter;
            $str .= "Ship Class Code".$this->delimiter;
            $str .= "Ship Handling First Item".$this->delimiter;
            $str .= "Ship Rate Additional Item".$this->delimiter;
            $str .= "Ship Handling Additional Item".$this->newline;


            return $str;

    }








    private function _outputMktPlceRow()
    {

            $row = $this->template;

            $str = "";

            $str .= $row["Auction Title"].$this->delimiter;
            $str .= $row["Inventory Number"].$this->delimiter;
            $str .= $row["Quantity Update Type"].$this->delimiter;
            $str .= $row["Quantity"].$this->delimiter;
            $str .= $row["Starting Bid"].$this->delimiter;
            $str .= $row["Reserve"].$this->delimiter;
            $str .= $row["Weight"].$this->delimiter;
            $str .= $row["ISBN"].$this->delimiter;
            $str .= $row["UPC"].$this->delimiter;
            $str .= $row["EAN"].$this->delimiter;
            $str .= $row["ASIN"].$this->delimiter;
            $str .= $row["MPN"].$this->delimiter;
            $str .= $row["Short Description"].$this->delimiter;
            $str .= $row["Description"].$this->delimiter;
            $str .= $row["Manufacturer"].$this->delimiter;
            $str .= $row["Brand"].$this->delimiter;
            $str .= $row["Condition"].$this->delimiter;
            $str .= $row["Warranty"].$this->delimiter;
            $str .= $row["Seller Cost"].$this->delimiter;
            $str .= $row["Product Margin"].$this->delimiter;
            $str .= $row["Buy It Now Price"].$this->delimiter;
            $str .= $row["Retail Price"].$this->delimiter;
            $str .= $row["Second Chance Offer Price"].$this->delimiter;
            $str .= $row["Picture URLs"].$this->delimiter;
            $str .= $row["TaxProductCode"].$this->delimiter;
            $str .= $row["Supplier Code"].$this->delimiter;
            $str .= $row["Supplier PO"].$this->delimiter;
            $str .= $row["Warehouse Location"].$this->delimiter;
            $str .= $row["Received In Inventory"].$this->delimiter;
            $str .= $row["Inventory Subtitle"].$this->delimiter;
            $str .= $row["Relationship Name"].$this->delimiter;
            $str .= $row["Variation Parent SKU"].$this->delimiter;
            $str .= $row["Ad Template Name"].$this->delimiter;
            $str .= $row["Posting Template Name"].$this->delimiter;
            $str .= $row["Schedule Name"].$this->delimiter;
            $str .= $row["eBay Category List"].$this->delimiter;
            $str .= $row["eBay Store Category Name"].$this->delimiter;
            $str .= $row["Labels"].$this->delimiter;
            $str .= $row["DC Code"].$this->delimiter;
            $str .= $row["Do Not Consolidate"].$this->delimiter;
            $str .= $row["ChannelAdviser Store Title"].$this->delimiter;
            $str .= $row["ChannelAdviser Store Description"].$this->delimiter;
            $str .= $row["Store Meta Description"].$this->delimiter;
            $str .= $row["ChannelAdviser Store Price"].$this->delimiter;
            $str .= $row["ChannelAdviser Store Category ID"].$this->delimiter;
            $str .= $row["Classification"].$this->delimiter;
            $str .= $row["Harmonized Code"].$this->delimiter;
            $str .= $row["Height"].$this->delimiter;
            $str .= $row["Length"].$this->delimiter;
            $str .= $row["Width"].$this->delimiter;
            $str .= $row["Ship Zone Name"].$this->delimiter;
            $str .= $row["Ship Carrier Code"].$this->delimiter;
            $str .= $row["Ship Class Code"].$this->delimiter;
            $str .= $row["Ship Handling First Item"].$this->delimiter;
            $str .= $row["Ship Rate Additional Item"].$this->delimiter;
            $str .= $row["Ship Handling Additional Item"].$this->newline;

            return $str;
    }





}

?>
