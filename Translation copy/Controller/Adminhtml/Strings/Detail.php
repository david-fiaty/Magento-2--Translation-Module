<?php
/**
 *
 * Copyright © 2015 Naxerocommerce. All rights reserved.
 */
namespace Naxero\Translation\Controller\Adminhtml\Strings;

use Magento\Framework\File\Csv;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Naxero\Translation\Model\FileEntityFactory;

class Detail extends Action
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

            // Get the file id from request
            $fileId = $this->getRequest()->getParam('file_id');

            // Load the requested item
            $fileEntity->load($fileId);

            //Convert to array
            $csvString = $fileEntity->getData('file_path');
            $csvData = $this->csvParser->getData($csvString);
            
            foreach ($csvData as $row) {
                $output[] = (object) array_combine(['key', 'value'], $row);
            }
        }

        return $result->setData($output);
    }
}
