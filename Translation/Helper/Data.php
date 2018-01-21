<?php
/**
 * Copyright © 2015 Naxero . All rights reserved.
 */
namespace Naxero\Translation\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Backend\Model\Auth\Session as AdminSession;

class Data extends AbstractHelper
{

	protected $adminSession;

	/**
     * @param \Magento\Framework\App\Helper\Context $context
     */
	public function __construct(
		Context $context,
		AdminSession $adminSession
	) {
		parent::__construct($context);
		$this->adminSession = $adminSession;
	}

	public function getUserLanguage() {
		return $this->adminSession->getUser()->getData()['interface_locale'];
	}

	public function insertIntoFile($file_path, $insert_marker, $text, $after = true) {
		$contents = file_get_contents($file_path);
    	$new_contents = preg_replace($insert_marker, ($after) ? '$0' . $text : $text . '$0', $contents);
    	return file_put_contents($file_path, $new_contents);
	}
}