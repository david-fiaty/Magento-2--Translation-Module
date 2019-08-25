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
     * @param Context $context
     * @param PageFactory $resultPageFactory
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
                // Prepare the fields
                $arr = $this->formatFileRow($arr, $item);
                $arr = $this->buildSortingFields($arr);

                // Store the item as an object
                $this->output['table_data'][] = (object) $arr;
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

    public function formatFileRow($arr, $fileEntity) {
        // Cast the id field to integer
        $arr['file_id'] = (int) $arr['file_id'];

        // Set the CSV row count
        $arr['file_count'] = $this->helper->countCSVRows($fileEntity->getData('file_path'));

        // Unset the content field
        unset($arr['file_content']);

        // Set the language field
        $arr['file_locale'] =  basename($arr['file_path'], '.csv');

        return $arr;
    }	
}
