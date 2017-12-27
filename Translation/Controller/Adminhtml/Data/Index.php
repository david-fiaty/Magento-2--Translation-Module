<?php
/**
 *
 * Copyright © 2018 David Fiaty. All rights reserved.
 */
namespace Naxero\Translation\Controller\Adminhtml\Data;

use Magento\Framework\File\Csv;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Naxero\Translation\Model\FileEntityFactory;

class Index extends Action
{

	/**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var FileEntityFactory
     */
    protected $fileEntityFactory;    

    /**
     * @var Csv
     */
    protected $csvParser;

    /**
     * @var Array
     */
    protected $output;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        FileEntityFactory $fileEntityFactory,
        Csv $csvParser
    ) {
        $this->resultJsonFactory            = $resultJsonFactory;
        $this->fileEntityFactory = $fileEntityFactory;
        $this->csvParser = $csvParser;
        $this->output = $this->prepareOutputArray();

        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        if ($this->getRequest()->isAjax()) 
        {
            // Get the factory
            $fileEntity = $this->fileEntityFactory->create(); 

            // Create the collection
            $collection = $fileEntity->getCollection();

            // Prepare the output array
            foreach($collection as $item)
            {
                // Get the item data
                $arr = $item->getData();

                // Prepare the fields
                $arr = $this->getFieldFormats($arr, $item);
                $arr = $this->getSortingFields($arr);

                // Store the item as an object
                $this->output['table_data'][] = (object) $arr;
            }
        }

        // Remove duplicate filters
        $this->removeDuplicateFilterValues();

        // Return a JSON output
        return $result->setData($this->output);
    }

    protected function prepareOutputArray() {
        return [
            'table_data' => [],
            'filter_data' => [
                'file_type' => [], 
                'file_group' => [], 
                'file_locale' => [], 
                'file_status' => [
                    __('Active'),
                    __('Error')
                ]
            ]
        ];
    }

    protected function removeDuplicateFilterValues() {
        $this->output['filter_data']['file_type'] = sort(array_unique($this->output['filter_data']['file_type']));
        $this->output['filter_data']['file_group'] = sort(array_unique($this->output['filter_data']['file_group']));
        $this->output['filter_data']['file_locale'] = sort(array_unique($this->output['filter_data']['file_locale']));
    }

    protected function getFieldFormats($arr, $fileEntity) {
        // Cast the id field to integer
        $arr['file_id'] = (int) $arr['file_id'];

        // Set the CSV row count
        $arr['file_count'] = $this->countCSVRows($fileEntity->getData('file_path'));

        // Unset the content field. Todo : better to refine query
        unset($arr['file_content']);

        // Set the language field
        $arr['file_locale'] =  basename($arr['file_path'], '.csv');

        return $arr;
    }

    protected function getSortingFields($arr) {

        $metadata = $this->scanPath($arr);

        return array_merge($arr, $metadata);
    }

    protected function scanPath($arr) {
        $path = $arr['file_path'];

        // Todo : detect themes in vendor folder
        if (
            strpos($path, 'vendor/magento') === 0) {
            $arr['file_type'] = __('Module');
            $arr['file_group'] = __('Core');
        }
        else if (strpos($path, 'app/code') === 0) {
            $arr['file_type'] = __('Module');
            $arr['file_group'] = __('Community');
        }
        else if (strpos($path, 'dev/') === 0) {
            $arr['file_type'] = __('Dev');
            $arr['file_group'] = __('Dev');
        }
        else if (strpos($path, 'app/design/frontend/Magento') === 0) {
            $arr['file_type'] = __('Theme');
            $arr['file_group'] = __('Core');
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

    protected function countCSVRows($csvPath) {
        // Parse the string
        $csvData = $this->csvParser->getData($csvPath);

        // Return the row count
        return count($csvData);
    }
}
