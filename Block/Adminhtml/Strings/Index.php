<?php
/**
 * Copyright © 2015 Naxero . All rights reserved.
 */
namespace Naxero\Translation\Block\Adminhtml\Strings;

class Index extends \Magento\Backend\Block\Template
{
	public $helper;

	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Naxero\Translation\Helper\Data $helper
	)
	{
		parent::__construct($context);
		$this->helper = $helper;
	}

	public function _prepareLayout()
	{
	   // Set page title
	   $this->pageConfig->getTitle()->set(__('Manage language strings'));

	   return parent::_prepareLayout();
	}
}
