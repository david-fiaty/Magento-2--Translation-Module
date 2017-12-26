<?php
/**
 *
 * Copyright © 2015 Naxerocommerce. All rights reserved.
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

        $output = array();

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
                $arr = $this->getFieldFormats($arr);
                $arr = $this->getSortingFields($arr);

                // Store the item as an object
                $output[] = (object) $arr;
            }
        }

        // Return a JSON output
        return $result->setData($output);
    }

    protected function getFieldFormats($arr) {
        // Cast the id field to integer
        $arr['file_id'] = (int) $arr['file_id'];

        // Set the CSV row count
        //$arr['file_count'] =  $this->countCSVRows($arr['file_content']);
        $arr['file_count'] = '110';

        // Unset the content field. Todo : better to refine query
        unset($arr['file_content']);

        // Set the language field
        $arr['file_locale'] =  basename($arr['file_path'], '.csv');

        return $arr;
    }

    protected function getSortingFields($arr) {

        $metadata = $this->scanPath($arr['file_path']);

        return array_merge($arr, $metadata);
    }

    protected function scanPath($path) {
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

        return $arr;
    }   

    protected function countCSVRows($csvString) {
        // Parse the string
        $csvData = $this->csvParser->getData($csvString);

        // Return the row count
        return count($csvData);
    }
}
