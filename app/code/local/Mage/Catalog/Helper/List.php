<?php

/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */
class Mage_Catalog_Helper_List extends Mage_Core_Helper_Abstract {

    private $configModel;
    private $fullColorList = array();

    /**
     * Get an array containing swatch image => listing image
     *
     * @param <type> $product
     * @param <type> $numberOfColors - -1 to return all
     * @return <type>
     */
    public function getSwatches($product, $limit = -1, $random = false) {
        if (! isset($this->configModel)) {
            $this->configModel = Mage::getModel('catalog/product_type_configurable');
        }

        $children = array();
        $childProducts = $this->configModel->getUsedProductCollection($product)
                        ->addAttributeToSelect('color')
                        ->addAttributeToSelect('swatch_image')
                        ->addAttributeToSelect('swatch_image_label')
                        ->addAttributeToSelect('image_label')
                        ->addAttributeToSelect('image')                        ;

        if ($limit != -1) {
            $childProducts->getSelect()->limit($limit);
        }

        $colors = array();
        foreach ($childProducts as $child) {
            if ($child['color'] == NULL) {
                return false;
            }
            if (! isset($colors[$child['color']])) {
                $colors[$child["color"]] = array(
                    "swatch_image" => "20/".$child["swatch_image"],
                    "swatch_image_label" => $child["swatch_image_label"],
                    "full_image" => (string)Mage::helper('catalog/image')->init($child, 'image')->resize(210)
                        //(!empty($child["image_label"])) ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'productImages/210/'.$child['image_label'] : (string)Mage::helper('catalog/image')->init($child, 'image')->resize(210) //$child["image"]
                );
            }
        }
        return $this->sortColors($colors);
    }

