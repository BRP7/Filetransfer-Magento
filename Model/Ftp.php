<?php
class Ccc_Filetransfer_Model_Ftp
{
    public function readFiles()
    {
        $collection = Mage::getModel('ccc_filetransfer/configuration')->getCollection();
        foreach ($collection as $config) {
            try {
                Mage::getModel('ccc_filetransfer/fileconfiguration')->readConfiguration($config);
            } catch (Exception $e) {
                Mage::log('Error reading files: ' . $e->getMessage(), null, 'ftp_errors.log');
            }
        }
    }
}
