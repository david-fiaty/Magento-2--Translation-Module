<?php
/**
 * Copyright © 2015 Naxero . All rights reserved.
 */
namespace Naxero\Translation\Block\Adminhtml\Logs;

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
	   $this->pageConfig->getTitle()->set(__('Error logs'));

	   return parent::_prepareLayout();
	}
}
