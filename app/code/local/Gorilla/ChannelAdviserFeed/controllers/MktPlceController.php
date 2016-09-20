<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Gorilla_ChannelAdviserFeed_MktPlceController extends Mage_Core_Controller_Front_Action {

    public function emcundshqlAction() {

        // header("Content-Type: application/csv");
       // header("Content-Disposition: attachment; filename=feed.txt");
        // header("Pragma: no-cache");
        // header("Expires: 0");

        $this->loadLayout();
        $this->renderLayout();


    }



}

?>