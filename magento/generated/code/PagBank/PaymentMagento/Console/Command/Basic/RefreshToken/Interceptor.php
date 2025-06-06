<?php
namespace PagBank\PaymentMagento\Console\Command\Basic\RefreshToken;

/**
 * Interceptor class for @see \PagBank\PaymentMagento\Console\Command\Basic\RefreshToken
 */
class Interceptor extends \PagBank\PaymentMagento\Console\Command\Basic\RefreshToken implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\State $state, \PagBank\PaymentMagento\Model\Console\Command\Basic\Refresh $refresh)
    {
        $this->___init();
        parent::__construct($state, $refresh);
    }

    /**
     * {@inheritdoc}
     */
    public function run(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output) : int
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'run');
        return $pluginInfo ? $this->___callPlugins('run', func_get_args(), $pluginInfo) : parent::run($input, $output);
    }
}
