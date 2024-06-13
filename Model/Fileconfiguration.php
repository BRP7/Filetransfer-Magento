<?php

class Ccc_Filetransfer_Model_Fileconfiguration extends Varien_Io_Ftp
{
    public function readConfiguration($config)
    {
        $ftpConfig = array(
            'host' => $config->getHost(),
            'user' => $config->getUser(),
            'password' => $config->getPassword(),
            'port' => $config->getPort(),
        );

        $this->open($ftpConfig);

        // List files and directories in the root directory
        $files = $this->ls();
    
        foreach ($files as $file) {
            if($file['text']=='./downloadedfiles'){
                continue;
            }
            // Process each file in the root directory
            $config->saveFile($this->_conn, $file['text']);
        }
    
        // Close FTP connection
        $this->close();


    }
}
