<?php
/**
 *
 * Copyright © 2018 David Fiaty. All rights reserved.
 */
namespace Naxero\Translation\Model\Service;

class StringDataService
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
     * StringDataService constructor
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

        // Process the files content
        $rowId = 0;
        foreach ($collection as $item)
        {
            // Get the item data
            $arr = $item->getData();
            if (!$this->helper->excludeFile($arr)) {
                // Build the sorting fields
                $sorting = $this->helper->buildSorting($arr, $this->output);
                $arr = $sorting['data'];
                $this->output = $sorting['filters'];

                // Get the content rows
                $rows = explode(PHP_EOL, $arr['file_content']);
                unset($arr['file_content']);

                // Set the language field
                $arr['file_locale'] =  basename($arr['file_path'], '.csv');

                // Loop through the rows
                $rowIndex = 1;
                foreach ($rows as $row) {
                    // Prepare the output array
                    $output = [];

                    // Get the line
                    $line = str_getcsv($row);

                    // Skip empty and non pair values
                    if (!$this->logDataService->hasErrors($arr['file_id'], $line, $rowId)) {
                        // Store the item as an object
                        $this->output['table_data'][] = (object) $this->buildRow(
                            $line,
                            $rowIndex,
                            $arr
                        );

                        // Increment the index
                        $rowIndex++;
                    }
                    else if ($this->logDataService->hasErrors($arr['file_id'], $line, $rowId)
                    && !$this->logDataService->shoudHideRow(false)) {
                        // Store the item as an object
                        $this->output['table_data'][] = (object) $this->buildErrorRow(
                            $line,
                            $rowIndex,
                            $arr
                        );

                        // Increment the row index
                        $rowIndex++;
                    }

                    // Increment the row id
                    $rowId++;
                }
            }
        }

        // Return the data output
        return $this->helper->removeDuplicateFilterValues($this->output);
    }

    public function buildRow($line, $rowIndex, $arr) {
        return array_merge([
            'index' => $rowIndex,
            'key' => $line[0],
            'value' => $line[1]
        ], $arr);
    }

    public function buildErrorRow($line, $rowIndex, $arr) {
        $errorLine = [];
        $errorLine['index'] = $rowIndex;
        $errorLine['key'] = isset($line[0]) ? $line[0] : '';
        $errorLine['value'] = isset($line[1]) ? $line[1] : '';

        return array_merge($errorLine, $arr);
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
}
