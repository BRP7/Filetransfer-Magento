<?php
class Ccc_Filetransfer_Block_Adminhtml_Ftpfile_Grid_Column_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $filePath = $row->getData('file_path');
        $configId = $row->getData('configuration_id');
        $actionsHtml = '';

        if (substr($filePath, -4) === '.xml') {
            $actionsHtml .= $this->_getXmlButtonHtml($row->getId(), $configId, $filePath);
        } 
        elseif (substr($filePath, -4) === '.zip') {
            $actionsHtml .= $this->_getExtractZipButtonHtml($row->getId(), $configId, $filePath);
        } 
        // else {
        //     $actionsHtml .= $this->_getDownloadButtonHtml($row->getId(), $configId, $filePath);
        // }

        return $actionsHtml;
    }

    protected function _getXmlButtonHtml($rowId, $configId, $filePath)
    {
        $url = $this->getUrl('*/*/convertXml', array('id' => $rowId, 'config_id' => $configId, 'file_path' => base64_encode($filePath)));
        return '<a href="' . $url . '">' . Mage::helper('ccc_filetransfer')->__('Download CSV') . '</a>';
    }

    protected function _getDownloadButtonHtml($rowId, $configId, $filePath)
    {
        $url = $this->getUrl('*/*/download', array('id' => $rowId, 'config_id' => $configId, 'file_path' => base64_encode($filePath)));
        return '<a href="' . $url . '">' . Mage::helper('ccc_filetransfer')->__('Download CSV') . '</a>';
    }

    protected function _getExtractZipButtonHtml($rowId, $configId, $filePath)
    {
        $url = $this->getUrl('*/*/extractZip', array('id' => $rowId, 'config_id' => $configId, 'file_path' => base64_encode($filePath)));
        return '<a href="' . $url . '">' . Mage::helper('ccc_filetransfer')->__('Extract ZIP') . '</a>';
    }
}
