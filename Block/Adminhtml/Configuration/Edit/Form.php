<?php

class Ccc_Filetransfer_Block_Adminhtml_Configuration_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('configuration_data');
        $isEdit = ($model && $model->getId());

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post'
        ));

              $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('ccc_filetransfer')->__('Item Information'),
            'class'  => 'fieldset-wide'
        ));

        if ($isEdit && $model->getCustomgridId()) {
            $fieldset->addField(
                'configuration_id',
                'hidden',
                array(
                    'name' => 'configuration_id',
                )
            );
        }
        
        $fieldset->addField('user', 'text', array(
            'name'     => 'user',
            'label'    => Mage::helper('ccc_filetransfer')->__('User Name'),
            'title'    => Mage::helper('ccc_filetransfer')->__('User Name'),
            'required' => true,
        ));

        $fieldset->addField('password', 'text', array(
            'name'     => 'password',
            'label'    => Mage::helper('ccc_filetransfer')->__('Password'),
            'title'    => Mage::helper('ccc_filetransfer')->__('Password'),
            'required' => true,
        ));
        $fieldset->addField('port', 'text', array(
            'name'     => 'port',
            'label'    => Mage::helper('ccc_filetransfer')->__('Port'),
            'title'    => Mage::helper('ccc_filetransfer')->__('Port'),
            'required' => true,
        ));
        $fieldset->addField('host', 'text', array(
            'name'     => 'host',
            'label'    => Mage::helper('ccc_filetransfer')->__('Host'),
            'title'    => Mage::helper('ccc_filetransfer')->__('Host'),
            'required' => true,
        ));

        $fieldset->addField('is_active', 'select', array(
            'name'     => 'is_active',
            'label'    => Mage::helper('ccc_filetransfer')->__('Is Active'),
            'title'    => Mage::helper('ccc_filetransfer')->__('Is Active'),
            'values'   => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('ccc_filetransfer')->__('Active')
                ),
                array(
                    'value' => 0,
                    'label' => Mage::helper('ccc_filetransfer')->__('In Active')
                ),
            ),
            'required' => true,
        ));

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
