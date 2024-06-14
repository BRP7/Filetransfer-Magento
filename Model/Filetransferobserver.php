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
        $originalDir = ftp_pwd($this->_conn);
        if (@ftp_chdir($this->_conn, $filepath)) {
            ftp_chdir($this->_conn, $originalDir);
            return true;
        } else {
            return false;
        }
    }

    protected function listDirectory($directory)
    {
        $files = array();
        $listing = ftp_nlist($this->_conn, $directory);
        foreach ($listing as $item) {
            $basename = basename($item);
            if ($basename !== '.' && $basename !== '..') {
                $files[] = array('text' => $basename, 'path' => $directory . '/' . $basename);
            }
        }
        return $files;
    }

    public function saveAndDownloadFiles($filepath)
    {
        if ($this->isDirectory($filepath)) {
            $remoteFiles = $this->listDirectory($filepath);
            foreach ($remoteFiles as $remoteFile) {
                $this->saveAndDownloadFiles($remoteFile['path']);
            }
        } else {
            $fileNewPath = $this->getProperFileName($filepath);
            $localFilePath = Mage::getBaseDir('var') . DS . 'filetransfer' . DS . $this->_configData->getId() . DS . $fileNewPath;

            // Debugging: print file paths
            echo "Processing file: " . $filepath . PHP_EOL;
            echo "New file path: " . $fileNewPath . PHP_EOL;
            echo "Local file path: " . $localFilePath . PHP_EOL;

            $directory = dirname($localFilePath);
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0777, true)) {
                    echo "Failed to create directory: " . $directory . PHP_EOL;
                } else {
                    echo "Created directory: " . $directory . PHP_EOL;
                }
            }

            $fileContents = $this->read($filepath);
            if ($fileContents !== false) {
                if (!file_put_contents($localFilePath, $fileContents)) {
                    echo "Failed to save file: " . $localFilePath . PHP_EOL;
                } else {
                    echo "File saved: " . $localFilePath . PHP_EOL;
                }
            } else {
                echo "Failed to read FTP file: " . $filepath . PHP_EOL;
            }

            $this->saveFileToDb($fileNewPath);
        }
    }

    protected function getProperFileName($filepath)
    {
        $configId = $this->_configData->getId();
        $creationDate = $this->getFileCreationDate($filepath);
        if ($creationDate === '0000-00-00_00-00-00') {
            $creationDate = date('Y-m-d_H-i-s');
        }
        $creationDate = str_replace(" ", "_", $creationDate);

        $directoryPath = dirname($filepath);
        $fileName = basename($filepath);

        // Construct the new file name
        $newFileName = $configId . '_' . $creationDate . '_' . $fileName;

        // Debugging: print file naming details
        echo "Original file path: " . $filepath . PHP_EOL;
        echo "Directory path: " . $directoryPath . PHP_EOL;
        echo "File name: " . $fileName . PHP_EOL;
        echo "New file name: " . $newFileName . PHP_EOL;

        $newFilePath = trim($directoryPath, './') . '/' . $newFileName;
        $newFilePath = str_replace('//', '/', $newFilePath); // Normalize path

        return $newFilePath;
    }

    protected function getFileCreationDate($filepath)
    {
        $lastModifiedTime = ftp_mdtm($this->_conn, $filepath);
        if ($lastModifiedTime === -1) {
            return '0000-00-00_00-00-00';
        } else {
            return date('Y-m-d_H-i-s', $lastModifiedTime);
        }
    }

    public function saveFileToDb($filepath)
    {
        $creationDate = $this->getFileCreationDate($filepath);
        $configurationId = $this->_configData->getId();
        $fileModel = Mage::getModel('ccc_filetransfer/filetransfer');
        $fileModel->setFilePath($filepath)
            ->setConfigurationId($configurationId)
            ->setFileDate($creationDate)
            ->save();
    }
}
