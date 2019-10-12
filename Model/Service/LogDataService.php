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

class LogDataService
{
    /**
     * @var LogEntityFactory
     */
    public $logEntityFactory;

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
     * LogDataService constructor
     */
    public function __construct(
        \Naxero\Translation\Model\LogEntityFactory $logEntityFactory,
        \Naxero\Translation\Model\FileEntityFactory $fileEntityFactory,
        \Naxero\Translation\Helper\Data $helper
    ) {
        $this->logEntityFactory = $logEntityFactory;
        $this->fileEntityFactory = $fileEntityFactory;
        $this->helper = $helper;
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

    public function getError()
    {
        return [
            __('Empty line detected.'),
            __('Empty key detected.'),
            __('Empty value detected.'),
            __('Incorrect Key/Value structure.'),
            __('The file is not readable.'),
            __('The file is not writable.')
        ];
    }
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function getList()
    {
        // Get the factory
        $logEntity = $this->logEntityFactory->create();

        // Create the collection
        $collection = $logEntity->getCollection();

        // Process the logs
        $fileCount = 0;
        foreach ($collection as $item) {
            // Get the item data
            $arr = $item->getData();
            
            // Load the file instance
            $fileEntity = $this->fileEntityFactory->create();
            $fileInstance = $fileEntity->load($arr['file_id']);

            // Process the file
            $filePath = $fileInstance->getFilePath();
            if (!$this->helper->excludeFile($filePath) && !empty($filePath)) {
                // Add the file index field
                $arr['index'] = $fileCount + 1;

                // Update the row id for display
                $arr['row_id'] = $arr['row_id'] + 1;

                // Add the file path field
                $arr['file_path'] = $filePath;

                // Add the read/write states
                $arr['is_readable'] = $fileInstance->getData('is_readable');
                $arr['is_writable'] = $fileInstance->getData('is_writable');

                // Format the errors
                if (!empty($arr['comments'])) {
                    $errors = json_decode($arr['comments']);
                    $arr['comments'] = '';
                    foreach ($errors as $errorId) {
                        $arr['comments'] .= $this->getError()[$errorId] . PHP_EOL;
                    }
                }

                // Add to output
                $this->output['table_data'][] = (object) $arr;

                // Increment the file count
                $fileCount++;
            }
        }

        // Return the data output
        return $this->output;
    }

    /**
     * Prepare the JS table data structure.
     */
    public function prepareOutputArray()
    {
        return [
            'table_data' => []
        ];
    }

    /**
     * Check if a file has errors.
     */
    public function hasErrors($fileId, $line, $rowId)
    {
        // Prepare the error array
        $errors = [];

        // Check for empty lines
        if (empty($line[0]) && empty($line[1])) {
            $errors[] = 0;
        }

        // Check for empty key
        if (empty($line[0]) && !empty($line[1])) {
            $errors[] = 1;
        }

        // Check for empty value
        if (!empty($line[0]) && empty($line[1])) {
            $errors[] = 2;
        }

        // Check for too many values
        if (count($line) > 2 || (!empty($line[0]) && count($line) < 2)) {
            $errors[] = 3;
        }

        // Process the results
        if (!empty($errors)) {
            foreach ($errors as $errorId) {
                $this->createLog($errorId, $fileId, $rowId);
            }

            return true;
        }

        return false;
    }

    /**
     * Create an error log record.
     */
    public function createLog($errorId, $fileId, $rowId = null)
    {
        // Check if the error already exists
        $collection = $this->logEntityFactory->create()->getCollection();
        $collection->addFieldToFilter('file_id', $fileId);

        // Add the row id if exists
        if ($rowId) {
            $collection->addFieldToFilter('row_id', $rowId);
        }

        // Create a new error or update an existing row
        if ($collection->getSize() < 1) {
            $logEntity = $this->logEntityFactory->create();
            $logEntity->setData('file_id', $fileId);
            $logEntity->setData('row_id', $rowId);
            $logEntity->setData('comments', json_encode([$errorId]));
            $logEntity->save();
        } else {
            foreach ($collection as $item) {
                // Load the existing row
                $logEntity = $this->logEntityFactory->create();
                $logInstance = $logEntity->load($item->getData('id'));

                // Create the new comments
                $newContent  = json_decode($logInstance->getData('comments'));
                if (!in_array($errorId, $newContent)) {
                    array_push($newContent, $errorId);
                }

                // Save the entity
                $logInstance->setData('comments', json_encode($newContent));
                $logInstance->setData('row_id', $rowId);
                $logInstance->save();
            }
        }
    }
}
