<?php
/**
 * WeArePlanet Magento 2
 *
 * This Magento 2 extension enables to process payments with WeArePlanet (https://www.weareplanet.com//).
 *
 * @package WeArePlanet_Payment
 * @author wallee AG (http://www.wallee.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */
namespace WeArePlanet\Payment\Model\Webhook\Listener;

/**
 * Webhook listener command pool interface.
 */
interface CommandPoolInterface
{

    /**
     * Retrieves listener.
     *
     * @param string $commandCode
     * @return CommandInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function get($commandCode);
}