<?php
class Ccc_Filetransfer_Block_Adminhtml_Ftpfile_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);

    }
    protected function _prepareCollection()
    {
        echo 123;
        $collection = Mage::getModel('ccc_filetransfer/filetransfer')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('filetransfer_id', array(
            'header' => Mage::helper('ccc_filetransfer')->__('Id'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'filetransfer_id',
        )
        );

        $this->addColumn('file_path', array(
            'header' => Mage::helper('ccc_filetransfer')->__('File Path'),
            'align' => 'left',
            'index' => 'file_path',
            'type' => 'text',
            'column_css_class' => 'row_name',
        )
        );
        $this->addColumn('file_date', array(
            'header' => Mage::helper('ccc_filetransfer')->__('File Date'),
            'align' => 'left',
            'index' => 'file_date',
            'type' => 'text',
            'column_css_class' => 'row_name',
        )
        );
        $this->addColumn('configuration_id', array(
            'header' => Mage::helper('ccc_filetransfer')->__('Configuration Id'),
            'align' => 'left',
            'index' => 'configuration_id',
            'type' => 'text',
            'column_css_class' => 'row_name',
        )
        );
        return parent::_prepareColumns();
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
