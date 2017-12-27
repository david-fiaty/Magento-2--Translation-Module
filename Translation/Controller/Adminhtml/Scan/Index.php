<?php
/**
 *
 * Copyright © 2018 David Fiaty. All rights reserved.
 */
namespace Naxero\Translation\Controller\Adminhtml\Scan;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Naxero\Translation\Model\FileEntityFactory;

class Index extends Action
{

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var DirectoryList
     */
    protected $tree;

    /**
     * @var FileEntityFactory
     */
    protected $fileEntityFactory;    

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        DirectoryList $tree,
        FileEntityFactory $fileEntityFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->tree = $tree;
        $this->fileEntityFactory = $fileEntityFactory;

        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        // Prepare the output
        $result = $this->resultJsonFactory->create();

        // Prepare the output
        $output = array();

        // Loop through the directory tree
        if ($this->getRequest()->isAjax()) 
        {
            // Clear the table data
            $this->clearTableData();

            // Get the root directory
            $rootPath = $this->tree->getRoot();

            $rdi = new \RecursiveDirectoryIterator($rootPath);
            foreach(new \RecursiveIteratorIterator($rdi) as $filePath)
            {
                if ($this->isWantedFile($filePath)) {
                    $output[] = $this->saveFile($filePath);
                }
            }
        }

        return $result->setData($output);
    }

    public function clearTableData() {
        // Todo : better to check if the files already exist in db
        $fileEntity = $this->fileEntityFactory->create(); 
        $connection = $fileEntity->getCollection()->getConnection();
        $tableName  = $fileEntity->getCollection()->getMainTable();
        $connection->truncateTable($tableName);
    }

    public function saveFile($filePath)
    {
        // Get the root directory
        $rootPath = $this->tree->getRoot();

        // Prepare a clean path
        $search = $rootPath . '/';
        $cleanPath = str_replace($search, '', $filePath);

        // Save the item
        $fileEntity = $this->fileEntityFactory->create(); 
        $fileEntity->setData('file_path', $cleanPath);
        $fileEntity->setData('file_content', file_get_contents($filePath));
        $fileEntity->setData('file_is_active', 1);
        $fileEntity->setData('file_creation_time', date("Y-m-d H:i:s"));
        $fileEntity->setData('file_update_time', date("Y-m-d H:i:s"));

        $fileEntity->save();

        return (object) $fileEntity->getData();
    }

    public function isWantedFile($filePath)
    {
        // Get the root directory
        $rootPath = $this->tree->getRoot();

        // Validate the conditions - Todo : move exclusion config settings 
        $result = (pathinfo($filePath, PATHINFO_EXTENSION) == 'csv')
                  && (is_file($filePath))
                  && (strpos($filePath, 'i18n') !== false);

        return $result;
    }
}
