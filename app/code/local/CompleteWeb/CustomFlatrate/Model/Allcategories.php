<?php
/**
 * Source for cron frequency 
 *
 * @category    Find
 * @package     Find_Feed
 */
class CompleteWeb_CustomFlatrate_Model_Allcategories
{
    /**
     * Fetch options array
     *
     * @return array
     */
     public function toOptionArray()
    {
		/*$category = Mage::getModel(‘catalog/category’);
$tree = $category->getTreeModel();
$tree->load();
$ids = $tree->getCollection()->getAllIds();
$arr = array();
if ($ids)
{
foreach ($ids as $id)
{
$cat = Mage::getModel(‘catalog/category’);
$cat->load($id);
//$arr[$id] = $cat->getName();
$arr[] = array(‘value’=> $id,’label’=> $cat->getName());
}
}
return $arr;    */
$categories = Mage::getModel("catalog/category")
->getCollection()
->addAttributeToSelect('*')
->addIsActiveFilter();

$all = array();
$prestr='';
foreach ($categories as $c)
{
//$all[$c] = $c->getName();
$prestr='';
$level=$c->getLevel();

if($level > 1){
for($i=1;$i< $level;$i++)
{
$prestr.='- ';

}
}

$all[] = array('value'=> $c->getId(),'label'=> $prestr.$c->getName());
}

return $all;
		$rootcatId= Mage::app()->getStore()->getRootCategoryId();
$categories = Mage::getModel('catalog/category')->getCategories($rootcatId);

		echo  $this->get_categories($categories);
exit;
		$category = Mage::getModel("catalog/category"); 
		$tree = $category->getTreeModel(); 
		$tree->load();
		
		$ids = $tree->getCollection()->getAllIds(); 
		$arr = array();
		
		if ($ids){ 
			foreach ($ids as $id){ 
				$cat = Mage::getModel("catalog/category"); 
				$cat->load($id);
				$cat_data = $cat->getData();
			//	echo "<pre>"; print_r($cat_data);// exit;
				if(isset($cat_data['is_active']) && $cat_data['is_active'] == 1) {
					$cat_arr = array('label' => $cat_data['name'] , 'value' => $cat_data['entity_id']);
					array_push($arr, $cat_arr); 
				}
			} 
		}
		
		$categoriesArray = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSort('path', 'asc')
            ->load()
            ->toArray();

    $categories = array();
    foreach ($categoriesArray as $categoryId => $category) {
        if (isset($category['name']) && isset($category['level'])) {
            $categories[] = array(
                'label' => $category['name'],
                'level'  =>$category['level'],
                'value' => $categoryId
            );
        }
    }

    return $categories;
		
		//exit;
		
		return $arr;
    }
	
	public function  get_categories($categories) { //This is the recursive function created and here we pass the a collection of categories.
$array= '<ul>';  //$array is a variable to store all the category detail .
foreach($categories as $category) {
        $cat = Mage::getModel('catalog/category')->load($category->getId());
        $count = $cat->getProductCount(); //$count the total no of products in the category
        $array .= '<li>'.'<a href="' . Mage::getUrl($cat->getUrlPath()). '">' . $category->getName() . "(".$count.")</a>\n"; //In this line we get an a link for the product and product count of that category
        if($category->hasChildren()) {  
             $children = Mage::getModel('catalog/category')->getCategories($category-> getId()); // $children get a list of all subcategories
            $array .=  $this->get_categories($children); //recursive call the get_categories function again.
            }
         $array .= '</li>';
    }
    return  $array . '</ul>';
}


}
