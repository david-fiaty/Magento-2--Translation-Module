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
    protected $logEntityFactory;    

    /**
     * @var FileEntityFactory
     */
    protected $fileEntityFactory;  

    /**
     * @var Array
     */
    protected $output;

    /**
     * @var Data
     */
    protected $helper;

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

            // Process the file
            $filePath = $fileInstance->getFilePath();
            if (!$this->helper->excludeFile($filePath)) {
                // Add the file path field
                $arr['file_path'] = $filePath;

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

    public function shoudHideRow($line, $fileId, $rowId, $isLogView) {
        $hideInvalidRows = $this->getConfig('hide_invalid_rows');

        return $hideInvalidRows
        && $this->hasErrors($line, $fileId, $rowId)
        && !$isLogView;
    }

    public function hasErrors($line, $fileId, $rowId) {
        // Prepare the error array
        $errors = [];

        // Check for empty lines
        if (empty($line[0])) { 
            $errors[] = __('Empty line detected.');
        }

        // Check for too many values
        if (count($line) > 2) {
            $errors[] = __('Incorrect Key/Value structure: more than 2 values detected.');
        }

        // Check for insufficient values
        if (count($line) < 2) {
            $errors[] = __('Incorrect Key/Value structure: less than 2 values detected');
        }

        // Process the results
        if (!empty($errors)) {
            foreach ($errors as $error) {
                // Check if the error already exists
                $collection = $this->logEntityFactory->create()->getCollection()
                ->addFieldToFilter('file_id', $fileId)
                ->addFieldToFilter('row_id', $rowId);

                // Create a new error or update an existing row
                if ($collection->getSize() < 1) {
                    $logEntity = $this->logEntityFactory->create();
                    $logEntity->setData('file_id', $fileId);
                    $logEntity->setData('row_id', $rowId);
                    $logEntity->setData('comments', $error);
                    $logEntity->save();
                }
                else {
                    foreach($collection as $item)
                    {
                        // Load the existing row
                        $logEntity = $this->logEntityFactory->create();
                        $logInstance = $logEntity->load($item->getData('id'));

                        // Create the new comments
                        $newContent  = $logInstance->getData('comments') . "\n";
                        $newContent .= $error;

                        // Save the entity
                        $logInstance->setData('comments', $newContent);
                        $logInstance->save();
                    }
                }
            }

            return true;
        }

        return false;
    }
}
