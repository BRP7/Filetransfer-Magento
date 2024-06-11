<?php

class Ccc_Locationcheck_Block_Adminhtml_Locationcheck_Grid_Renderer_Datetime extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        Mage::log('Datetime Renderer Called', null, 'vendorinventory.log');
        $date = $row->getData($this->getColumn()->getIndex());
        return Mage::helper('core')->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true);
    }
}
