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

class StringDataService
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
     * StringDataService constructor
     */
    public function __construct(
        \Naxero\Translation\Model\FileEntityFactory $fileEntityFactory,
        \Naxero\Translation\Helper\Data $helper,
        \Naxero\Translation\Model\Service\LogDataService $logDataService
    ) {
        $this->fileEntityFactory = $fileEntityFactory;
        $this->helper = $helper;
        $this->logDataService = $logDataService;
    }

    /**
     * Initilaise the class instance.
     */
    public function init()
    {
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

        // Process the files content
        $rowCount = 0;
        foreach ($collection as $item) {
            // Get the item data
            $arr = $item->getData();
            if (!$this->helper->excludeFile($arr)) {
                // Build the sorting fields
                $sorting = $this->helper->buildFilters($arr, $this->output);
                $arr = $sorting['data'];
                $this->output = $sorting['filters'];

                // Get the content rows
                $rows = json_decode($arr['file_content']);
                unset($arr['file_content']);

                // Set the language field
                $arr['file_locale'] = $this->helper->getPathInfo(
                    $arr['file_path'],
                    'filename'
                );
                
                // Loop through the rows
                if (!empty($rows)) {
                    $rowId = 0;
                    foreach ($rows as $row) {
                        // Prepare the output array
                        $output = [];

                        // Prepare the row index
                        $rowIndex = $rowCount + 1;

                        // Skip empty and non pair values
                        if (!$this->logDataService->hasErrors($arr['file_id'], $row, $rowId)) {
                            // Store the item as an object
                            $this->output['table_data'][] = (object) $this->buildRow(
                                $row,
                                $rowId,
                                $rowIndex,
                                $arr
                            );
                        } else {
                            // Store the item as an object
                            $this->output['table_data'][] = (object) $this->buildErrorRow(
                                $row,
                                $rowId,
                                $rowIndex,
                                $arr
                            );

                            // Store the error reference
                            $this->output['error_data'][] = $rowIndex;
                        }

                        // Increment the row id
                        $rowCount++;
                    }
                }
            }
        }

        // Return the data output
        return $this->helper->removeDuplicateFilterValues($this->output);
    }

    /**
     * Format a CSV file row for display.
     */
    public function buildRow($line, $rowId, $rowIndex, $arr)
    {
        return array_merge([
            'index' => $rowIndex,
            'row_id' => $rowId,
            'is_error' => 0,
            'key' => $line[0],
            'value' => $line[1]
        ], $arr);
    }

    /**
     * Format a CSV file error row for display.
     */
    public function buildErrorRow($line, $rowId, $rowIndex, $arr)
    {
        $errorLine = [];
        $errorLine['index'] = $rowIndex;
        $errorLine['row_id'] = $rowId;
        $errorLine['is_error'] = 1;
        $errorLine['key'] = isset($line[0]) ? $line[0] : '';
        $errorLine['value'] = isset($line[1]) ? $line[1] : '';

        return array_merge($errorLine, $arr);
    }

    /**
     * Prepare the JS table data structure.
     */
    public function prepareOutputArray()
    {
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
}
