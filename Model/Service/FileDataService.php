<?php
/**
 *
 * Copyright © 2018 David Fiaty. All rights reserved.
 */
namespace Naxero\Translation\Model\Service;

class FileDataService
{
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
     * @var LogDataService
     */
    protected $logDataService;

    /**
     * FileDataService constructor
     */
    public function __construct(
        \Naxero\Translation\Model\FileEntityFactory $fileEntityFactory,
        \Naxero\Translation\Helper\Data $helper,
        \Naxero\Translation\Model\Service\LogDataService $logDataService
    ) {
        $this->fileEntityFactory = $fileEntityFactory;
        $this->helper = $helper;
        $this->logDataService = $logDataService;
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
        $fileEntity = $this->fileEntityFactory->create(); 

        // Create the collection
        $collection = $fileEntity->getCollection();

        // Prepare the output array
        foreach ($collection as $item)
        {
            // Get the item data
            $arr = $item->getData();

            // Process the file
            if (!$this->helper->excludeFile($arr)) {
                // Get the permissions
                $isReadable = $this->logDataService->isReadable($arr['file_path']);
                $isWritable = $this->logDataService->isWritable($arr['file_path']);

                if ($isReadable) {
                    // Prepare the fields
                    $arr = $this->formatFileRow($arr, $item);
                    $arr = $this->helper->buildSortingFields($arr, $this->output);

                    // Get the content rows
                    $rows = explode(PHP_EOL, $arr['file_content']);

                    // Loop through the rows
                    $rowId = 0;
                    foreach ($rows as $row) {
                        // Get the line
                        $line = str_getcsv($row);

                        // Check the file content
                        $this->logDataService->hasErrors($arr['file_id'], $line, $rowId);

                        // Increment the row id
                        $rowId++;
                    }
                }

                // Store the item as an object
                $this->output['table_data'][] = (object) $arr;
            }
        }

        // Return the data output
        return $this->helper->removeDuplicateFilterValues($this->output);
    }

    public function prepareOutputArray() {
        return [
            'table_data' => [],
            'filter_data' => [
                'file_type' => [], 
                'file_group' => [], 
                'file_locale' => [], 
                'file_status' => [
                    __('Error'),
                    __('Active')
                ]
            ]
        ];
    }

    public function formatFileRow($arr, $fileEntity) {
        // Cast the id field to integer
        $arr['file_id'] = (int) $arr['file_id'];

        // Set the CSV row count
        $arr['file_count'] = $this->helper->countCSVRows($fileEntity->getData('file_path'));

        // Set the language field
        $arr['file_locale'] =  basename($arr['file_path'], '.csv');

        return $arr;
    }	
}
