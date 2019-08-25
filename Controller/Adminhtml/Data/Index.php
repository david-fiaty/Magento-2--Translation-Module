<?php
/**
 *
 * Copyright © 2018 David Fiaty. All rights reserved.
 */
namespace Naxero\Translation\Controller\Adminhtml\Data;

use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
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
     * @var LogDataService
     */
    protected $logDataService; 

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Index class constructor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Naxero\Translation\Model\Service\FileDataService $fileDataService,
        \Naxero\Translation\Model\Service\StringDataService $stringDataService,
        \Naxero\Translation\Model\Service\LogDataService $logDataService,
        \Naxero\Translation\Helper\Data $helper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fileDataService = $fileDataService;
        $this->stringDataService = $stringDataService;
        $this->logDataService = $logDataService;
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
        $view = $this->getRequest()->getParam('view');

        // Process the request
        if ($this->getRequest()->isAjax()) 
        {
            switch ($view) {
                case 'files':
                    $output = $this->fileDataService->init()->getList();
                    break;

                case 'strings':
                    $output = $this->stringDataService->init()->getList();
                    break;

                case 'logs':
                    $output = $this->logDataService->init()->getList();
                    break;
            }
        }

        return $this->resultJsonFactory->create()->setData($output);
    }
}
