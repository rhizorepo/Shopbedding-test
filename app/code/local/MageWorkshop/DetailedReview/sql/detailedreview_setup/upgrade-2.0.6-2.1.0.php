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

$moveConfig = array(
    'detailedreview/settings/only_verified_buyer'               => 'detailedreview/settings_customer/only_verified_buyer',
    'detailedreview/settings/write_review_once'                 => 'detailedreview/settings_customer/write_review_once',
    'detailedreview/settings/allow_guest_vote'                  => 'detailedreview/settings_customer/allow_guest_vote',
    'detailedreview/settings/show_verified_buyer_image'         => 'detailedreview/settings_customer/show_verified_buyer_image',
    'detailedreview/settings/enable_to_set_timezone'            => 'detailedreview/datetime_options/enable_to_set_timezone',
    'detailedreview/bitly/enabled'                              => 'detailedreview/social_share_optios/bitly_enabled',
    'detailedreview/bitly/login'                                => 'detailedreview/social_share_optios/bitly_login',
    'detailedreview/bitly/api_key'                              => 'detailedreview/social_share_optios/bitly_api_key',
    'detailedreview/show_settings/allow_review_graph'           => 'detailedreview/show_review_info_settings/allow_review_graph',
    'detailedreview/show_settings/allow_good_and_bad_detail'    => 'detailedreview/show_review_form_settings/allow_good_and_bad_detail',
    'detailedreview/show_settings/allow_pros_and_cons'          => 'detailedreview/show_review_form_settings/allow_pros_and_cons',
    'detailedreview/show_settings/allow_user_pros_and_cons'     => 'detailedreview/show_review_form_settings/allow_user_pros_and_cons',
    'detailedreview/show_settings/allow_image'                  => 'detailedreview/show_review_form_settings/allow_image',
    'detailedreview/show_settings/allow_video_preview'          => 'detailedreview/show_review_info_settings/allow_video_preview',
    'detailedreview/show_settings/allow_video'                  => 'detailedreview/show_review_form_settings/allow_video',
    'detailedreview/show_settings/allow_sizing'                 => 'detailedreview/show_review_form_settings/allow_sizing',
    'detailedreview/show_settings/allow_about_you'              => 'detailedreview/show_review_form_settings/allow_about_you',
    'detailedreview/show_settings/allow_response'               => 'detailedreview/show_review_info_settings/allow_response',
    'detailedreview/filters/allow_result_filters'               => 'detailedreview/show_review_info_settings/allow_result_filters',
    'detailedreview/filters/qty_items_in_highest_contributors'  => 'detailedreview/show_review_info_settings/qty_items_in_highest_contributors',
    'detailedreview/sorting_options/allow_sorting_by'           => 'detailedreview/show_review_info_settings/allow_sorting_by',

);

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
$catalogProductEntity = $installer->getEntityTypeId('catalog_product');
$coreConfigTable = $installer->getTable('core/config_data');

$idAttributes[] = $installer->getAttribute($catalogProductEntity, 'popularity_by_sells', 'attribute_id');
$idAttributes[] = $installer->getAttribute($catalogProductEntity, 'popularity_by_reviews', 'attribute_id');
$idAttributes[] = $installer->getAttribute($catalogProductEntity, 'popularity_by_rating', 'attribute_id');
foreach ($idAttributes as $idAttribute) {
    $installer->updateAttribute($catalogProductEntity, $idAttribute, array(
        'is_visible' => true
    ));
}

foreach ($moveConfig as $key => $value) {
    $installer->run("UPDATE `$coreConfigTable` SET `path` = '$value' WHERE `path` = '$key'");
}

$installer->endSetup();