    private function sortColors($colors) {
        if (empty($this->fullColorList)) {
            $i = 0;
            $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'color');
            foreach ( $attribute->getSource()->getAllOptions(true, true) as $option){
                $this->fullColorList[] = $option['value'];
                $i++;
            }
        }
        $sortedColors = array();
        foreach ($this->fullColorList as $colorOption) {
            if (isset($colors[$colorOption])) {
                $sortedColors[] = $colors[$colorOption];
            }
        }
        return $sortedColors;
    }

    /**
     * @param $color_name
     * @return string
     */
    function getHexColorByName($color_name)
    {
    
        // standard 147 HTML color names
        $colors  =  array(
            'aliceblue'=>'F0F8FF',
            'antiquewhite'=>'FAEBD7',
            'aqua'=>'00b3b9',
            'aquamarine'=>'7FFFD4',
            'azure'=>'F0FFFF',
            'beige'=>'F5F5DC',
            'bisque'=>'FFE4C4',
            'black'=>'2f2e2a',
            'blanchedalmond '=>'FFEBCD',
            'blue'=>'0000FF',
            'blueviolet'=>'8A2BE2',
            'brown'=>'412925',
            'burlywood'=>'DEB887',
            'burgundy'=>'451d1d',
            'cadetblue'=>'5F9EA0',
            'chartreuse'=>'7FFF00',
            'chocolate'=>'391413',
            'coral'=>'FF7F50',
            'cornflowerblue'=>'6495ED',
            'cornsilk'=>'FFF8DC',
            'crimson'=>'DC143C',
            'cyan'=>'00FFFF',
            'darkblue'=>'00008B',
            'darkcyan'=>'008B8B',
            'darkgoldenrod'=>'B8860B',
            'darkgray'=>'A9A9A9',
            'darkgreen'=>'006400',
            'darkgrey'=>'A9A9A9',
            'darkkhaki'=>'BDB76B',
            'darkmagenta'=>'8B008B',
            'darkolivegreen'=>'556B2F',
            'darkorange'=>'FF8C00',
            'darkorchid'=>'9932CC',
            'darkred'=>'8B0000',
            'darksalmon'=>'E9967A',
            'darkseagreen'=>'8FBC8F',
            'darkslateblue'=>'483D8B',
            'darkslategray'=>'2F4F4F',
            'darkslategrey'=>'2F4F4F',
            'darkturquoise'=>'00CED1',
            'darkviolet'=>'9400D3',
            'deeppink'=>'FF1493',
            'deepskyblue'=>'00BFFF',
            'dimgray'=>'696969',
            'dimgrey'=>'696969',
            'dodgerblue'=>'1E90FF',
            'firebrick'=>'B22222',
            'floralwhite'=>'FFFAF0',
            'forestgreen'=>'228B22',
            'fuchsia'=>'FF00FF',
            'gainsboro'=>'DCDCDC',
            'ghostwhite'=>'F8F8FF',
            'gold'=>'dfb06c',
            'goldenrod'=>'DAA520',
            'gray'=>'808080',
            'green'=>'008000',
            'greenyellow'=>'ADFF2F',
            'grey'=>'a9a9a9',
            'honeydew'=>'F0FFF0',
            'hotpink'=>'FF69B4',
            'indianred'=>'CD5C5C',
            'indigo'=>'4B0082',
            'ivory'=>'e1d1ba',
            'khaki'=>'F0E68C',
            'lavender'=>'ff8bd3',
            'lavenderblush'=>'FFF0F5',
            'lawngreen'=>'7CFC00',
            'lemonchiffon'=>'FFFACD',
            'lightblue'=>'ADD8E6',
            'lightcoral'=>'F08080',
            'lightcyan'=>'E0FFFF',
            'lightgoldenrodyellow'=>'FAFAD2',
            'lightgray'=>'D3D3D3',
            'lightgreen'=>'90EE90',
            'lightgrey'=>'D3D3D3',
            'lightpink'=>'FFB6C1',
            'lightsalmon'=>'FFA07A',
            'lightseagreen'=>'20B2AA',
            'lightskyblue'=>'87CEFA',
            'lightslategray'=>'778899',
            'lightslategrey'=>'778899',
            'lightsteelblue'=>'B0C4DE',
            'lightyellow'=>'FFFFE0',
            'lime'=>'00FF00',
            'limegreen'=>'32CD32',
            'linen'=>'FAF0E6',
            'magenta'=>'FF00FF',
            'maroon'=>'800000',
            'mediumaquamarine'=>'66CDAA',
            'mediumblue'=>'0000CD',
            'mediumorchid'=>'BA55D3',
            'mediumpurple'=>'9370D0',
            'mediumseagreen'=>'3CB371',
            'mediumslateblue'=>'7B68EE',
            'mediumspringgreen'=>'00FA9A',
            'mediumturquoise'=>'48D1CC',
            'mediumvioletred'=>'C71585',
            'midnightblue'=>'191970',
            'mintcream'=>'F5FFFA',
            'mistyrose'=>'FFE4E1',
            'moccasin'=>'FFE4B5',
            'navajowhite'=>'FFDEAD',
            'navy'=>'272530',
            'oldlace'=>'FDF5E6',
            'olive'=>'808000',
            'olivedrab'=>'6B8E23',
            'orange'=>'FFA500',
            'orangered'=>'FF4500',
            'orchid'=>'DA70D6',
            'palegoldenrod'=>'EEE8AA',
            'palegreen'=>'98FB98',
            'paleturquoise'=>'AFEEEE',
            'palevioletred'=>'DB7093',
            'papayawhip'=>'FFEFD5',
            'peachpuff'=>'FFDAB9',
            'peru'=>'CD853F',
            'pink'=>'f9cad0',
            'plum'=>'DDA0DD',
            'powderblue'=>'B0E0E6',
            'purple'=>'800080',
            'red'=>'FF0000',
            'rosybrown'=>'BC8F8F',
            'royalblue'=>'4169E1',
            'saddlebrown'=>'8B4513',
            'salmon'=>'FA8072',
            'sandybrown'=>'F4A460',
            'seagreen'=>'2E8B57',
            'seashell'=>'FFF5EE',
            'sienna'=>'A0522D',
            'silver'=>'C0C0C0',
            'skyblue'=>'87CEEB',
            'slateblue'=>'6A5ACD',
            'slategray'=>'708090',
            'slategrey'=>'708090',
            'snow'=>'FFFAFA',
            'springgreen'=>'00FF7F',
            'steelblue'=>'4682B4',
            'tan'=>'D2B48C',
            'teal'=>'447a6c',
            'thistle'=>'D8BFD8',
            'tomato'=>'FF6347',
            'turquoise'=>'40E0D0',
            'violet'=>'EE82EE',
            'wheat'=>'a88f79',
            'white'=>'e5e6e1',
            'whitesmoke'=>'F5F5F5',
            'yellow'=>'FFFF00',
            'Bone'=>'e3d6b4',
            'Camel'=>'b88d6d',
            'Cream'=>'bbaaa1',
            'Grape'=>'834f91',
            'Hunter'=>'334336',
            'Grape'=>'834f91',
            'Light Navy'=>'003258',
            'Jewel Blue'=>'929ec4',
            'Natural'=>'c9c3ab',
            'Rose'=>'da8a8d',
            'Sage'=>'83834d',
            'Seafoam'=>'acc3a7',
            'yellowgreen'=>'9ACD32');

        $color_name = strtolower($color_name);
        if (isset($colors[$color_name]))
        {
            return ('#' . $colors[$color_name]);
        }
        else
        {
            return false;
        }
    }

}
