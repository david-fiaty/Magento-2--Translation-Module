<?php
/**
 * Copyright © 2018 David Fiaty. All rights reserved.
 */
namespace Naxero\Translation\Model;

/**
 * Translation Config model
 */
class Config extends \Magento\Framework\DataObject
{
	/**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
	/**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface 
     */
    protected $scopeConfig;
	/**
     * @var \Magento\Framework\App\Config\ValueInterface
     */
    protected $backendModel;
	/**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;
	/**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $configValueFactory;
	/**
     * @var int $storeId
     */
    protected $storeId;
	/**
     * @var string $storeCode
     */
    protected $storeCode;

	/**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager,
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
     * @param \Magento\Framework\App\Config\ValueInterface $backendModel,
     * @param \Magento\Framework\DB\Transaction $transaction,
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory,
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\ValueInterface $backendModel,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->backendModel = $backendModel;
        $this->transaction = $transaction;
        $this->configValueFactory = $configValueFactory;
		$this->storeId = (int) $this->storeManager->getStore()->getId();
		$this->storeCode = $this->storeManager->getStore()->getCode();
	}
	
	/**
	 * Function for getting Config value of current store
     * @param string $path,
     */
	public function getCurrentStoreConfigValue($path){
		return $this->scopeConfig->getValue($path,'store',$this->storeCode);
	}
	
	/**
	 * Function for setting Config value of current store
     * @param string $path,
	 * @param string $value,
     */
	public function setCurrentStoreConfigValue($path,$value){
		$data = [
                    'path' => $path,
                    'scope' =>  'stores',
                    'scope_id' => $this->storeId,
                    'scope_code' => $this->storeCode,
                    'value' => $value,
                ];

		$this->backendModel->addData($data);
		$this->transaction->addObject($this->backendModel);
		$this->transaction->save();
	}
}
