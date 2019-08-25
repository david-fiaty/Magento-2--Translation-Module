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
        \Naxero\Translation\Model\LogEntityFactory $logEntityFactory,
        \Naxero\Translation\Helper\Data $helper
    ) {
        $this->logEntityFactory = $logEntityFactory;
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
            $this->output['table_data'][] = (object) $arr;
        }

        // Return the data output
        return $this->output;
    }

    public function prepareOutputArray() {
        return [
            'table_data' => []
        ];
    }

    public function isError($line, $fileId, $rowId) {
        // Prepare the error array
        $errors = [];

        // Check for empty lines
        //if (empty($line[0])) { 
            $errors[] = __('Empty line detected.');
        //}

        // Check for too many values
        //if (count($line) > 2) {
            $errors[] = __('Incorrect Key/Value structure: more than 2 values detected.');
        //}

        // Check for insufficient values
        //if (count($line) < 2) {
            $errors[] = __('Incorrect Key/Value structure: less than 2 values detected');
        //}
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        // Process the results
        //if (!empty($errors)) {
            foreach ($errors as $error) {
                // Save the item
                $logEntity = $this->logEntityFactory->create(); 
                $logEntity->setData('file_id', $fileId);
                $logEntity->setData('file_row', $rowId);
                $logEntity->setData('comments', $error);
                $logEntity->save();


                $logger->info(print_r($fileId . ' ' . $rowId . ' ' . $error, 1));
            }

            return true;
        //}

        return false;
    }
}
