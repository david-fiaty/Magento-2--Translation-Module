<?php
/**
 * Copyright © 2015 Naxero . All rights reserved.
 */
namespace Naxero\Translation\Block\Adminhtml\Strings;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Naxero\Translation\Helper\Data;

class Index extends Template
{
	protected $helper;

	public function __construct(Context $context, Data $helper)
	{
		parent::__construct($context);
		$this->helper = $helper;
	}

	public function _prepareLayout()
	{
	   //set page title
	   $this->pageConfig->getTitle()->set(__('Manage translations'));

	   return parent::_prepareLayout();
	}  

	public function getUserLanguage()
	{
		$userLanguage = str_replace('_', '-', $this->helper->getUserLanguage());
		return strtolower($userLanguage);
	}

	public function getSelect($attributes) {
		$select = $this->getLayout()->createBlock('Magento\Framework\View\Element\Html\Select')->setData($attributes);
		$select->addOption('', __('--- All ---'));

		return $select->getHtml();
	}
}
