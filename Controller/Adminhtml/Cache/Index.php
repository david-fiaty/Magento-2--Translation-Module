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

namespace Naxero\Translation\Controller\Adminhtml\Cache;

class Index extends \Magento\Backend\App\Action
{
	/**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var Data
     */
    public $helper;

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
     * @return \Magento\Framework\Controller\Result\JsonFactory
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
