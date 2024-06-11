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
        $files = $this->ls();
        
        foreach ($files as $file) {
            $pathInfo = pathinfo($file['text']);
            if ($pathInfo['extension']) {
                $config->saveFile($this->_conn, $file);
            }
        }
        $this->close();


    }
}