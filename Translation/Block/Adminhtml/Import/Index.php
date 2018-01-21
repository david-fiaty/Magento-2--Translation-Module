<?php
/**
 * Copyright © 2015 Naxero . All rights reserved.
 */
namespace Naxero\Translation\Block\Adminhtml\Import;

class Index extends \Magento\Backend\Block\Template
{

	public function __construct(\Magento\Backend\Block\Template\Context $context)
	{
		parent::__construct($context);
	}

	public function sayHello()
	{
		return __('Hello World');
	}
}
