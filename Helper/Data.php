<?php
/**
 * Copyright © 2015 Naxero . All rights reserved.
 */
namespace Naxero\Translation\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
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
		\Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\Auth\Session $adminSession,
		\Magento\Framework\Filesystem\DirectoryList $tree,
        \Magento\Framework\File\Csv $csvParser,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList, 
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
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
        $locale = $this->adminSession->getUser()->getData()['interface_locale'];
		$userLanguage = str_replace('_', '-', $locale);
		return strtolower($userLanguage);
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

    public function excludeFile($item) {
        $path = is_array($item) ? $item['file_path'] : $item;
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

	public function getFilterSelect($attributes, $layout) {
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