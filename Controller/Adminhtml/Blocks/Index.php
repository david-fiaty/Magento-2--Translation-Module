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

namespace Naxero\Translation\Controller\Adminhtml\Blocks;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var Context
     */
    public $context;

    /**
     * @var PageFactory
     */
    public $pageFactory;

    /**
     * @var JsonFactory
     */
    public $jsonFactory;

    /**
     * Index class constructor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        // Prepare the return variable
        $html = '';

        // Process the request
        if ($this->getRequest()->isAjax()) {
            $html = $this->renderBlock();
        }

        return $this->jsonFactory->create()->setData(
            ['html' => $html]
        );
    }

    /**
     * Render a block
     */
    public function renderBlock()
    {
        // Get the request variables
        $blockType = $this->getRequest()->getParam('block_type');
        $templateName = $this->getRequest()->getParam('template_name');

        // Build the block class path
        $blockClassPath  = 'Naxero\\Translation\\Block\\Adminhtml';
        $blockClassPath .= '\\' . ucfirst($blockType) . '\\Form';

        // Build the block template path
        $blockTemplatePath  = 'Naxero_Translation::';
        $blockTemplatePath .= $blockType . '/' . $templateName . '.phtml';

        // Return the rendered block
        return $this->pageFactory->create()->getLayout()
        ->createBlock($blockClassPath)
        ->setTemplate($blockTemplatePath)
        ->toHtml();
    }
}
