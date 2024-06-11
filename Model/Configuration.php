<?php

class Ccc_Filetransfer_Model_Configuration extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('ccc_filetransfer/configuration');
    }

    public function saveFile($conn,$file){
        Mage::getModel('ccc_filetransfer/filetransferobserver')
        ->setConfigData($this)
        ->setConnection($conn)
        ->readAndSave($file);
    }
}
