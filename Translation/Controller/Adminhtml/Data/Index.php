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
use Naxero\Translation\Helper\Data;
use Naxero\Translation\Model\Service\FileDataService;

class Index extends Action
{
	/**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var FileDataService
     */
    protected $fileDataService;    

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
        FileDataService $fileDataService,
        Data $helper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fileDataService = $fileDataService;
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
            $output = $this->fileDataService->getList();

            // Return a JSON output
            return $result->setData($output);
        }

        return $result->setData([]);
    }
}
