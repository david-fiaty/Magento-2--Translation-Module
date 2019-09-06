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

namespace Naxero\Translation\Block\Adminhtml\Strings;

class Index extends \Magento\Backend\Block\Template
{
    /**
     * @var Data
     */
	public $helper;

    /**
     * Index class constructor.
     */
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Naxero\Translation\Helper\Data $helper
	)
	{
		parent::__construct($context);
		$this->helper = $helper;
	}

    /**
     * Prepare the block layout.
     */
	public function _prepareLayout()
	{
	   // Set page title
	   $this->pageConfig->getTitle()->set(__('Language strings'));

	   return parent::_prepareLayout();
	}
}
