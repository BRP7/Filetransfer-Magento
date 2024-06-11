<?php

class Ccc_Filetransfer_Model_Filetransferobserver extends Varien_Io_Ftp
{
    protected $_configData;


    public function setConfigData($config)
    {
        $this->_configData = $config;
        return $this;
    }

    public function setConnection($conn)
    {
        $this->_conn = $conn;
        return $this;
    }
    protected function isDirectory($filepath)
    {
        $listing = ftp_mlsd($this->_conn, $filepath);
        var_dump($listing);
        if ($listing === false || count($listing) === 0) {
            return false;
        }
        $firstItem = $listing[0];
        return $firstItem['type'] === 'dir';
    }
    public function readAndSave($filepath)
    {
        if ($this->isDirectory($filepath)) {
            $this->recursiveReadAndSaveDirectory($filepath);
        } else {
            $this->saveAndDownloadFiles($filepath);
        }
    }

    public function saveAndDownloadFiles($file)
    {
        $filepath = $file['text'];
        $filename = $this->getProperFileName($filepath);
        $localFilePath = Mage::getBaseDir('var') . DS . 'filetransfer' . DS . $this->_configData->getId() . DS . $filename;

        $directory = dirname($localFilePath);

        if (!is_dir($directory)) {
            echo "Directory does not exist: " . $directory;
            mkdir($directory, 0777, true);
        } elseif (!is_writable($directory)) {
            echo "Directory is not writable: " . $directory;
            chmod($directory, 0777);
        } else {
            echo "Directory exists and is writable: " . $directory;
        }

        echo "Trying to create file at: " . $localFilePath . "\n";

        $newFile = fopen($localFilePath, 'w');
        var_dump($newFile);
        if ($newFile !== false) {
            fclose($newFile);
        } else {
            echo "Unable to create file: " . $localFilePath;
            throw new Exception('Failed to save attachment: ' . $localFilePath);
        }

        $fileContents = $this->read($filepath);
        if ($fileContents !== false) {
            file_put_contents($localFilePath, $fileContents);

        } else {
            Mage::log('FTP file read failed for file: ' . $filepath, null, 'ftp_errors.log');
            throw new Exception('Failed to save attachment: ' . $filepath);
        }
        $this->saveFileToDb($filepath);
        $this->moveFile($filepath);
    }
    // public function moveFile($filepath) {
    //     $pathInfo = pathinfo($filepath);
    //     $filename = $pathInfo['basename'];
    //     $destinationPath = 'downloadedfiles' . DS . $filename;

    //     if (!is_dir('downloadedfiles')) {
    //         mkdir('downloadedfiles', 0777, true);
    //     }

    //     echo "Moving file from $filepath to $destinationPath\n";

    //     $moveResult = $this->mv( $filepath, $destinationPath);
    //     if ($moveResult) {
    //         echo "File moved successfully to $destinationPath\n";
    //     } else {
    //         echo "Failed to move file: $filepath to $destinationPath\n";
    //         $error = error_get_last();
    //         echo "Error details: " . $error['message'] . "\n";
    //         Mage::log('FTP move failed for file: ' . $filepath . ' to ' . $destinationPath . '. Error: ' . $error['message'], null, 'ftp_errors.log');
    //         throw new Exception('Failed to move file: ' . $filepath);
    //     }

    //     // Check if the file still exists at the source
    //     if (file_exists($filepath)) {
    //         echo "File still exists at source: $filepath\n";
    //     } else {
    //         echo "File no longer exists at source: $filepath\n";
    //     }

    //     // Remove the file if it was successfully moved
    //     if ($moveResult) {
    //         $this->rm($filepath);
    //     }
    // }



    public function moveFile($filepath)
    {
        $pathInfo = pathinfo($filepath);
        $filename = $pathInfo['basename'];
        var_dump($filename);
        $destinationPath = 'downloadedfiles/' . $filename;
        var_dump($destinationPath);

        if (!is_dir('downloadedfiles')) {
            mkdir('downloadedfiles', 0777, true);
        }

        $moveResult = $this->mv($filepath, $destinationPath);
        if ($moveResult) {
            echo "File moved successfully to $destinationPath\n";
            $this->rm($filepath);
        } else {
            $error = error_get_last();
            echo "Error details: " . $error['message'] . "\n";
            Mage::log('FTP move failed for file: ' . $filepath . ' to ' . $destinationPath . '. Error: ' . $error['message'], null, 'ftp_errors.log');
            throw new Exception('Failed to move file: ' . $filepath);
        }
    }


    public function saveFileToDb($filepath)
    {
        $creationDate = $this->getFileCreationDate($filepath);
        $configurationId = $this->_configData->getId();
        $fileModel = Mage::getModel('ccc_filetransfer/filetransfer');
        $fileData = $fileModel->setFilePath($filepath)
            ->setConfigurationId($configurationId)
            ->setFileDate($creationDate)
            ->save();
        var_dump($fileData->getData());
    }

    public function getProperFileName($filepath)
    {
        $configurationId = $this->_configData->getId();
        $creationDate = $this->getFileCreationDate($filepath);
        $newDate = str_replace(" ", "_", $creationDate);
        $fileNewPath = str_replace("./", '', $filepath);
        $fileNewPath = $configurationId . '_' . $newDate . '_' . $fileNewPath;
        $filename = preg_replace('/[^\w\-\.]/', '_', $fileNewPath);
        // var_dump($filename);
        return $filename;
    }

    protected function getFileCreationDate($filepath)
    {
        $lastModifiedTime = ftp_mdtm($this->_conn, $filepath);
        // var_dump($lastModifiedTime);
        if ($lastModifiedTime === -1) {
            var_dump('can not get date');
            return null;
        } else {
            $creationDate = date('Y-m-d H:i:s', $lastModifiedTime);
            $creationDate = str_replace(" ", "_", $creationDate);
            return $creationDate;
        }
    }

    protected function recursiveReadAndSaveDirectory($directory)
    {
        $contents = $this->ls($directory);

        foreach ($contents as $item) {
            $itemPath = $directory . DS . $item['text'];

            if ($this->isDirectory($itemPath)) {
                $this->recursiveReadAndSaveDirectory($itemPath);
            } else {
                $this->saveAndDownloadFiles($itemPath);
            }
        }
    }



}