<?php
/**
 *
 * Copyright © 2018 David Fiaty. All rights reserved.
 */
namespace Naxero\Translation\Model\Service;

use Naxero\Translation\Model\FileEntityFactory;
use Naxero\Translation\Helper\Data;

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
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        FileEntityFactory $fileEntityFactory,
        Data $helper
    ) {
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
        $fileEntity = $this->fileEntityFactory->create(); 

        // Create the collection
        $collection = $fileEntity->getCollection();

        // Process the files content
        $i = 0;
        foreach ($collection as $item)
        {
            // Get the item data
            $arr = $item->getData();

            // Build the sorting fields
            $arr = $this->buildSortingFields($arr);

            // Get the content rows
            $rows = explode("\n", $arr['file_content']);
            unset($arr['file_content']);

            // Set the language field
            $arr['file_locale'] =  basename($arr['file_path'], '.csv');

            // Loop through the rows
            foreach ($rows as $row) {
                // Prepare the output array
                $output = [];

                // Get the line
                $line = str_getcsv($row);

                // Skip empty and non pair values
                if (!empty($line[0]) && count($line) == 2) {
                    $output = array_merge([
                        'index' => $i,
                        'key' => $line[0],
                        'value' => $line[1]
                    ], $arr);

                    // Store the item as an object
                    $this->output['table_data'][] = (object) $output;

                    // Increment the index
                    $i++;
                }
            }
        }

        // Remove duplicate filters
        $this->removeDuplicateFilterValues();

        // Return the data output
        return $this->output;
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

    public function removeDuplicateFilterValues() {
        // Remove duplicates
        $this->output['filter_data']['file_type'] = array_unique($this->output['filter_data']['file_type']);
        $this->output['filter_data']['file_group'] = array_unique($this->output['filter_data']['file_group']);
        $this->output['filter_data']['file_locale'] = array_unique($this->output['filter_data']['file_locale']);

        // Sort fields
        sort($this->output['filter_data']['file_type']);
        sort($this->output['filter_data']['file_group']);
        sort($this->output['filter_data']['file_locale']);
    }

    public function buildSortingFields($arr) {
        $metadata = $this->scanPath($arr);

        return array_merge($arr, $metadata);
    }

    public function scanPath($arr) {
        $path = $arr['file_path'];

        // Todo : detect themes in vendor folder
        if (strpos($path, 'vendor/magento') === 0) {
            $arr['file_type'] = __('Module');
            $arr['file_group'] = __('Core');
        }
        else if (strpos($path, 'app/code') === 0) {
            $arr['file_type'] = __('Module');
            $arr['file_group'] = __('Community');
        }
        else if (strpos($path, 'dev/tests/') === 0) {
            $arr['file_type'] = __('Test');
            $arr['file_group'] = __('Dev');
        }
        else if (strpos($path, 'app/design/frontend/Magento') === 0) {
            $arr['file_type'] = __('Theme');
            $arr['file_group'] = __('Core');
        }
        else if (strpos($path, 'pub/static') === 0) {
            $arr['file_type'] = __('Theme');
            $arr['file_group'] = __('Static');
        }
        else if (strpos($path, 'lib/') === 0) {
            $arr['file_type'] = __('Web');
            $arr['file_group'] = __('Library');
        }        
        else if (strpos($path, 'app/design/frontend/') === 0
                && strpos($path, 'app/design/frontend/Magento') === false) {
            $arr['file_type'] = __('Theme');
            $arr['file_group'] = __('Community');
        }
        else if (
            strpos($path, 'vendor/') === 0 
            && strpos($path, 'vendor/magento') === false
        ) {
            $arr['file_type'] = __('Module');
            $arr['file_group'] = __('Vendor');
        }
        else {
            $arr['file_type'] = __('Other');
            $arr['file_group'] = __('Undefined');
        }

        // Add type filter data
        $this->output['filter_data']['file_type'][] = $arr['file_type'];

        // Add group filter data
        $this->output['filter_data']['file_group'][] = $arr['file_group'];

        // Add locale filter data
        $this->output['filter_data']['file_locale'][] = basename($path, '.csv');

        return $arr;
    }   
}
