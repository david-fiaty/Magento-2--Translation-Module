<?php
/**
 * Naxero.com
 * Professional ecommerce integrations for Magento
 *
 * PHP version 7
 *
 * @category  Magento2
 * @package   Naxero
 * @author    Platforms Development Team <contact@naxero.com>
 * @copyright Naxero.com
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.naxero.com
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
     * @var File
     */
    public $fileDriver;

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
     * @var DirectoryList
     */
    public $dir;

	/**
     * Data class constructor
     */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\Auth\Session $adminSession,
		\Magento\Framework\Filesystem\DirectoryList $tree,
        \Magento\Framework\File\Csv $csvParser,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList, 
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\Filesystem\DirectoryList $dir
	) {
		parent::__construct($context);
        $this->adminSession = $adminSession;
        $this->tree = $tree;
        $this->csvParser = $csvParser;
        $this->fileDriver = $fileDriver;
        $this->scopeConfig = $scopeConfig;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->dir = $dir;
	}

    /**
     * Get the clean path for a file path.
     */
	public function getCleanPath($filePath) {
        // Return the clean path
        return str_replace($this->tree->getRoot() . DIRECTORY_SEPARATOR, '', $filePath);
	}

    /**
     * Get the full path from a clean path.
     */
	public function getFullPath($cleanPath) {
        // Return the full path
        return $this->dir->getRoot() . DIRECTORY_SEPARATOR . $cleanPath;
    }

    /**
     * Get a module config parameter.
     */
    public function getConfig($value) {
        return $this->scopeConfig->getValue(
            'translation/general/' . $value,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Explude a file from display.
     */
    public function excludeFile($item) {
        $path = is_array($item) ? $item['file_path'] : $item;
        $excludeTestFiles = $this->getConfig('exclude_test_files');
        $excludeCoreFiles = $this->getConfig('exclude_core_files');
        $excludeStaticFiles = $this->getConfig('exclude_static_files');

        return ($excludeTestFiles && $this->isTestFile($path))
        || ($excludeCoreFiles && $this->isCoreFile($path))
        || ($excludeStaticFiles && $this->isStaticFile($path));
    }

    /**
     * Count the rows in a CSV file.
     */
    public function isTestFile($path) {
        return strpos($path, 'dev/tests/') === 0;
    }

    /**
     * Check if a file is part of the core.
     */
    public function isCoreFile($path) {
        return strpos($path, 'dev/tests/') === 0
        || strpos($path, 'vendor/magento') === 0
        || strpos($path, 'lib/') === 0
        || strpos($path, 'app/design/frontend/Magento') === 0;
    }

    /**
     * Check if a file is statically generated.
     */
    public function isStaticFile($path) {
        return strpos($path, 'pub/static') === 0;
    }

    /**
     * Generate the select list filters.
     */
	public function getFilterSelect($attributes, $layout) {
		// Build the select list
		$select = $this->buildSelectList($attributes, $layout);

		// Add an option
		$select->addOption('alltx', __('--- All ---'));

		return $select->getHtml();
    }

    /**
     * Generate the pager select list options.
     */
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

    /**
     * Generate a base select list.
     */
    public function buildSelectList($attributes, $layout) {
        return $layout
		->createBlock('Magento\Framework\View\Element\Html\Select')
		->setData($attributes);
    }

    /**
     * Flush the Magento cache.
     */
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

    /**
     * Remove duplicated values in filters.
     */
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

    /**
     * Generate the list filter options.
     */
    public function buildFilters($rowData, $output) {
        // Prepare the variables
        $arr = $rowData;
        $path = $arr['file_path'];

        // Test the file paths
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
        else if (strpos($path, 'app/design/frontend/Magento') === 0
        || strpos($path, 'app/design/adminhtml/Magento') === 0) {
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
        else if ((strpos($path, 'app/design/frontend/') === 0
                && strpos($path, 'app/design/frontend/Magento') === false)
                || (strpos($path, 'app/design/adminhtml/') === 0
                && strpos($path, 'app/design/adminhtml/Magento') === false)) {
            $arr['file_type'] = __('Theme');
            $arr['file_group'] = __('Community');
        }
        else if (
            strpos($path, 'vendor/') === 0 
            && strpos($path, 'vendor/magento') === false
        ) {
            if ($this->isVendorTheme($path)) {
                $arr['file_type'] = __('Theme');
                $arr['file_group'] = __('Vendor');
            }
            else {
                $arr['file_type'] = __('Module');
                $arr['file_group'] = __('Vendor');             
            }
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

    /**
     * Check if a module is vendor theme.
     */
	public function isVendorTheme($path) {
        // File path to array
        $members = explode(DIRECTORY_SEPARATOR, $path);

        // Remove the file name
        if (is_array($members) && !empty($members)) {
            array_pop($members);

            // Remove the i18n folder name
            array_pop($members);

            // Get the registration path
            $moduleDir = implode(DIRECTORY_SEPARATOR, $members);
            $regPath = $moduleDir . DIRECTORY_SEPARATOR . 'theme.xml';
            $fullRegPath = $this->getFullPath($regPath);

            return $this->fileExists($fullRegPath)
            && $this->isReadable($fullRegPath);
        }

        return false;
    }

    /**
     * Get the user locale.
     */
	public function getUserLanguage() {
        // Get the user language
        $locale = $this->adminSession->getUser()->getData()['interface_locale'];

        // Format the string for js tables
        $userLanguage = str_replace('_', '-', $locale);
        $userLanguage = strtolower($userLanguage);

		return $userLanguage;
    }
    
    /**
     * Get the target langauge for a JS table.
     */
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

    /**
     * Check if a file exists.
     */
    public function fileExists($path) {
        try {
            return $this->fileDriver->isExists($path)
            && $this->fileDriver->isFile($path);
        }
        catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if a file is readable.
     */
    public function isReadable($path) {
        try {
            return $this->fileDriver->isFile($path)
            && $this->fileDriver->isReadable($path);
        }
        catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if a file is writable.
     */
    public function isWritable($path) {
        try {
            return $this->fileDriver->isFile($path)
            && $this->fileDriver->isWritable($path);
        }
        catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create a new file.
     */
    function createFile($filePath) {
        try {
            // Try to create a file
            return $this->fileDriver->filePutContents(
                $filePath,
                ''
            );
        }
        catch (\Exception $e) {
            return __($e->getMessage());
        }
    }
}