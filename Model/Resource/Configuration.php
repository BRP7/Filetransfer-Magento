<?php

class Ccc_Filetransfer_Model_Resource_Configuration extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    { 
        $this->_init('ccc_filetransfer/configuration', 'configuration_id');
    }
}


