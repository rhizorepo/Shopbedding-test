<?php

$installer = $this;
$installer->startSetup();

$dataflowData = array(
    array(
        'name'         => 'Import Customer Reviews (CSV)',
        'actions_xml'  => '<action type="dataflow/convert_parser_csv" method="parse">'."\r\n".'    <var name="delimiter"><![CDATA[,]]></var>'."\r\n".'    <var name="enclose"><![CDATA["]]></var>'."\r\n".'    <var name="fieldnames">true</var>'."\r\n".'    <var name="store"><![CDATA[0]]></var>'."\r\n".'    <var name="number_of_records">1</var>'."\r\n".'    <var name="decimal_separator"><![CDATA[.]]></var>'."\r\n".'   <var name="adapter">customerreviewsimportexport/convert_adapter_customerreviewimport</var>'."\r\n".'    <var name="method">parse</var>'."\r\n".'</action>',
        'gui_data'     => 'a:8:{s:6:"export";a:1:{s:13:"add_url_field";s:1:"0";}s:6:"import";a:2:{s:17:"number_of_records";s:1:"1";s:17:"decimal_separator";s:1:".";}s:5:"parse";a:13:{s:15:"attribute_options_delimiter";s:1:"|";}s:4:"file";a:8:{s:4:"type";s:4:"file";s:8:"filename";s:18:"import_customer_reviews.csv";s:4:"path";s:10:"var/import";s:4:"host";s:0:"";s:4:"user";s:0:"";s:8:"password";s:0:"";s:9:"file_mode";s:1:"2";s:7:"passive";s:0:"";}}',
        'direction'    => 'import',
        'entity_type'  => 'product',
        'store_id'     => 0,
        'data_transfer'=> 'interactive',
        'is_commerce_extensions' => 4
    ),
    array(
        'name'         => 'Import Customer Reviews (XML)',
        'actions_xml'  => '<action type="dataflow/convert_parser_xml_excel" method="parse">'."\r\n".'    <var name="single_sheet"><![CDATA[]]></var>'."\r\n".'    <var name="fieldnames">true</var>'."\r\n".'    <var name="store"><![CDATA[0]]></var>'."\r\n".'    <var name="number_of_records">1</var>'."\r\n".'    <var name="decimal_separator"><![CDATA[.]]></var>'."\r\n".'    <var name="adapter">customerreviewsimportexport/convert_adapter_customerreviewimport</var>'."\r\n".'    <var name="method">parse</var>'."\r\n".'</action>',
        'gui_data'     => 'a:8:{s:6:"export";a:1:{s:13:"add_url_field";s:1:"0";}s:6:"import";a:2:{s:17:"number_of_records";s:1:"1";s:17:"decimal_separator";s:1:".";}s:5:"parse";a:13:{s:15:"attribute_options_delimiter";s:1:"|";}s:4:"file";a:8:{s:4:"type";s:4:"file";s:8:"filename";s:18:"import_customer_reviews.xml";s:4:"path";s:10:"var/import";s:4:"host";s:0:"";s:4:"user";s:0:"";s:8:"password";s:0:"";s:9:"file_mode";s:1:"2";s:7:"passive";s:0:"";}}',
        'direction'    => 'import',
        'entity_type'  => 'product',
        'store_id'     => 0,
        'data_transfer'=> 'interactive',
        'is_commerce_extensions' => 4
    ),
    array(
        'name'         => 'Export Customer Reviews (CSV)',
        'actions_xml'  => '<action type="customerreviewsimportexport/convert_parser_customerreviewexport" method="unparse">'."\r\n".'    <var name="store"><![CDATA[0]]></var>'."\r\n".' 	 <var name="reviews_by_sku"><![CDATA[false]]></var>'."\r\n".'    <var name="customers_by_email"><![CDATA[false]]></var>'."\r\n".'</action>'."\r\n".''."\r\n".'<action type="dataflow/convert_mapper_column" method="map">'."\r\n".'</action>'."\r\n".''."\r\n".'<action type="dataflow/convert_parser_csv" method="unparse">'."\r\n".'    <var name="delimiter"><![CDATA[,]]></var>'."\r\n".'    <var name="enclose"><![CDATA["]]></var>'."\r\n".'    <var name="fieldnames">true</var>'."\r\n".'</action>'."\r\n".''."\r\n".'<action type="dataflow/convert_adapter_io" method="save">'."\r\n".'    <var name="type">file</var>'."\r\n".'    <var name="path">var/export</var>'."\r\n".'    <var name="filename"><![CDATA[export_customer_reviews.csv]]></var>'."\r\n".'</action>',
        'gui_data'     => 'a:8:{s:6:"export";a:1:{s:13:"add_url_field";s:1:"0";}s:6:"import";a:2:{s:17:"number_of_records";s:1:"1";s:17:"decimal_separator";s:1:".";}s:5:"parse";a:13:{s:15:"categorydelimiter";s:1:"/";}s:7:"unparse";a:7:{s:16:"categorydelimiter";s:1:"/";s:23:"export_categories_for_transfer";s:5:"false";s:23:"export_products_for_categories";s:5:"false";s:24:"export_product_position";s:5:"false";}s:4:"file";a:8:{s:4:"type";s:4:"file";s:8:"filename";s:19:"export_customer_reviews.csv";s:4:"path";s:10:"var/export";s:4:"host";s:0:"";s:4:"user";s:0:"";s:8:"password";s:0:"";s:9:"file_mode";s:1:"2";s:7:"passive";s:0:"";}}',
        'direction'    => 'export',
        'entity_type'  => 'product',
        'store_id'     => 0,
        'data_transfer'=> 'file',
        'is_commerce_extensions' => 4
    ),
    array(
        'name'         => 'Export Customer Reviews (XML)',
        'actions_xml'  => '<action type="customerreviewsimportexport/convert_parser_customerreviewexport" method="unparse">'."\r\n".'    <var name="store"><![CDATA[0]]></var>'."\r\n".'    <var name="reviews_by_sku"><![CDATA[false]]></var>'."\r\n".'    <var name="customers_by_email"><![CDATA[false]]></var>'."\r\n".' </action>'."\r\n".''."\r\n".'<action type="dataflow/convert_mapper_column" method="map">'."\r\n".'</action>'."\r\n".''."\r\n".'<action type="dataflow/convert_parser_xml_excel" method="unparse">'."\r\n".'    <var name="single_sheet"><![CDATA[]]></var>'."\r\n".'    <var name="fieldnames">true</var>'."\r\n".'</action>'."\r\n".''."\r\n".'<action type="dataflow/convert_adapter_io" method="save">'."\r\n".'    <var name="type">file</var>'."\r\n".'    <var name="path">var/export</var>'."\r\n".'    <var name="filename"><![CDATA[export_customer_reviews.xml]]></var>'."\r\n".'</action>'."\r\n".''."\r\n".'',
        'gui_data'     => 'a:8:{s:6:"export";a:1:{s:13:"add_url_field";s:1:"0";}s:6:"import";a:2:{s:17:"number_of_records";s:1:"1";s:17:"decimal_separator";s:1:".";}s:5:"parse";a:13:{s:15:"categorydelimiter";s:1:"/";}s:7:"unparse";a:7:{s:16:"entitytypeid";s:1:"10";s:23:"recordlimit";s:5:"100";s:23:"export_w_sort_order";s:5:"false";s:24:"attribute_options_delimiter";s:5:"|";}s:4:"file";a:8:{s:4:"type";s:4:"file";s:8:"filename";s:19:"export_customer_reviews.xml";s:4:"path";s:10:"var/export";s:4:"host";s:0:"";s:4:"user";s:0:"";s:8:"password";s:0:"";s:9:"file_mode";s:1:"2";s:7:"passive";s:0:"";}}',
        'direction'    => 'export',
        'entity_type'  => 'product',
        'store_id'     => 0,
        'data_transfer'=> 'file',
        'is_commerce_extensions' => 4
    )
);

foreach ($dataflowData as $profileData)
{
    Mage::getModel('customerreviewsimportexport/profile')->setData($profileData)->save();
}

$installer->endSetup();