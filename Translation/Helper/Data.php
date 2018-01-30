<?php
/**
 * Copyright © 2015 Naxero . All rights reserved.
 */
namespace Naxero\Translation\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Backend\Model\Auth\Session as AdminSession;

class Data extends AbstractHelper
{
    /**
     * @var Session
     */
	protected $adminSession;

    /**
     * @var DirectoryList
     */
    protected $tree;

	/**
     * @param \Magento\Framework\App\Helper\Context $context
     */
	public function __construct(
		Context $context,
		AdminSession $adminSession,
		DirectoryList $tree
	) {
		parent::__construct($context);
		$this->adminSession = $adminSession;
        $this->tree = $tree;
	}

	public function getUserLanguage() {
        // Return the user locale
		return $this->adminSession->getUser()->getData()['interface_locale'];
	}

	public function insertIntoFile($file_path, $insert_marker, $text, $after = true) {
		$contents = file_get_contents($file_path);
    	$new_contents = preg_replace($insert_marker, ($after) ? '$0' . $text : $text . '$0', $contents);
    	return file_put_contents($file_path, $new_contents);
	}

	public function getCleanPath($filePath) {
        // Return the clean path
        return str_replace($this->tree->getRoot() . '/', '', $filePath);
	}
}