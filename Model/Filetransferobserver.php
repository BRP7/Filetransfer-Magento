<?php

class Ccc_Filetransfer_Model_Filetransferobserver extends Varien_Io_Ftp
{
    protected $_configData;

    public function setConfigData($config)
    {
        $this->_configData = $config;
        // var_dump('Config data set:', $config->getData());
        return $this;
    }

    public function setConnection($conn)
    {
        $this->_conn = $conn;
        // var_dump('FTP connection set.');
        return $this;
    }

    protected function isDirectory($filepath)
    {
        $originalDir = ftp_pwd($this->_conn);
        // var_dump('Checking if directory:', $filepath);
        if (@ftp_chdir($this->_conn, $filepath)) {
            ftp_chdir($this->_conn, $originalDir);
            // var_dump($filepath . ' is a directory.');
            return true;
        } else {
            // var_dump($filepath . ' is not a directory.');
            return false;
        }
    }

    protected function listDirectory($directory)
    {
        // var_dump('Listing directory:', $directory);
        $files = array();
        $listing = ftp_nlist($this->_conn, $directory);
        // var_dump('Directory listing:', $listing);
        foreach ($listing as $item) {
            if ($item !== '.' && $item !== '..') {
                $files[] = array('text' => $item);
            }
        }
        // var_dump('Filtered directory listing:', $files);
        return $files;
    }

    public function saveAndDownloadFiles($filepath)
    {
        var_dump('Processing file:', $filepath);
        if ($this->isDirectory($filepath)) {
            var_dump('Entering directory:', $filepath);
            $remoteFiles = $this->listDirectory($filepath);
            foreach ($remoteFiles as $remoteFile) {
                $this->saveAndDownloadFiles($remoteFile['text']);
            }
        } else {
            var_dump('Downloading file:', $filepath);
            $fileNewPath = str_replace("./", '', $filepath);
            $localFilePath = Mage::getBaseDir('var') . DS . 'filetransfer' . DS . $this->_configData->getId() . DS . $fileNewPath;
            var_dump('Local file path:', $localFilePath);

            $directory = dirname($localFilePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
                var_dump('Created directory:', $directory);
            }

            $fileContents = $this->read($filepath);
            if ($fileContents !== false) {
                file_put_contents($localFilePath, $fileContents);
                var_dump('File downloaded:', $filepath);
            } else {
                var_dump('FTP file read failed for file:', $filepath);
                throw new Exception('Failed to save attachment: ' . $filepath);
            }

            $this->saveFileToDb($filepath);
            $this->moveFile($filepath);
            // Change directory back to the previous one after processing the file
            $this->cd($this->_configData->getRemoteDirectory());
        }
    }

    

    protected function moveFile($source) {
        $destination = str_replace('./', './downloadedfiles/', $source);
        var_dump('Destination:', $destination);
        
        $destinationDir = dirname($destination);
        
        // Check if parent directory is writable
        if (is_writable(dirname($destinationDir))) {
            echo "Parent directory is writable!\n";
        } else {
            echo "Parent directory is not writable. Please check permissions.\n";
        }
        
        // Create destination directory if it doesn't exist
        if (!is_dir($destinationDir)) {
            if (!mkdir($destinationDir, 0777, true)) {
                var_dump(error_get_last()); // Display any errors that occurred during directory creation
                throw new Exception('Failed to create directory: ' . $destinationDir);
            }
            var_dump('Created directory:', $destinationDir);
        } else {
            var_dump('Directory already exists:', $destinationDir);
        }
    
        // Verify directory creation and permissions
        if (is_dir($destinationDir) && is_writable($destinationDir)) {
            echo "Directory is created and writable!\n";
        } else {
            echo "Directory is not writable. Please check permissions.\n";
        }
        
        // Attempt to move file
        var_dump('Moving file from', $source, 'to', $destination);
        if (!rename($source, $destination)) {
            var_dump('Failed to move file from', $source, 'to', $destination);
            var_dump(error_get_last()); // Display any errors that occurred during file moving
            // Log the error
            Mage::log('Failed to move file from ' . $source . ' to ' . $destination);
            throw new Exception('Failed to move file: ' . $source);
        }
    }
    
    
    
    public function saveFileToDb($filepath)
    {
        // var_dump('Saving file to DB:', $filepath);
        $creationDate = $this->getFileCreationDate($filepath);
        $configurationId = $this->_configData->getId();
        $fileModel = Mage::getModel('ccc_filetransfer/filetransfer');
        $fileModel->setFilePath($filepath)
            ->setConfigurationId($configurationId)
            ->setFileDate($creationDate)
            ->save();
    }

    public function getProperFileName($filepath, $configId = '', $date = "")
    {
        $unzipFilePath = strpos($filepath, Mage::getBaseDir('var')) !== false;
        if ($unzipFilePath) {
            return $filepath;
        }
        if ($configId != '') {
            $configurationId = $configId;
        } else {
            $configurationId = $this->_configData->getId();
        }
        if ($date != '') {
            $creationDate = $date;
        } else {
            $creationDate = $this->getFileCreationDate($filepath);
        }

        $newDate = str_replace(" ", "_", $creationDate);
        $fileNewPath = str_replace("./", '', $filepath);

        $fileNewPath = $configurationId . '_' . $newDate . '_' . $fileNewPath;
        $filename = preg_replace('/[^\w\-\.]/', '_', $fileNewPath);
        return $filename;
    }

    protected function getFileCreationDate($filepath)
    {
        // var_dump('Getting file creation date for:', $filepath);
        $lastModifiedTime = ftp_mdtm($this->_conn, $filepath);
        if ($lastModifiedTime === -1) {
            return null;
        } else {
            $creationDate = date('Y-m-d H:i:s', $lastModifiedTime);
            // var_dump('File creation date:', $creationDate);
            return $creationDate;
        }
    }
}
