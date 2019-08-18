<?php
/**
 *
 * Copyright © 2018 David Fiaty. All rights reserved.
 */
namespace Naxero\Translation\Controller\Adminhtml\Files;

use Magento\Framework\File\Csv;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DirectoryList;
use Naxero\Translation\Model\FileEntityFactory;
use Naxero\Translation\Helper\Data;

class Detail extends Action
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
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        FileEntityFactory $fileEntityFactory,
        DirectoryList $tree,
        Csv $csvParser,
        Data $helper
    ) {
        $this->resultJsonFactory            = $resultJsonFactory;
        $this->fileEntityFactory = $fileEntityFactory;
        $this->tree = $tree;
        $this->csvParser = $csvParser;
		$this->helper = $helper;

        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        if ($this->getRequest()->isAjax()) 
        {
            // Prepare the output
            $output = '';

            // Get the controller action
            $action  = $this->getRequest()->getParam('action');

            // Get the factory
            $fileEntity = $this->fileEntityFactory->create(); 

            // Get the file id from request
            $fileId = $this->getFileId();

            // Load the requested item
            $fileEntity = $this->loadFileEntity($fileId);

            // Get data
            if ($action == 'get_data') {
                $output = $this->getFileEntityContent($fileEntity);
            }
            else if ($action == 'update_data') {
                $output = $this->updateFileEntityContent($fileEntity);
            }
            else if ($action == 'save_data') {
                $output = $this->saveFileEntityContent($fileEntity);
            }

            // Return the content
            return $result->setData($output);
        }

        return [];
    }

    public function getFileId() {
        return $this->getRequest()->getParam('file_id');
    }

    public function loadFileEntity($fileId) {
        $fileEntity = $this->fileEntityFactory->create(); 
        return $fileEntity->load($fileId);
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

            // Save the new content
            $fileEntity->setFileContent($newContent);
            $fileEntity->save();

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
        // Prepare the output
        $csvString = "\"" . $row['key'] . "\"," . "\"" . $row['value'] . "\"";

        return $csvString;
    }

    public function getFileEntityContent($fileEntity) {
        $output = array(); 

        $lines = explode(PHP_EOL, $fileEntity->getData('file_content'));
        $i = 0;
        foreach ($lines as $line) {
            $row = str_getcsv($line);
            if (is_array($row) && count($row) == 2) {
                array_unshift($row , $i);
                $output[] = (object) array_combine(['index', 'key', 'value'], $row);
                $i++;
            }
        }

        return $output;
    }
}
