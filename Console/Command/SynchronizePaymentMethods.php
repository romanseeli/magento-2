<?php
/**
 * WeArePlanet Magento 2
 *
 * This Magento 2 extension enables to process payments with WeArePlanet (https://www.weareplanet.com).
 *
 * @package WeArePlanet_Payment
 * @author Planet Merchant Services Ltd (https://www.weareplanet.com)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)

 */
namespace WeArePlanet\Payment\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WeArePlanet\Payment\Api\PaymentMethodConfigurationManagementInterface;

/**
 * Command to synchronize the payment methods.
 */
class SynchronizePaymentMethods extends Command
{

    /**
     *
     * @var PaymentMethodConfigurationManagementInterface
     */
    private $paymentMethodConfigurationManagement;

    /**
     *
     * @param PaymentMethodConfigurationManagementInterface $paymentMethodConfigurationManagement
     */
    public function __construct(
        PaymentMethodConfigurationManagementInterface $paymentMethodConfigurationManagement)
    {
        parent::__construct();
        $this->paymentMethodConfigurationManagement = $paymentMethodConfigurationManagement;
    }

    protected function configure()
    {
        $this->setName('weareplanet:payment-method:synchronize')->setDescription(
            'Synchronizes the WeArePlanet payment methods.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->paymentMethodConfigurationManagement->synchronize($output);
    }
}