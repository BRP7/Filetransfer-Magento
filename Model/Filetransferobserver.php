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
        // var_dump('Processing file:', $filepath);
        if ($this->isDirectory($filepath)) {
            // var_dump('Entering directory:', $filepath);
            $remoteFiles = $this->listDirectory($filepath);
            foreach ($remoteFiles as $remoteFile) {
                $this->saveAndDownloadFiles($remoteFile['text']);
            }
        } else {
            // $filepath=$this->getProperFileName($filepath);
            //     $fileNewPath = str_replace("./", '', $filepath);
            //     $localFilePath = Mage::getBaseDir('var') . DS . 'filetransfer' . DS . $this->_configData->getId() . DS . $fileNewPath;

            $fileNewPath = str_replace("./", '', $filepath);
            $localFilePath = Mage::getBaseDir('var') . DS . 'filetransfer' . DS . $this->_configData->getId() . DS . $fileNewPath;
            
            // var_dump('Local file path:', $localFilePath);
            
            $directory = dirname($localFilePath);
            var_dump($directory);
            if (!is_dir($directory)) {

                mkdir($directory, 0777, true);
                // var_dump('Created directory:', $directory);
                }
                $filepath=$this->getProperFileName($filepath);
                $fileNewPath = str_replace("./", '', $filepath);
                $localFilePath = Mage::getBaseDir('var') . DS . 'filetransfer' . DS . $this->_configData->getId() . DS . $fileNewPath;

            $fileContents = $this->read($filepath);
            if ($fileContents !== false) {
                file_put_contents($localFilePath, $fileContents);
                // var_dump('File downloaded:', $filepath);
            } else {
                // var_dump('FTP file read failed for file:', $filepath);
                throw new Exception('Failed to save attachment: ' . $filepath);
            }

            $this->saveFileToDb($fileNewPath);
            // $this->cd($this->_configData->getRemoteDirectory());
            // $this->moveFile($filepath);
            // Change directory back to the previous one after processing the file
        }
    }



    protected function moveFile($source)
    {
        // Define the destination path by replacing './' with './downloadedfiles/'
        $destination = str_replace('./', './downloadedfiles/', $source);
        var_dump('Destination:', $destination);

        // Extract the directory part of the destination path
        $destinationDir = dirname($destination);
        var_dump('Destination Directory:', $destinationDir);

        // Check if the parent directory of the destination directory is writable
        if (is_writable(dirname($destinationDir))) {
            echo "Parent directory is writable!\n";
        } else {
            echo "Parent directory is not writable. Please check permissions.\n";
        }

        // Create the Varien_Io_File instance
        $io = new Varien_Io_File();

        // Check if the destination directory already exists
        if (!$io->fileExists($destinationDir, false)) {
            // After attempting to create the destination directory
            if (!$io->fileExists($destinationDir, false)) {
                if (!$io->mkdir($destinationDir, 0777, true)) {
                    // Log the error
                    Mage::log('Failed to create directory: ' . $destinationDir);
                    // Throw an exception with more details
                    throw new Exception('Failed to create directory: ' . $destinationDir . ". Error: " . $io->getError());
                } else {
                    echo "Directory created: " . $destinationDir . "\n";
                }
            }

            // Before moving the file
            if (!$io->fileExists($source)) {
                // Log the error
                Mage::log('Source file does not exist: ' . $source);
                throw new Exception('Source file does not exist: ' . $source);
            }

            // Attempt to move the file using Varien_Io_File
            if (!$io->mv($source, $destination)) {
                // Log the error
                Mage::log('Failed to move file from ' . $source . ' to ' . $destination . ". Error: " . $io->getError());
                // Throw an exception with more details
                throw new Exception('Failed to move file: ' . $source);
            }

            // Create the destination directory if it doesn't exist
            if (!$io->mkdir($destinationDir, 0777, true)) {
                var_dump(error_get_last()); // Display any errors that occurred during directory creation
                throw new Exception('Failed to create directory: ' . $destinationDir);
            } else {
                echo "Directory created: " . $destinationDir . "\n";
            }
        } else {
            echo "Directory already exists: " . $destinationDir . "\n";
        }

        // Verify directory creation and permissions
        if (is_dir($destinationDir) && is_writable($destinationDir)) {
            echo "Directory is created and writable!\n";
        } else {
            echo "Directory is not writable. Please check permissions.\n";
        }

        // Attempt to move the file using Varien_Io_File
        var_dump('Moving file from', $source, 'to', $destination);
        if (!$io->mv($source, $destination)) {
            var_dump('Failed to move file from', $source, 'to', $destination);
            var_dump(error_get_last()); // Display any errors that occurred during file moving
            // Log the error
            Mage::log('Failed to move file from ' . $source . ' to ' . $destination);
            throw new Exception('Failed to move file: ' . $source);
        } else {
            echo "File moved successfully!\n";
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
        if($this->isDirectory(dirname($filepath))){
            $str=str_replace(dirname($filepath).'/','',$filepath);
            var_dump($str);
        }

        // $unzipFilePath = strpos($filepath, Mage::getBaseDir('var')) !== false;
        // if ($unzipFilePath) {
        //     return $filepath;
        // }
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
        // var_dump($newDate);
        $fileNewPath = str_replace("./", '', $filepath);

        $fileNewPath = $configurationId . '_' . $newDate . '_' . $fileNewPath;
        $filename = preg_replace('/[^\w\-\.]/', '_', $fileNewPath);
        // var_dump($filename);
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
