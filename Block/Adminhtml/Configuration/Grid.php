<?php

class Ccc_Filetransfer_Block_Adminhtml_Configuration_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ccc_filetransfer/configuration')->getCollection();
        // var_dump($collection);
        // var_dump($collection->getData()); 
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('configuration_id', array(
            'header' => Mage::helper('ccc_filetransfer')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'configuration_id',
        ));

        $this->addColumn('user', array(
            'header' => Mage::helper('ccc_filetransfer')->__('User Name'),
            'align' => 'left',
            'index' => 'user',
        ));

        $this->addColumn('port', array(
            'header' => Mage::helper('ccc_filetransfer')->__('Port'),
            'align' => 'left',
            'index' => 'port',
        ));

        $this->addColumn('host', array(
            'header' => Mage::helper('ccc_filetransfer')->__('Host'),
            'align' => 'left',
            'index' => 'host',
        ));

        $this->addColumn('is_active', array(
            'header' => Mage::helper('ccc_filetransfer')->__('Is Active'),
            'align' => 'left',
            'index' => 'is_active',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('ccc_filetransfer')->__('Yes'),
                '0' => Mage::helper('ccc_filetransfer')->__('No'),
            ),
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label' => Mage::helper('ccc_filetransfer')->__('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => Mage::helper('ccc_filetransfer')->__('Are you sure you want to delete selected configuration?')
            )
        );

        Mage::dispatchEvent('filetransfer_adminhtml_configuration_grid_prepare_massaction', array('block' => $this));
        return $this;
    }

}
