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
     * @var Data
     */
    protected $helper;

    /**
     * Index class constructor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Naxero\Translation\Helper\Data $helper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
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

        // Process the request
        if ($this->getRequest()->isAjax()) 
        {
            // Get the view mode
            $view = $this->getRequest()->getParam('view');

            // Render the view
            $output = $this->helper->renderView($view);
        }

        return $this->resultJsonFactory->create()->setData($output);
    }
}
