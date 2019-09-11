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

namespace Naxero\Translation\Model\Service;

class FileDataService
{
    /**
     * @var FileEntityFactory
     */
    public $fileEntityFactory;    

    /**
     * @var Array
     */
    public $output;

    /**
     * @var Data
     */
    public $helper;

    /**
     * @var LogDataService
     */
    public $logDataService;

    /**
     * @var LogEntityFactory
     */
    public $logEntityFactory;

    /**
     * FileDataService constructor
     */
    public function __construct(
        \Naxero\Translation\Model\FileEntityFactory $fileEntityFactory,
        \Naxero\Translation\Helper\Data $helper,
        \Naxero\Translation\Model\Service\LogDataService $logDataService,
        \Naxero\Translation\Model\LogEntityFactory $logEntityFactory
    ) {
        $this->fileEntityFactory = $fileEntityFactory;
        $this->helper = $helper;
        $this->logDataService = $logDataService;
        $this->logEntityFactory = $logEntityFactory;
    }

    /**
     * Initilaise the class instance.
     */
    public function init() {
        // Prepare the output array
        $this->output = $this->prepareOutputArray();

        return $this;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function getList()
    {
        // Get the factory
        $fileEntity = $this->fileEntityFactory->create(); 

        // Create the collection
        $collection = $fileEntity->getCollection();

        // Prepare the output array
        foreach ($collection as $item)
        {
            // Get the item data as array
            $arr = $item->getData();

            // Process the file
            if (!$this->helper->excludeFile($arr) && !empty($arr['file_path'])) {
                // Prepare the columns and filters
                $arr = $this->formatFileRow($arr, $item);

                // Build the sorting
                $sorting = $this->helper->buildFilters($arr, $this->output);
                $arr = $sorting['data'];
                $this->output = $sorting['filters'];

                // Check if the file exists
                $fileExists = $this->helper->fileExists($arr['file_path']);
                if ($fileExists) {
                    // Get the permissions
                    $isReadable = $this->helper->isReadable($arr['file_path']);
                    $isWritable = $this->helper->isWritable($arr['file_path']);

                    // Process the read/write state 
                    if (!$isReadable || !$isWritable) {
                        $this->output['error_data'][] = $arr['file_id'];
                    }
                }
                else {
                    $this->output['error_data'][] = $arr['file_id'];  
                }

                // Remove uneeded file content for performance
                unset($arr['file_content']);

                // Store the item as an object
                $this->output['table_data'][] = (object) $arr;
            }
        }

        // Return the data output
        return $this->helper->removeDuplicateFilterValues($this->output);
    }

    /**
     * Prepare the JS table data structure.
     */
    public function prepareOutputArray() {
        return [
            'table_data' => [],
            'filter_data' => [
                'file_type' => [], 
                'file_group' => [], 
                'file_locale' => [], 
                'file_status' => [
                    __('Error'),
                    __('Active')
                ]
            ]
        ];
    }

    /**
     * Format a file row data for display.
     */
    public function formatFileRow($arr, $fileEntity) {
        // Cast the id field to integer
        $arr['file_id'] = (int) $arr['file_id'];

        // Set the language field
        $arr['file_locale'] =  basename($arr['file_path'], '.csv');

        // Add the errors column
        $arr['errors'] = $this->getFileErrorCount($fileEntity);

        return $arr;
    }	

    /**
     * Count the error rows in a file.
     */
    function getFileErrorCount($fileEntity) {
        // Get the file id
        $fileId = $fileEntity->getId();

        // Load the collection
        $collection = $this->logEntityFactory->create()->getCollection();
        $collection->addFieldToFilter('file_id', $fileId);

        // Return the count
        return $collection->getSize();
    }
}
