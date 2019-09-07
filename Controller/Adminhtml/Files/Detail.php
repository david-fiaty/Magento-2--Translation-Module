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

namespace Naxero\Translation\Controller\Adminhtml\Files;

class Detail extends \Magento\Backend\App\Action
{
	/**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var FileEntityFactory
     */
    public $fileEntityFactory;    

    /**
     * @var Csv
     */
    public $csvParser;

    /**
     * @var File
     */
    public $fileDriver;

    /**
     * @var Data
     */
	public $helper;

    /**
     * @var DirectoryList
     */
    public $tree;

    /**
     * @var LogDataService
     */
    public $logDataService;

    /**
     * Detail class constructor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Naxero\Translation\Model\FileEntityFactory $fileEntityFactory,
        \Magento\Framework\Filesystem\DirectoryList $tree,
        \Magento\Framework\File\Csv $csvParser,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Naxero\Translation\Helper\Data $helper,
        \Naxero\Translation\Model\Service\LogDataService $logDataService
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fileEntityFactory = $fileEntityFactory;
        $this->tree = $tree;
        $this->csvParser = $csvParser;
        $this->fileDriver = $fileDriver;
        $this->helper = $helper;
        $this->logDataService = $logDataService;

        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        // Prepare the response instance
        $output = [];
        $result = $this->resultJsonFactory->create();

        // Process the request
        if ($this->getRequest()->isAjax()) 
        {
            // Get the request parameters
            $action  = $this->getRequest()->getParam('action');
            $fileId = $this->getRequest()->getParam('file_id');
            $isLogView = $this->getRequest()->getParam('is_log_view');

            // Load the requested item
            $fileInstance = $this->fileEntityFactory
                ->create()
                ->load($fileId);

            // Get data
            if ($fileInstance->getId() > 0) {
                switch ($action) {
                    case 'get_data':
                        $output = $this->getFileEntityContent($fileInstance, $isLogView);
                        break;
        
                    case 'update_data':
                        $output = $this->updateFileEntityContent($fileInstance);
                        break;

                    case 'save_data':
                        $output = $this->saveFileEntityContent($fileInstance);
                        break;
                }
            }
        }

        // Return the content
        return $result->setData($output);
    }

    /**
     * Update a file entity content in database.
     */
    public function updateFileEntityContent($fileEntity) {
        // Prepare the new content
        $params = $this->getRequest()->getParams();
        $newRrow = $this->rowToCsv($params['row_content']);

        // Insert the new data
        try {
            // Get the current content
            $content = $fileEntity->getFileContent();

            // Convert the content to array
            $lines = explode(PHP_EOL, $content);

            // Get the row id from index
            $rowId = $params['row_content']['index'] - 1;

            // Update the row
            $lines[$rowId] = $newRrow;
            $newContent = $this->arrayToCsv($lines);

            // Save the new content to db
            $fileEntity->setFileContent($newContent);
            $fileEntity->setRowsCount(count($lines));
            $fileEntity->save();

            // Update the CSV file
            $this->saveFileEntityContent($fileEntity);

            return true;
        }
        catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return false;
    }

    /**
     * Save a file content in the file system.
     */
    public function saveFileEntityContent($fileEntity) {
        // Get the root path
        $rootPath = $this->tree->getRoot();

        // Save the data
        try {
            // Prepare the full file path
            $filePath = $rootPath . DIRECTORY_SEPARATOR . $fileEntity->getData('file_path');

            // Save the file
            return $this->fileDriver->filePutContents(
                $filePath,
                $fileEntity->getData('file_content')
            );
        }
        catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return false;
    }

    /**
     * Convert an array to CSV format.
     */
    public function arrayToCsv($array) {
        // Prepare the output
        $csvString = '';

        // Array to CSV
        foreach ($array as $row) {
            $parts = explode(',', $row);
            if (isset($parts[0]) && isset($parts[1])) {
                $csvString .= $parts[0] . ',' . $parts[1] . PHP_EOL;
            }
            else if (isset($parts[0]) && !isset($parts[1])) {
                $csvString .= $parts[0] . PHP_EOL;
            }
            else {
                $csvString .= $row . PHP_EOL;
            }
        }

        return $csvString;
    }

    /**
     * Convert a row to CSV format.
     */
    public function rowToCsv($row) {
        $csvString = '"' . $row['key'] 
        . '",' . '"' . $row['value'] . '"';

        return $csvString;
    }

    /**
     * Get a file content from database.
     */
    public function getFileEntityContent($fileEntity, $isLogView) {
        // Prepare the output array
        $output = array(); 

        // Get the file content rows
        $rows = explode(PHP_EOL, $fileEntity->getData('file_content'));

        // Get the file id
        $fileId = $fileEntity->getData('file_id');

        // Loop through the rows
        $rowIndex = 1;
        $rowId = 0;
        foreach ($rows as $row) {
            $line = str_getcsv($row);
            if (!$this->logDataService->hasErrors($fileId, $line, $rowId)) {
                $output['table_data'][] = $this->buildRow($line, $rowIndex);
                $rowIndex++;
            }
            else {
                $output['table_data'][] = $this->buildErrorRow($line, $rowIndex);
                $output['error_data'][] = $rowIndex;
                $rowIndex++;
            }
            $rowId++;
        }

        return $output;
    }

    /**
     * Prepare a file row content for display.
     */
    public function buildRow($rowDataArray, $rowIndex) {
        // Add the index to the row array
        array_unshift($rowDataArray, $rowIndex);

        // Retun combined data
        return (object) array_combine(
            ['index', 'key', 'value'],
            $rowDataArray
        );
    }

    /**
     * Prepare a file content row error for display.
     */
    public function buildErrorRow($rowDataArray, $rowIndex) {
        // Build the error line
        $errorLine = [];
        $errorLine[] = $rowIndex;
        $errorLine[] = isset($rowDataArray[0]) ? $rowDataArray[0] : '';
        $errorLine[] = isset($rowDataArray[1]) ? $rowDataArray[1] : '';

        // Retun combined data
        return (object) array_combine(
            ['index', 'key', 'value'],
            $errorLine
        );
    }
}
