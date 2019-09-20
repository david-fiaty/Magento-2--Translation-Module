<?php
/**
 * Naxero.com
 * Professional ecommerce integrations for Magento
 *
 * PHP version 7
 *
 * @category  Magento2
 * @package   Naxero
 * @author    Platforms Development Team <contact@naxero.com>
 * @copyright Naxero.com
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.naxero.com
 */

namespace Naxero\Translation\Controller\Adminhtml\Files;

class Create extends \Magento\Backend\App\Action
{
	/**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * Create class constructor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Create action
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        // Prepare the response instance
        $output = [];
        $result = $this->resultJsonFactory->create();

        // Process the request
        if ($this->getRequest()->isAjax()) 
        {
            // Get the request parameters
            $filePath  = $this->getRequest()->getParam('file_path');
            $fileName = $this->getRequest()->getParam('file_name');
        }

        // Return the response
        return $result->setData($output);
    }
}
