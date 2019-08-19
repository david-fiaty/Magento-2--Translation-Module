<?php
/**
 *
 * Copyright © 2018 David Fiaty. All rights reserved.
 */
namespace Naxero\Translation\Controller\Adminhtml\Cache;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Naxero\Translation\Helper\Data;

class Index extends Action
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
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Data $helper
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
        $output = ['success' => 'true'];

        // Get the view mode
        $action = $this->getRequest()->getParam('action');

        // Process the request
        if ($this->getRequest()->isAjax() && $action == 'flush_cache') 
        {
            try {
                $this->helper->flushCache();
            }
            catch(\Exception $e) {
                $output = [
                    'success' => false,
                    'message' => __($e->getMessage())
                ];
            }
        }

        return $this->resultJsonFactory->create()->setData($output);
    }
}
