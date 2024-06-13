<?php
// app/code/local/Ccc/Filetransfer/Block/Adminhtml/Renderer/ActionButtons.php
class Ccc_Filetransfer_Block_Adminhtml_Renderer_ActionButtons extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $filePath = $row->getData('file_path');
        $actionsHtml = '';

        if (substr($filePath, -4) === '.xml') {
            $actionsHtml .= $this->_getXmlButtonHtml($row->getId());
        } else {
            $actionsHtml .= $this->_getDownloadButtonHtml($row->getId());
        }

        return $actionsHtml;
    }

    protected function _getXmlButtonHtml($rowId)
    {
        $url = $this->getUrl('*/*/convertXml', array('id' => $rowId));

        $buttonHtml = '<button type="button" onclick="window.location.href=\'' . $url . '\'">';
        $buttonHtml .= Mage::helper('ccc_filetransfer')->__('Convert XML');
        $buttonHtml .= '</button>';

        return $buttonHtml;
    }

    protected function _getDownloadButtonHtml($rowId)
    {
        $url = $this->getUrl('*/*/download', array('id' => $rowId));

        $buttonHtml = '<button type="button" onclick="window.location.href=\'' . $url . '\'">';
        $buttonHtml .= Mage::helper('ccc_filetransfer')->__('Download CSV');
        $buttonHtml .= '</button>';

        return $buttonHtml;
    }
}
