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
    public $csvParser;

    /**
     * @var Session
     */
    public $adminSession;

    /**
     * @var DirectoryList
     */
    public $tree;

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var TypeListInterface
     */
    public $cacheTypeList;

    /**
     * @var Pool
     */
    public $cacheFrontendPool;

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

	public function getCleanPath($filePath) {
        // Return the clean path
        return str_replace($this->tree->getRoot() . '/', '', $filePath);
	}

    public function countCsvRows($csvPath) {
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
		$select = $this->buildSelectList($attributes, $layout);

		// Add an option
		$select->addOption('alltx', __('--- All ---'));

		return $select->getHtml();
    }

	public function getPagerSelect($attributes, $layout) {
		// Build the select list
		$select = $this->buildSelectList($attributes, $layout);

        // Add the options
        $values = [50, 100, 150, 200, 250, 300, 350, 400];
        foreach ($values as $value) {
            $select->addOption($value, $value);
        }

		return $select->getHtml();
    }

    public function buildSelectList($attributes, $layout) {
        return $layout
		->createBlock('Magento\Framework\View\Element\Html\Select')
		->setData($attributes);
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

    public function removeDuplicateFilterValues($output) {
        // Prepare the filters array
        $filters = [
            'file_type',
            'file_group',
            'file_locale'
        ];

        // Process the filters
        foreach ($filters as $filter) {
            // Remove duplicates
            $output['filter_data'][$filter] = array_unique(
                $output['filter_data'][$filter]
            );
            
            // Sort the fields
            sort($output['filter_data'][$filter]);
        }

        return $output;
    }

    public function buildSorting($rowData, $output) {
        // Prepare the variables
        $arr = $rowData;
        $path = $arr['file_path'];

        // Todo : detect themes in vendor folder
        if (strpos($path, 'vendor/magento') === 0) {
            $arr['file_type'] = __('Module');
            $arr['file_group'] = __('Core');
        }
        else if (strpos($path, 'app/code') === 0) {
            $arr['file_type'] = __('Module');
            $arr['file_group'] = __('Community');
        }
        else if (strpos($path, 'dev/tests/') === 0) {
            $arr['file_type'] = __('Test');
            $arr['file_group'] = __('Dev');
        }
        else if (strpos($path, 'app/design/frontend/Magento') === 0) {
            $arr['file_type'] = __('Theme');
            $arr['file_group'] = __('Core');
        }
        else if (strpos($path, 'pub/static') === 0) {
            $arr['file_type'] = __('Theme');
            $arr['file_group'] = __('Static');
        }
        else if (strpos($path, 'lib/') === 0) {
            $arr['file_type'] = __('Web');
            $arr['file_group'] = __('Library');
        }        
        else if (strpos($path, 'app/design/frontend/') === 0
                && strpos($path, 'app/design/frontend/Magento') === false) {
            $arr['file_type'] = __('Theme');
            $arr['file_group'] = __('Community');
        }
        else if (
            strpos($path, 'vendor/') === 0 
            && strpos($path, 'vendor/magento') === false
        ) {
            $arr['file_type'] = __('Module');
            $arr['file_group'] = __('Vendor');
        }
        else {
            $arr['file_type'] = __('Other');
            $arr['file_group'] = __('Undefined');
        }

        // Add type filter data
        $output['filter_data']['file_type'][] = $arr['file_type'];

        // Add group filter data
        $output['filter_data']['file_group'][] = $arr['file_group'];

        // Add locale filter data
        $output['filter_data']['file_locale'][] = basename($path, '.csv');

        return [
            'data' => array_merge($arr, $rowData),
            'filters' => $output
        ];
    }

	public function getUserLanguage() {
        // Get the user language
        $locale = $this->adminSession->getUser()->getData()['interface_locale'];

        // Format the string for js tables
        $userLanguage = str_replace('_', '-', $locale);
        $userLanguage = strtolower($userLanguage);

		return $userLanguage;
    }
    
    public function getTableLocaleData() {
        // Get the user language
        $userLanguage = $this->getUserLanguage();

        // Format the locale data
        $localeData = [
            $userLanguage => [
                'pagination' => [
                    'first' => __('First'),
                    'first_title' => __('First page'),
                    'last' => __('Last'),
                    'last_title' => __('Last page'),
                    'prev' => __('Previous'),
                    'prev_title' => __('Previous page'),
                    'next' => __('Next'),
                    'next_title' => __('Next page')
                ]
            ]
        ];

        return addslashes(json_encode($localeData));
    }
}