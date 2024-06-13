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
    // protected function isDirectory($filepath)
    // {
    //     $originalDir = ftp_pwd($this->_conn);
    //     if (ftp_chdir($this->_conn, $filepath)) {
    //         ftp_chdir($this->_conn, $originalDir);
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }
    // protected function isDirectory($filepath)
    // {
    //     $filepath = $filepath['text'];
    //     $filepath = rtrim($filepath, '/');
    //     // var_dump($filepath);
    //     $listing = ftp_rawlist($this->_conn, $filepath);
    //     // var_dump($listing);
    //     if ($listing === false || count($listing) === 0) {
    //         return false;
    //     }

    //     foreach ($listing as $item) {
    //         $parts = preg_split('/\s+/', $item);
    //         $fileName = count($parts);
    //         // var_dump($parts[$fileName-1]);
    //         if (isset($parts[0]) && $parts[0][0] === 'd') {
    //             // return $parts[$fileName-1];
    //             return true;
    //         }
    //     }
    //     return false;
    // }

    // public function readAndSave($filepath)
    // {
    //     // print_R($filepath); 
    //     if ($folderName = $this->isDirectory($filepath)) {
    //         $folderName = $filepath['text'].'/'.$folderName;
    //         // var_dump($folderName);
    //         $this->recursiveReadAndSaveDirectory($folderName);
    //     } else {
    //         $this->saveAndDownloadFiles($filepath);
    //     }
    // }

  
    protected function moveFile($source, $destination)
    {
        if (!rename($source, $destination)) {
            Mage::log('Failed to move file from ' . $source . ' to ' . $destination, null, 'ftp_errors.log');
            throw new Exception('Failed to move file: ' . $source);
        }
    }
    // protected function isDirectory($filepath)
    // {

    //     return is_dir($filepath);
    // }
    

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

   public function getProperFileName($filepath, $configId = '', $date = "")
{
    $unzipFilePath = strpos($filepath, Mage::getBaseDir('var')) !== false;
    if($unzipFilePath){
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
        $lastModifiedTime = ftp_mdtm($this->_conn, $filepath);
        if ($lastModifiedTime === -1) {
            return null;
        } else {
            $creationDate = date('Y-m-d H:i:s', $lastModifiedTime);
            return $creationDate;
        }
    }

    // public function recursiveReadAndSaveDirectory($directoryPath)
    // {
    //     // echo "recursiveReadAndSaveDirectory called with directory: " . $directoryPath . "\n";
    //     // var_dump($directoryPath);
    //     $fileList = ftp_nlist($this->_conn, $directoryPath);
    //     foreach ($fileList as $file) {
    //         echo '<br>';
    //         print_r($file);
    //         if ($file == '.' || $file == '..') {
    //             continue;
    //         }

    //         $fullPath = rtrim($directoryPath, '/') . '/' . ltrim($file, '/');
    //         var_dump($fullPath);
    //         if ($this->isDirectory($fullPath)) {
    //             echo "Recursively processing directory: " . $fullPath . "\n";
    //             $this->recursiveReadAndSaveDirectory($fullPath);
    //         } else {
    //             echo "Processing file: " . $fullPath . "\n";
    //             $this->saveAndDownloadFiles(['text' => $fullPath]);
    //         }
    //     }
    // }


//     protected function isDirectory($filepath)
// {
//     // Ensure the filepath is not root or parent directory references
//     if ($filepath == '.' || $filepath == '..') {
//         return false;
//     }
    
//     // Check if the listing contains subdirectories
//     $listing = ftp_nlist($this->_conn, $filepath);
//     if ($listing === false || count($listing) === 0) {
//         return false;
//     }
    
//     // If listing contains entries, it's a directory
//     return true;
// }

// protected function listDirectory($directory)
// {
//     $files = array();
//     $listing = ftp_nlist($this->_conn, $directory);
//     foreach ($listing as $item) {
//         if ($item !== '.' && $item !== '..') {
//             $files[] = array('text' => $item);
//         }
//     }
//     return $files;
// }

// public function saveAndDownloadFiles($file)
// {
//     if (is_array($file)) {
//         $filepath = $file['text'];
//     } else {
//         $filepath = $file;
//     }

//     if ($this->isDirectory($filepath)) {
//         // Replace "./" with an empty string to clean up the filepath
//         $fileNewPath = str_replace("./", '', $filepath);
//         $localFilePath = Mage::getBaseDir('var') . DS . 'filetransfer' . DS . $this->_configData->getId() . DS . $fileNewPath;
        
//         // Create local directory if it doesn't exist
//         if (!file_exists($localFilePath)) {
//             mkdir($localFilePath, 0777, true);
//         }

//         // Recursively process all files and directories within the current directory
//         $remoteFiles = $this->listDirectory($filepath);
//         foreach ($remoteFiles as $remoteFile) {
//             $this->saveAndDownloadFiles($remoteFile);
//         }
//     } else {
//         // Process file download and saving logic here
//         $filename = $this->getProperFileName($filepath);
//         $localFilePath = Mage::getBaseDir('var') . DS . 'filetransfer' . DS . $this->_configData->getId() . DS . $filename;

//         // Create directory if it doesn't exist
//         $directory = dirname($localFilePath);
//         if (!is_dir($directory)) {
//             mkdir($directory, 0777, true);
//         }

//         // Download file
//         $fileContents = $this->read($filepath);
//         if ($fileContents !== false) {
//             file_put_contents($localFilePath, $fileContents);
//         } else {
//             Mage::log('FTP file read failed for file: ' . $filepath, null, 'ftp_errors.log');
//             throw new Exception('Failed to save attachment: ' . $filepath);
//         }

//         // Save file to database
//         // $this->saveFileToDb($filepath);

//         // Move the file to destination directory
//         // $this->moveFile($filepath, $localFilePath);
//     }
// }


protected function isDirectory($filepath)
{
    if ($filepath == '.' || $filepath == '..') {
        return false;
    }
    
    $listing = ftp_nlist($this->_conn, $filepath);
    if ($listing === false || count($listing) === 0) {
        return false;
    }
    
    foreach ($listing as $item) {
        if (strpos($item, $filepath) === 0 && $item !== $filepath) {
            return true;
        }
    }
    return false;
}

protected function listDirectory($directory)
{
    $files = array();
    $listing = ftp_nlist($this->_conn, $directory);
    foreach ($listing as $item) {
        if ($item !== '.' && $item !== '..') {
            $files[] = array('text' => $item);
        }
    }
    return $files;
}

public function saveAndDownloadFiles($file)
{
    if (is_array($file)) {
        $filepath = $file['text'];
    } else {
        $filepath = $file;
    }

    if ($this->isDirectory($filepath)) {
        $fileNewPath = str_replace("./", '', $filepath);
        $localDirPath = Mage::getBaseDir('var') . DS . 'filetransfer' . DS . $this->_configData->getId() . DS . $fileNewPath;
        
        if (!file_exists($localDirPath)) {
            mkdir($localDirPath, 0777, true);
        }

        $remoteFiles = $this->listDirectory($filepath);
        foreach ($remoteFiles as $remoteFile) {
            $this->saveAndDownloadFiles($remoteFile);
        }
    } else {
        $fileNewPath = str_replace("./", '', $filepath);
        $localFilePath = Mage::getBaseDir('var') . DS . 'filetransfer' . DS . $this->_configData->getId() . DS . $fileNewPath;

        $directory = dirname($localFilePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $fileContents = $this->read($filepath);
        if ($fileContents !== false) {
            file_put_contents($localFilePath, $fileContents);
        } else {
            Mage::log('FTP file read failed for file: ' . $filepath, null, 'ftp_errors.log');
            throw new Exception('Failed to save attachment: ' . $filepath);
        }

        $this->saveFileToDb($filepath);
        $this->moveFile($filepath, $localFilePath);
    }
}




}
