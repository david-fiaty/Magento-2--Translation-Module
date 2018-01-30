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
use Naxero\Translation\Helper\Data;

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
     * @var Data
     */
    protected $helper;

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
        FileEntityFactory $fileEntityFactory,
        Data $helper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->tree = $tree;
        $this->fileEntityFactory = $fileEntityFactory;
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
        // Prepare the output
        $result = $this->resultJsonFactory->create();

        // Prepare the output
        $output = array();

        // Loop through the directory tree
        if ($this->getRequest()->isAjax()) 
        {
            // Get the update mode
            //$update_mode = $this->getRequest()->getParam('update_mode');

            // Clear the table data
            $this->clearTableData();
            /*if ($update_mode == 'update_replace') {
                $this->clearTableData();
            }*/

            // Get the root directory
            $rootPath = $this->tree->getRoot();

            $rdi = new \RecursiveDirectoryIterator($rootPath);
            foreach(new \RecursiveIteratorIterator($rdi) as $filePath)
            {
                if ($this->isWantedFile($filePath)) {
                    $output['table_data'][] = $this->saveFile($filePath);
                }
            }
        }

        return $result->setData($output);
    }

    public function clearTableData() {
        $fileEntity = $this->fileEntityFactory->create(); 
        $connection = $fileEntity->getCollection()->getConnection();
        $tableName  = $fileEntity->getCollection()->getMainTable();
        $connection->truncateTable($tableName);
    }

    public function saveFile($filePath)
    {
        // Get the clean path
        $cleanPath = $this->helper->getCleanPath($filePath);

        // Save the item
        $fileEntity = $this->fileEntityFactory->create(); 
        $fileEntity->setData('file_path', $cleanPath);
        $fileEntity->setData('file_content', file_get_contents($filePath));
        $fileEntity->setData('file_is_active', 1);
        $fileEntity->setData('file_creation_time', date("Y-m-d H:i:s"));
        $fileEntity->setData('file_update_time', date("Y-m-d H:i:s"));

        $fileEntity->save();

        return (object) $this->helper->getFieldFormats($fileEntity->getData(), $fileEntity);
    }

    public function isWantedFile($filePath)
    {
        // Validate the conditions - Todo : move exclusion config settings 
        $result = (pathinfo($filePath, PATHINFO_EXTENSION) == 'csv')
                  && (is_file($filePath))
                  && (strpos($filePath, 'i18n') !== false);
                  //&& !$this->isIndexed($filePath);          

        return $result;
    }

    public function isIndexed($filePath) {
        // Get the update mode
        $update_mode = $this->getRequest()->getParam('update_mode');

        if ($update_mode == 'update_add') {
            // Get the clean path
            $cleanPath = $this->helper->getCleanPath($filePath);

            // Create the collection
            $fileEntity = $this->fileEntityFactory->create(); 
            $collection = $fileEntity->getCollection();

            // Prepare the output array
            foreach($collection as $item)
            {
                if ($fileEntity->getData('file_path') == $cleanPath) {
                    return true;
                }
            }    
        }

        return false;    
    }
}
