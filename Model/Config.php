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

namespace Naxero\Translation\Model;

/**
 * Translation Config model
 */
class Config extends \Magento\Framework\DataObject
{
	/**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

	/**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface 
     */
    public $scopeConfig;

	/**
     * @var \Magento\Framework\App\Config\ValueInterface
     */
    public $backendModel;

	/**
     * @var \Magento\Framework\DB\Transaction
     */
    public $transaction;

	/**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    public $configValueFactory;

	/**
     * @var int $storeId
     */
    public $storeId;

	/**
     * @var string $storeCode
     */
    public $storeCode;

	/**
     * Config class constructor
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
	 * Function for getting config value of current store
     * @param string $path,
     */
	public function getCurrentStoreConfigValue($path){
		return $this->scopeConfig->getValue($path, 'store', $this->storeCode);
	}
	
	/**
	 * Function for setting config value of current store
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
