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
use Magento\Framework\App\PageCache\Version;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

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
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var Pool
     */
    protected $cacheFrontendPool;

	/**
     * @param \Magento\Framework\App\Helper\Context $context
     */
	public function __construct(
		Context $context,
		AdminSession $adminSession,
		DirectoryList $tree,
        Csv $csvParser,
        ScopeConfigInterface $scopeConfig,
        TypeListInterface $cacheTypeList, 
        Pool $cacheFrontendPool
	) {
		parent::__construct($context);
		$this->adminSession = $adminSession;
        $this->tree = $tree;
        $this->csvParser = $csvParser;
        $this->scopeConfig = $scopeConfig;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
	}

	public function getUserLanguage() {
        // Return the user locale
		return $this->adminSession->getUser()->getData()['interface_locale'];
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
        $excludeTestFiles = $this->getConfig('exclude_test_files');
        $excludeCoreFiles = $this->getConfig('exclude_core_files');
        $excludeStaticFiles = $this->getConfig('exclude_static_files');

        return ($excludeTestFiles && $this->isTestFile($path))
        || ($excludeCoreFiles && $this->isCoreFile($path))
        || ($excludeStaticFiles && $this->isStaticFile($path));
    }

    public function isTestFile($path) {
        return strpos($path, 'dev/tests/') === 0;
    }

    public function isCoreFile($path) {
        return strpos($path, 'dev/tests/') === 0
        || strpos($path, 'vendor/magento') === 0
        || strpos($path, 'lib/') === 0
        || strpos($path, 'app/design/frontend/Magento') === 0;
    }

    public function isStaticFile($path) {
        return strpos($path, 'pub/static') === 0;
    }

	public function getSelect($attributes, $layout) {
		// Build the select list
		$select = $layout
		->createBlock('Magento\Framework\View\Element\Html\Select')
		->setData($attributes);

		// Add an option
		$select->addOption('alltx', __('--- All ---'));

		return $select->getHtml();
	}

    public function flushCache()
    {
        // Types list
        $types = [
            'config',
            'layout',
            'block_html',
            'collections',
            'reflection',
            'db_ddl',
            'eav',
            'config_integration',
            'config_integration_api',
            'full_page',
            'translate',
            'config_webservice'
        ];
     
        // Process the types
        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }

        // Process the pools
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}