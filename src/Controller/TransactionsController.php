<?php declare(strict_types=1);
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Netzkollektiv\EasyCredit\Api\IntegrationFactory;
use Teambank\RatenkaufByEasyCreditApiV3\ApiException;
use Teambank\RatenkaufByEasyCreditApiV3\Model\CaptureRequest;
use Teambank\RatenkaufByEasyCreditApiV3\Model\RefundRequest;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 */
class TransactionsController extends AbstractController
{
    private IntegrationFactory $integrationFactory;

    public function __construct(IntegrationFactory $integrationFactory)
    {
        $this->integrationFactory = $integrationFactory;
    }

    protected function getJsonResponseFromException ($response) {
        $response = new Response($response->getResponseBody(), $response->getCode());
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/api/v{version}/easycredit/transaction/{transactionId}", name="api.easycredit.transaction.post", methods={"GET"})
     */
    public function getTransaction(Request $request, $transactionId): Response
    {
        try {
            $transaction = $this->integrationFactory
                ->createTransactionApi()
                ->apiMerchantV3TransactionTransactionIdGet($transactionId);

            return new JsonResponse($transaction);
        } catch (ApiException $e) {
            return $this->getJsonResponseFromException($e);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @Route("/api/v{version}/easycredit/transaction/{transactionId}/capture", name="api.easycredit.transaction.capture", methods={"POST"})
     */
    public function captureTransaction(Request $request, $transactionId): Response
    {
        try {
            $params = $request->request->all();

            $this->integrationFactory
                ->createTransactionApi()
                ->apiMerchantV3TransactionTransactionIdCapturePost(
                    $transactionId,
                    new CaptureRequest(['trackingNumber' => isset($params['trackingNumber']) ? $params['trackingNumber'] : null])
                );

            return new JsonResponse();
        } catch (ApiException $e) {
            return $this->getJsonResponseFromException($e);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * @Route("/api/v{version}/easycredit/transaction/{transactionId}/refund", name="api.easycredit.transaction.refund", methods={"POST"})
     */
    public function refundTransaction(Request $request, $transactionId): Response
    {
        try {
            $params = $request->request->all();

            $this->integrationFactory
                ->createTransactionApi()
                ->apiMerchantV3TransactionTransactionIdRefundPost(
                    $transactionId,
                    new RefundRequest(['value' => $params['value']])
                );

                return new JsonResponse();
        } catch (ApiException $e) {
            return $this->getJsonResponseFromException($e);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
