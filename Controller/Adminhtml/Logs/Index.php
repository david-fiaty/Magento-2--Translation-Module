<?php
/**
 *
 * Copyright © 2018 David Fiaty. All rights reserved.
 */
namespace Naxero\Translation\Controller\Adminhtml\Logs;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Index class constructor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) { 
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        // Prepare the output
        $result = $this->resultJsonFactory->create();

        // Prepare the output
        $output = array();

        // Loop through the directory tree
        if ($this->getRequest()->isAjax()) 
        {
 
        }

        return $result->setData([]);
    }
}
