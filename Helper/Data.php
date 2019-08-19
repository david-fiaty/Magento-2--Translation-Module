<?php
/**
 * Copyright © 2015 Naxero . All rights reserved.
 */
namespace Naxero\Translation\Helper;

use Magento\Framework\File\Csv;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Backend\Model\Auth\Session as AdminSession;

class Data extends AbstractHelper
{
    /**
     * @var Csv
     */
    protected $csvParser;

    /**
     * @var Session
     */
	protected $adminSession;

    /**
     * @var DirectoryList
     */
    protected $tree;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

	/**
     * @param \Magento\Framework\App\Helper\Context $context
     */
	public function __construct(
		Context $context,
		AdminSession $adminSession,
		DirectoryList $tree,
        Csv $csvParser,
        ScopeConfigInterface $scopeConfig
	) {
		parent::__construct($context);
		$this->adminSession = $adminSession;
        $this->tree = $tree;
        $this->csvParser = $csvParser;
        $this->scopeConfig = $scopeConfig;
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

    public function countCSVRows($csvPath) {
        // Parse the string
        $csvData = $this->csvParser->getData($csvPath);

        // Return the row count
        return count($csvData);
    }

    public function getConfig($value) {
        return $this->scopeConfig->getValue(
            'translation/general/' . $value,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function excludeFile($row) {
        $path = $row['file_path'];
        $excludeTestFiles = $this->helper->getConfig('exclude_test_files');

        return $excludeTestFiles && strpos($path, 'dev/tests/') === 0;
    }
}