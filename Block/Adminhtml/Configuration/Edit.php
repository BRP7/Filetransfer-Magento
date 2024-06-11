<?php
class Ccc_Filetransfer_Block_Adminhtml_Configuration_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_configuration';
        $this->_blockGroup = 'ccc_filetransfer';
        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('ccc_filetransfer')->__('Save Configuration'));
        $this->_updateButton('delete', 'label', Mage::helper('ccc_filetransfer')->__('Delete Configuration'));
        $this->_updateButton('login', 'label', Mage::helper('ccc_filetransfer')->__('Login'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('ccc_filetransfer')->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);
        
        $objId = $this->getRequest()->getParam($this->_objectId);
        if(!empty($objId)){
            $this->_addButton('login', array(
                'label'     => Mage::helper('adminhtml')->__('Login'),
                'onclick'   => 'setLocation(\'' . $this->getLoginUrl($objId) . '\')',
                'class'     => 'login',
            ), -1);
        }

        $this->_formScripts[] = "
        function toggleEditor() {
            if (tinyMCE.getInstanceById('block_content') == null) {
                tinyMCE.execCommand('mceAddControl', false, 'block_content');
            } else {
                tinyMCE.execCommand('mceRemoveControl', false, 'block_content');
            }
        }

        function saveAndContinueEdit(){
            editForm.submit($('edit_form').action+'back/edit/');
        }
    ";
    }
    
    public function getHeaderText()
    {
        if (Mage::registry('configuration_data')->getId()) {
            return Mage::helper('ccc_filetransfer')->__("Edit Filetransfer '%s'", $this->escapeHtml(Mage::registry('filetransfer_configuration_data')->getTitle()));
        } else {
            return Mage::helper('ccc_filetransfer')->__('New Configuration');
        }
    }

    public function getLoginUrl($objId)
    {
        return $this->getUrl('*/*/login', array('configuration_id'=>$objId,
            Mage_Core_Model_Url::FORM_KEY => $this->getFormKey()
        ));
    }
}
?>