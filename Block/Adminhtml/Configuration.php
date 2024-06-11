<?php
class Ccc_Filetransfer_Block_Adminhtml_Configuration extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_configuration';
        $this->_blockGroup = 'ccc_filetransfer';
        $this->_headerText = Mage::helper('ccc_filetransfer')->__('Manage Configurations');
        parent::__construct();
    }
}
