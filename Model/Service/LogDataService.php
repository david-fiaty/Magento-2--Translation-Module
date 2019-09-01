<?php
/**
 *
 * Copyright © 2018 David Fiaty. All rights reserved.
 */
namespace Naxero\Translation\Model\Service;

class LogDataService
{
    /**
     * @var LogEntityFactory
     */
    public $logEntityFactory;    

    /**
     * @var FileEntityFactory
     */
    public $fileEntityFactory;  

    /**
     * @var Array
     */
    public $output;

    /**
     * @var Data
     */
    public $helper;

    /**
     * LogDataService constructor
     */
    public function __construct(
        \Naxero\Translation\Model\LogEntityFactory $logEntityFactory,
        \Naxero\Translation\Model\FileEntityFactory $fileEntityFactory,
        \Naxero\Translation\Helper\Data $helper
    ) {
        $this->logEntityFactory = $logEntityFactory;
        $this->fileEntityFactory = $fileEntityFactory;
        $this->helper = $helper;
    }

    public function init() {
        // Prepare the output array
        $this->output = $this->prepareOutputArray();

        return $this;
    }

    public function getError() {
        return [
            __('Empty line detected.'),
            __('Incorrect Key/Value structure: more than 2 values detected.'),
            __('Incorrect Key/Value structure: less than 2 values detected.'),
            __('The file is not readable.'),
            __('The file is not writable.')
        ];
    }
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function getList()
    {
        // Get the factory
        $logEntity = $this->logEntityFactory->create(); 

        // Create the collection
        $collection = $logEntity->getCollection();

        // Process the logs
        foreach ($collection as $item)
        {
            // Get the item data
            $arr = $item->getData();
            
            // Load the file instance
            $fileEntity = $this->fileEntityFactory->create();
            $fileInstance = $fileEntity->load($arr['file_id']);

            // Add the row index
            $arr['index'] = $arr['row_id'] + 1;

            // Process the file
            $filePath = $fileInstance->getFilePath();
            if (!$this->helper->excludeFile($filePath) && !empty($filePath)) {
                // Add the file path field
                $arr['file_path'] = $filePath;

                // Format the errors
                if (!empty($arr['comments'])) {
                    $errors = json_decode($arr['comments']);
                    $arr['comments'] = '';
                    foreach ($errors as $errorId) {
                        $arr['comments'] .= $this->getError()[$errorId] . PHP_EOL;
                    }
                }

                // Add to output
                $this->output['table_data'][] = (object) $arr;
            }
        }

        // Return the data output
        return $this->output;
    }

    public function prepareOutputArray() {
        return [
            'table_data' => []
        ];
    }

    public function shoudHideRow($isLogView) {
        $hideInvalidRows = $this->helper->getConfig('hide_invalid_rows');

        return $hideInvalidRows && !$isLogView;
    }

    public function isFilexists($path) {
        try {
            return file_exists($path);
        }
        catch (\Exception $e) {
            return false;
        }
    }

    public function isReadable($path) {
        try {
            return is_readable($path);
        }
        catch (\Exception $e) {
            return false;
        }
    }

    public function isWritable($path) {
        try {
            return is_writable($path);
        }
        catch (\Exception $e) {
            return false;
        }
    }

    public function hasErrors($fileId, $line, $rowId) {
        // Prepare the error array
        $errors = [];

        // Check for empty lines
        if (empty($line[0])) { 
            $errors[] = 0;
        }

        // Check for too many values
        if (count($line) > 2) {
            $errors[] = 1;
        }

        // Check for insufficient values
        if (count($line) < 2) {
            $errors[] = 2;
        }

        // Process the results
        if (!empty($errors)) {
            foreach ($errors as $errorId) {
                $this->createLog($errorId, $fileId, $rowId);
            }

            return true;
        }

        return false;
    }

    public function createLog($errorId, $fileId, $rowId = null) {
        // Check if the error already exists
        $collection = $this->logEntityFactory->create()->getCollection();
        $collection->addFieldToFilter('file_id', $fileId);

        // Add the row id if exists
        if ($rowId) {
            $collection->addFieldToFilter('row_id', $rowId);
        }

        // Create a new error or update an existing row
        if ($collection->getSize() < 1) {
            $logEntity = $this->logEntityFactory->create();
            $logEntity->setData('file_id', $fileId);
            $logEntity->setData('row_id', $rowId);
            $logEntity->setData('comments', json_encode([$errorId]));
            $logEntity->save();
        }
        else {
            foreach ($collection as $item)
            {
                // Load the existing row
                $logEntity = $this->logEntityFactory->create();
                $logInstance = $logEntity->load($item->getData('id'));

                // Create the new comments
                $newContent  = json_decode($logInstance->getData('comments'));
                if (!in_array($errorId, $newContent)) {
                    array_push($newContent, $errorId);
                }

                // Save the entity
                $logInstance->setData('comments', json_encode($newContent));
                $logInstance->setData('row_id', $rowId);
                $logInstance->save();
            }
        }
    }
}
