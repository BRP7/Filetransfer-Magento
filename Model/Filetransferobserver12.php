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
        $filepath = $filepath['text'];
        $filepath = rtrim($filepath, '/');
        var_dump($filepath);
        $listing = ftp_rawlist($this->_conn, $filepath);
        var_dump($listing);
        if ($listing === false || count($listing) === 0) {
            return false;
        }

        foreach ($listing as $item) {
            $parts = preg_split('/\s+/', $item);
            $fileName = count($parts);
            // var_dump($parts[$fileName-1]);
            if (isset($parts[0]) && $parts[0][0] === 'd') {
                return $parts[$fileName-1];
            }
        }
        return false;
    }

   

    public function saveAndDownloadFiles($file)
    {
        $filepath = $file['text'];
        var_dump($filepath);
        $filename = $this->getProperFileName($filepath);
        $localFilePath = Mage::getBaseDir('var') . DS . 'filetransfer' . DS . $this->_configData->getId() . DS . $filename;

        $directory = dirname($localFilePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        } elseif (!is_writable($directory)) {
            chmod($directory, 0777);
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

    public function moveFile($filepath)
    {
        $directoryPath = dirname($filepath);
        if (strpos($directoryPath, 'downloadedfiles') !== false) {
            $directoryPath = substr($directoryPath, strpos($directoryPath, 'downloadedfiles') + strlen('downloadedfiles') + 1);
        }
        $pathInfo = pathinfo($filepath);
        $filePath = str_replace('./','',$directoryPath);
        $filePath = str_replace('/.','\\',$filePath);

        $destinationDirectory =  'downloadedfiles'.DS. $filePath;

        if (!is_dir($destinationDirectory)) {
            mkdir($destinationDirectory, 0777, true);
        }


        $moveResult = $this->mv($filePath, $destinationDirectory);
        if ($moveResult) {
            echo "File moved successfully to $destinationDirectory\n";
            $this->rm($filepath);
        } else {
            echo "Failed to move file: $filePath to $destinationDirectory\n";
            $error = error_get_last();
            echo "Error details: " . $error['message'] . "\n";
            Mage::log('FTP move failed for file: ' . $filepath . ' to ' . $destinationDirectory . '. Error: ' . $error['message'], null, 'ftp_errors.log');
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
    }

    public function getProperFileName($filepath)
    {
        $configurationId = $this->_configData->getId();
        $creationDate = $this->getFileCreationDate($filepath);
        $newDate = str_replace(" ", "_", $creationDate);
        $fileNewPath = str_replace("./", '', $filepath);
        $fileNewPath = $configurationId . '_' . $newDate . '_' . $fileNewPath;
        $filename = preg_replace('/[^\w\-\.]/', '_', $fileNewPath);
        return $filename;
    }

    protected function getFileCreationDate($filepath)
    {
        $lastModifiedTime = ftp_mdtm($this->_conn, $filepath);
        if ($lastModifiedTime === -1) {
            return null;
        } else {
            $creationDate = date('Y-m-d H:i:s', $lastModifiedTime);
            return $creationDate;
        }
    }

    protected function recursiveReadAndSaveDirectory($directory)
    {
        $contents = $this->ls($directory);
        var_dump($directory);
        foreach ($contents as $item) {
            $itemName = $item['text'];
            if($itemName == "./downloadedfiles"){
                continue;
            }
            $itemPath = rtrim($directory, '/') . '/' . ltrim($itemName, '/');
            if ($this->isDirectory(['text' => $itemPath])) {
                $this->recursiveReadAndSaveDirectory(['text' => $itemPath]); 
            } else {
                $this->saveAndDownloadFiles($item); 
            }
        }
    }

}
