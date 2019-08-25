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
     * @var View
     */
    protected $viewHelper;

    /**
     * Index class constructor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Naxero\Translation\Helper\View $viewHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->viewHelper = $viewHelper;

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
            $output = $this->viewHelper->render($view);
        }

        return $this->resultJsonFactory->create()->setData($output);
    }
}
