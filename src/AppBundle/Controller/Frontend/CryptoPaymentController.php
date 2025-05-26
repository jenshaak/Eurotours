<?php

namespace AppBundle\Controller\Frontend;

use AppBundle\Entity\Order;
use AppBundle\Service\CryptoPaymentService;
use AppBundle\Service\PaymentService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CryptoPaymentController extends Controller
{
    /**
     * Create crypto payment for an order
     * 
     * @Route("/order/{order}/crypto-payment", name="crypto_payment_create")
     */
    public function createCryptoPaymentAction(Order $order, Request $request)
    {
        try {
            // Get the selected cryptocurrency from request (default to BTC)
            $cryptocurrency = $request->get('crypto', 'BTC');
            
            // Validate cryptocurrency
            $allowedCryptos = ['BTC', 'ETH', 'LTC', 'BCH', 'DOGE'];
            if (!in_array($cryptocurrency, $allowedCryptos)) {
                $cryptocurrency = 'BTC';
            }

            /** @var CryptoPaymentService $cryptoPaymentService */
            $cryptoPaymentService = $this->get('service.cryptoPayment');
            
            // Create crypto payment
            $payment = $cryptoPaymentService->createCryptoPaymentForOrder($order, $cryptocurrency);
            
            // Save the payment
            $cryptoPaymentService->savePayment($payment);
            
            // Redirect to the CoinRemitter payment page
            return $this->redirect($payment->getUrl());
            
        } catch (\Exception $e) {
            // Log the error
            $this->get('logger')->error('Crypto payment creation failed: ' . $e->getMessage());
            
            // Add flash message and redirect back to order
            $this->addFlash('error', 'Unable to create crypto payment. Please try again or use card payment.');
            return $this->redirectToRoute('order_detail', ['order' => $order->getId()]);
        }
    }

    /**
     * Handle CoinRemitter webhook notifications
     * 
     * @Route("/crypto-payment/webhook", name="crypto_payment_webhook", methods={"POST"})
     */
    public function webhookAction(Request $request)
    {
        try {
            // Get the webhook data
            $webhookData = json_decode($request->getContent(), true);
            
            if (!$webhookData) {
                return new JsonResponse(['status' => 'error', 'message' => 'Invalid JSON'], 400);
            }

            /** @var CryptoPaymentService $cryptoPaymentService */
            $cryptoPaymentService = $this->get('service.cryptoPayment');
            
            // Process the webhook
            $result = $cryptoPaymentService->processCryptoWebhook($webhookData);
            
            if ($result) {
                return new JsonResponse(['status' => 'success']);
            } else {
                return new JsonResponse(['status' => 'error', 'message' => 'Processing failed'], 400);
            }
            
        } catch (\Exception $e) {
            // Log the error
            $this->get('logger')->error('Crypto webhook processing failed: ' . $e->getMessage());
            return new JsonResponse(['status' => 'error', 'message' => 'Internal error'], 500);
        }
    }

    /**
     * Check crypto payment status (AJAX endpoint)
     * 
     * @Route("/order/{order}/crypto-payment/status", name="crypto_payment_status")
     */
    public function checkPaymentStatusAction(Order $order, Request $request)
    {
        try {
            $payment = $order->getPayment();
            
            if (!$payment || !$payment->isCryptoPayment()) {
                return new JsonResponse(['status' => 'error', 'message' => 'No crypto payment found'], 404);
            }

            $response = [
                'status' => 'pending',
                'paid' => $payment->isPaid(),
                'payment_method' => $payment->getPaymentMethod(),
                'crypto_currency' => $payment->getCryptoCurrency(),
                'invoice_id' => $payment->getCryptoInvoiceId()
            ];

            if ($payment->isPaid()) {
                $response['status'] = 'paid';
                $response['paid_at'] = $payment->getDatetimePaid() ? $payment->getDatetimePaid()->format('Y-m-d H:i:s') : null;
            }

            return new JsonResponse($response);
            
        } catch (\Exception $e) {
            $this->get('logger')->error('Crypto payment status check failed: ' . $e->getMessage());
            return new JsonResponse(['status' => 'error', 'message' => 'Status check failed'], 500);
        }
    }
} 