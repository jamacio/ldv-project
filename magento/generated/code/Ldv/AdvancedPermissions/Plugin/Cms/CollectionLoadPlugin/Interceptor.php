<?php
namespace Ldv\AdvancedPermissions\Plugin\Cms\CollectionLoadPlugin;

/**
 * Interceptor class for @see \Ldv\AdvancedPermissions\Plugin\Cms\CollectionLoadPlugin
 */
class Interceptor extends \Ldv\AdvancedPermissions\Plugin\Cms\CollectionLoadPlugin implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory, \Psr\Log\LoggerInterface $logger, \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy, \Magento\Framework\Event\ManagerInterface $eventManager, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\EntityManager\MetadataPool $metadataPool, \Ldv\AdvancedPermissions\Model\User\Config $userConfig, \Ldv\AdvancedPermissions\Model\Config $moduleConfig, \Ldv\AdvancedPermissions\Model\AdvancedPermissionFactory $advancedPermissionFactory, \Ldv\AdvancedPermissions\Model\RoleScopeFactory $roleScopeFactory, ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null, ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null)
    {
        $this->___init();
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $storeManager, $metadataPool, $userConfig, $moduleConfig, $advancedPermissionFactory, $roleScopeFactory, $connection, $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurPage($displacement = 0)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getCurPage');
        return $pluginInfo ? $this->___callPlugins('getCurPage', func_get_args(), $pluginInfo) : parent::getCurPage($displacement);
    }
}
