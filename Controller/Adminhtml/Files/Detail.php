<?php
/**
 *
 * Copyright © 2018 David Fiaty. All rights reserved.
 */
namespace Naxero\Translation\Controller\Adminhtml\Files;

class Detail extends \Magento\Backend\App\Action
{
	/**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var FileEntityFactory
     */
    protected $fileEntityFactory;    

    /**
     * @var Csv
     */
    protected $csvParser;

    /**
     * @var Data
     */
	protected $helper;

    /**
     * @var DirectoryList
     */
    protected $tree;

    /**
     * @var LogDataService
     */
    protected $logDataService;

    /**
     * Detail class constructor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Naxero\Translation\Model\FileEntityFactory $fileEntityFactory,
        \Magento\Framework\Filesystem\DirectoryList $tree,
        \Magento\Framework\File\Csv $csvParser,
        \Naxero\Translation\Helper\Data $helper,
        \Naxero\Translation\Model\Service\LogDataService $logDataService
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fileEntityFactory = $fileEntityFactory;
        $this->tree = $tree;
        $this->csvParser = $csvParser;
        $this->helper = $helper;
        $this->logDataService = $logDataService;

        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        // Prepare the response instance
        $result = $this->resultJsonFactory->create();

        // Process the request
        if ($this->getRequest()->isAjax()) 
        {
            // Prepare the output
            $output = '';

            // Get the controller action
            $action  = $this->getRequest()->getParam('action');

            // Get the log view parameter
            $isLogView = $this->getRequest()->getParam('is_log_view');

            // Get the factory
            $fileEntity = $this->fileEntityFactory->create(); 

            // Get the file id from request
            $fileId = $this->getFileId();

            // Load the requested item
            $fileInstance = $fileEntity->load($fileId);

            // Get data
            if ($action == 'get_data') {
                $output = $this->getFileEntityContent($fileInstance, $isLogView);
            }
            else if ($action == 'update_data') {
                $output = $this->updateFileEntityContent($fileInstance);
            }
            else if ($action == 'save_data') {
                $output = $this->saveFileEntityContent($fileInstance);
            }

            // Return the content
            return $result->setData($output);
        }

        return [];
    }

    public function getFileId() {
        return $this->getRequest()->getParam('file_id');
    }

    public function updateFileEntityContent($fileEntity) {
        // Prepare the new content
        $params = $this->getRequest()->getParams();
        $newRrow = $this->rowToCsv($params['row_content']);

        // Insert the new row
        try {
            // Get the current content
            $fileId = $this->getFileId();
            $fileEntity = $this->fileEntityFactory->create(); 
            $fileEntity->load($fileId);
            $content = $fileEntity->getFileContent();

            // Convert the content to array
            $lines = explode(PHP_EOL, $content);

            // Update the row
            $lines[$params['row_content']['index']] = $newRrow;
            $newContent = $this->arrayToCsv($lines);

            // Save the new content to db
            $fileEntity->setFileContent($newContent);
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

    public function saveFileEntityContent($fileEntity) {
        // Get the root path
        $rootPath = $this->tree->getRoot();

        // Save the data
        try {
            // Load the file entity
            $fileId = $this->getFileId();
            $fileEntity = $this->fileEntityFactory->create(); 
            $fileEntity->load($fileId);

            // Prepare the full file path
            $filePath = $rootPath . '/' . $fileEntity->getData('file_path');

            // Save the file
            file_put_contents($filePath, $fileEntity->getData('file_content'));

            return true;
        }
        catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return false;
    }

    public function arrayToCsv($array) {
        // Prepare the output
        $csvString = '';

        // Array to CSV
        foreach ($array as $row) {
            $parts = explode(',', $row);
            if (isset($parts[0]) && isset($parts[1])) {
                $csvString .= $parts[0] . "," . $parts[1] . "\n";
            }
            else if (isset($parts[0]) && !isset($parts[1])) {
                $csvString .= $parts[0] . "\n";
            }
            else {
                $csvString .= $row . "\n";
            }
        }

        return $csvString;
    }

    public function rowToCsv($row) {
        $csvString = "\"" . $row['key'] . "\"," . "\"" . $row['value'] . "\"";

        return $csvString;
    }

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
            if (!$this->logDataService->hasErrors($line, $fileId, $rowId)) {
                $output[] = $this->buildRow($line, $rowIndex);
                $rowIndex++;
            }
            else if ($this->logDataService->hasErrors($line, $fileId, $rowId) && !$this->logDataService->shoudHideRow($isLogView)) {
                $output[] = $this->buildErrorRow($line, $rowIndex);
                $rowIndex++;
            }
            $rowId++;
        }

        return $output;
    }

    public function buildRow($rowDataArray, $rowIndex) {
        // Add the index to the row array
        array_unshift($rowDataArray, $rowIndex);

        // Retun combined data
        return (object) array_combine(
            ['index', 'key', 'value'],
            $rowDataArray
        );
    }

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
