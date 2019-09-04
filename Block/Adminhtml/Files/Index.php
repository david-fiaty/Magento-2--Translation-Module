<?php
/**
 * Copyright © 2015 Naxero . All rights reserved.
 */
namespace Naxero\Translation\Block\Adminhtml\Files;

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
	   $this->pageConfig->getTitle()->set(__('Language files'));

	   return parent::_prepareLayout();
	} 
}
