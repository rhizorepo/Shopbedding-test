<?php
/**
 * Magmodules.eu - http://www.magmodules.eu - info@magmodules.eu
 * =============================================================
 * NOTICE OF LICENSE [Single domain license]
 * This source file is subject to the EULA that is
 * available through the world-wide-web at:
 * http://www.magmodules.eu/license-agreement/
 * =============================================================
 * @category    Magmodules
 * @package     Magmodules_Snippets
 * @author      Magmodules <info@magmodules.eu>
 * @copyright   Copyright (c) 2014 (http://www.magmodules.eu)
 * @license     http://www.magmodules.eu/license-agreement/  
 * =============================================================
 */
 
class Magmodules_Snippets_Model_System_Config_Model_License extends Mage_Core_Model_Config_Data {

    public function afterLoad()
    {
       $data = call_user_func(str_rot13('onfr64_qrpbqr'), "JG1vZHVsZSA9ICdNYWdtb2R1bGVzX1NuaXBwZXRzJzsgJG1vZHVsZV92ZXJzaW9uID0gJ01hZ21vZHVsZXNfU25pcDE3MjMnOyAkbW9kdWxlX3BhdGggPSAnc25pcHBldHMvZ2VuZXJhbC8nOyAkbW9kdWxlX3NlcnZlciA9IHN0cl9yZXBsYWNlKCd3d3cuJywgJycsICRfU0VSVkVSWydIVFRQX0hPU1QnXSk7ICRtb2R1bGVfaW5zdGFsbGVkID0gTWFnZTo6Z2V0Q29uZmlnKCktPmdldE5vZGUoKS0+bW9kdWxlcy0+TWFnbW9kdWxlc19TbmlwcGV0cy0+dmVyc2lvbjsgcmV0dXJuIGJhc2U2NF9lbmNvZGUoYmFzZTY0X2VuY29kZShiYXNlNjRfZW5jb2RlKCRtb2R1bGUgLiAnOycgLiAkbW9kdWxlX3ZlcnNpb24gLiAnOycgLiAkbW9kdWxlX2luc3RhbGxlZCAuICc7JyAuIHRyaW0oTWFnZTo6Z2V0TW9kZWwoJ2NvcmUvY29uZmlnX2RhdGEnKS0+bG9hZCgkbW9kdWxlX3BhdGggLiAnbGljZW5zZV9rZXknLCAncGF0aCcpLT5nZXRWYWx1ZSgpKSAuICc7JyAuICRtb2R1bGVfc2VydmVyIC4gJzsnIC4gTWFnZTo6Z2V0VXJsKCkgLiAnOycgLiBNYWdlOjpnZXRTaW5nbGV0b24oJ2FkbWluL3Nlc3Npb24nKS0+Z2V0VXNlcigpLT5nZXRFbWFpbCgpIC4gJzsnIC4gTWFnZTo6Z2V0U2luZ2xldG9uKCdhZG1pbi9zZXNzaW9uJyktPmdldFVzZXIoKS0+Z2V0TmFtZSgpIC4gJzsnIC4gJF9TRVJWRVJbJ1NFUlZFUl9BRERSJ10pKSk7");
	   $this->setValue(eval($data));
    }

    static function isEnabled()
    {
        return eval(call_user_func(str_rot13('onfr64_qrpbqr'), "JG1vZHVsZV92ZXJzaW9uID0gJ01hZ21vZHVsZXNfU25pcDE3MjMnOyAkbW9kdWxlX3BhdGggPSAnc25pcHBldHMvZ2VuZXJhbC8nOyAkbW9kdWxlX3NlcnZlciA9IHN0cl9yZXBsYWNlKCd3d3cuJywgJycsICRfU0VSVkVSWydIVFRQX0hPU1QnXSk7IGlmKChzaGExKHNoYTEoJG1vZHVsZV92ZXJzaW9uIC4gJ19tYWdfJyAuICRtb2R1bGVfc2VydmVyKSkpICE9ICh0cmltKE1hZ2U6OmdldE1vZGVsKCdjb3JlL2NvbmZpZ19kYXRhJyktPmxvYWQoJG1vZHVsZV9wYXRoIC4gJ2xpY2Vuc2Vfa2V5JywgJ3BhdGgnKS0+Z2V0VmFsdWUoKSkpKSB7IE1hZ2U6OmdldENvbmZpZygpLT5zYXZlQ29uZmlnKCRtb2R1bGVfcGF0aCAuICdlbmFibGVkJywgMCk7IE1hZ2U6OmdldENvbmZpZygpLT5jbGVhbkNhY2hlKCk7IE1hZ2U6OmdldFNpbmdsZXRvbignYWRtaW5odG1sL3Nlc3Npb24nKS0+YWRkRXJyb3IoTWFnZTo6aGVscGVyKCdzbmlwcGV0cycpLT5fXygiVGhlIEdvb2dsZSBSaWNoIFNuaXBwZXRzIGV4dGVuc2lvbiBjb3VsZG4ndCBiZSBlbmFibGVkLiBQbGVhc2UgbWFrZSBzdXJlIHlvdSBhcmUgdXNpbmcgYSB2YWxpZCBsaWNlbnNlIGtleS4iKSk7IHJldHVybiBmYWxzZTsgfSBlbHNlIHsgcmV0dXJuIHRydWU7IH0="));
    }
        
}
