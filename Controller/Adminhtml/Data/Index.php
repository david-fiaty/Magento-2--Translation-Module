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
use Naxero\Translation\Model\Service\StringDataService;

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
     * @var StringDataService
     */
    protected $stringDataService;  

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
        StringDataService $stringDataService,
        Data $helper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fileDataService = $fileDataService;
        $this->stringDataService = $stringDataService;
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
        // Prepare the output array
        $output = [];

        // Get the view mode
        $mode = $this->getRequest()->getParam('mode');

        // Process the request
        if ($this->getRequest()->isAjax()) 
        {
            switch ($mode) {
                case 'files':
                    $output = $this->fileDataService->getList();
                    break;

                case 'strings':
                    $output = $this->stringDataService->getList();
                    break;
            }
        }

        return $this->resultJsonFactory->create()->setData($output);
    }
}
