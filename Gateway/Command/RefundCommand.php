<?php
/**
 WeArePlanet Magento 2
 *
 * This Magento 2 extension enables to process payments with WeArePlanet (https://www.weareplanet.com).
 *
 * @package WeArePlanet_Payment
 * @author Planet Merchant Services Ltd (https://www.weareplanet.com)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)

 */
namespace WeArePlanet\Payment\Gateway\Command;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Psr\Log\LoggerInterface;
use WeArePlanet\Payment\Api\RefundJobRepositoryInterface;
use WeArePlanet\Payment\Helper\Locale as LocaleHelper;
use WeArePlanet\Payment\Model\ApiClient;
use WeArePlanet\Payment\Model\Service\RefundService;
use WeArePlanet\Sdk\Model\RefundState;
use WeArePlanet\Sdk\Service\RefundService as ApiRefundService;

/**
 * Payment gateway command to refund a payment.
 */
class RefundCommand implements CommandInterface
{

    /**
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @var LocaleHelper
     */
    private $localeHelper;

    /**
     *
     * @var RefundJobRepositoryInterface
     */
    private $refundJobRepository;

    /**
     *
     * @var RefundService
     */
    private $refundService;

    /**
     *
     * @var ApiClient
     */
    private $apiClient;

    /**
     *
     * @param LoggerInterface $logger
     * @param LocaleHelper $localeHelper
     * @param RefundJobRepositoryInterface $refundJobRepository
     * @param RefundService $refundService
     * @param ApiClient $apiClient
     */
    public function __construct(LoggerInterface $logger, LocaleHelper $localeHelper,
        RefundJobRepositoryInterface $refundJobRepository, RefundService $refundService, ApiClient $apiClient)
    {
        $this->logger = $logger;
        $this->localeHelper = $localeHelper;
        $this->refundJobRepository = $refundJobRepository;
        $this->refundService = $refundService;
        $this->apiClient = $apiClient;
    }

    public function execute(array $commandSubject)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = SubjectReader::readPayment($commandSubject)->getPayment();
        $creditmemo = $payment->getCreditmemo();

        if ($creditmemo->getWeareplanetExternalId() == null) {
            try {
                $refundJob = $this->refundJobRepository->getByOrderId($payment->getOrder()
                    ->getId());
            } catch (NoSuchEntityException $e) {
                $refund = $this->refundService->createRefund($creditmemo);
                $refundJob = $this->refundService->createRefundJob($creditmemo->getInvoice(), $refund);
            }

            try {
                $refund = $this->apiClient->getService(ApiRefundService::class)->refund(
                    $creditmemo->getOrder()
                        ->getWeareplanetSpaceId(), $refundJob->getRefund());
            } catch (\WeArePlanet\Sdk\ApiException $e) {
                if ($e->getResponseObject() instanceof \WeArePlanet\Sdk\Model\ClientError) {
                    $this->refundJobRepository->delete($refundJob);
                    throw new \Magento\Framework\Exception\LocalizedException(
                        \__($e->getResponseObject()->getMessage()));
                } else {
                    $creditmemo->setWeareplanetKeepRefundJob(true);
                    $this->logger->critical($e);
                    throw new \Magento\Framework\Exception\LocalizedException(
                        \__('There has been an error while sending the refund to the gateway.'));
                }
            } catch (\Exception $e) {
                $creditmemo->setWeareplanetKeepRefundJob(true);
                $this->logger->critical($e);
                throw new \Magento\Framework\Exception\LocalizedException(
                    \__('There has been an error while sending the refund to the gateway.'));
            }

            if ($refund->getState() == RefundState::FAILED) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    \__($this->localeHelper->translate($refund->getFailureReason()
                        ->getDescription())));
            } elseif ($refund->getState() == RefundState::PENDING || $refund->getState() == RefundState::MANUAL_CHECK) {
                $creditmemo->setWeareplanetKeepRefundJob(true);
                throw new \Magento\Framework\Exception\LocalizedException(
                    \__('The refund was requested successfully, but is still pending on the gateway.'));
            }

            $creditmemo->setWeareplanetExternalId($refund->getExternalId());
            $this->refundJobRepository->delete($refundJob);
        }
    }
}