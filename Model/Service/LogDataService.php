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

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(print_r($this->output, 1));

        // Return the data output
        return $this->output;
    }
}
