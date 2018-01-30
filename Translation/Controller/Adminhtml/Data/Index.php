<?php
/**
 *
 * Copyright © 2018 David Fiaty. All rights reserved.
 */
namespace Naxero\Translation\Controller\Adminhtml\Data;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Naxero\Translation\Model\FileEntityFactory;
use Naxero\Translation\Helper\Data;

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
        Context $context,
        JsonFactory $resultJsonFactory,
        FileEntityFactory $fileEntityFactory,
        Data $helper
    ) {
        $this->resultJsonFactory            = $resultJsonFactory;
        $this->fileEntityFactory = $fileEntityFactory;
        $this->output = $this->prepareOutputArray();
        $this->helper = $helper;

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
                $arr = $this->helper->getFieldFormats($arr, $item);
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
        // Remove duplicates
        $this->output['filter_data']['file_type'] = array_unique($this->output['filter_data']['file_type']);
        $this->output['filter_data']['file_group'] = array_unique($this->output['filter_data']['file_group']);
        $this->output['filter_data']['file_locale'] = array_unique($this->output['filter_data']['file_locale']);

        // Sort fields
        sort($this->output['filter_data']['file_type']);
        sort($this->output['filter_data']['file_group']);
        sort($this->output['filter_data']['file_locale']);
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
}
