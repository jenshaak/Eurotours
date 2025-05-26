<?php

namespace AppBundle\Service;

use AppBundle\Entity\Order;
use AppBundle\Entity\Payment;
use AppBundle\Repository\PaymentRepository;
use CoinRemitter\CoinRemitter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;

class CryptoPaymentService
{
    const PAYMENT_METHOD_CRYPTO = 'crypto';
    
    /**
     * @var ContainerInterface
     */
    private $container;
    
    /**
     * @var Router
     */
    private $router;
    
    /**
     * @var PaymentRepository
     */
    private $paymentRepository;
    
    /**
     * @var CurrencyService
     */
    private $currencyService;

    public function __construct(ContainerInterface $container,
                                Router $router,
                                PaymentRepository $paymentRepository,
                                CurrencyService $currencyService)
    {
        $this->container = $container;
        $this->router = $router;
        $this->paymentRepository = $paymentRepository;
        $this->currencyService = $currencyService;
    }

    /**
     * Create a crypto payment for an order
     * 
     * @param Order $order
     * @param string $cryptocurrency (BTC, ETH, LTC, etc.)
     * @return Payment
     */
    public function createCryptoPaymentForOrder(Order $order, $cryptocurrency = 'BTC')
    {
        $payment = new Payment();
        $payment->setIdent(time() . rand(100, 999));
        $payment->setPrice($order->getPrice() + $order->getSellerFee());
        $payment->setOrder($order);
        $payment->setCurrency($order->getCurrency());
        $payment->setPaymentMethod(self::PAYMENT_METHOD_CRYPTO);
        $order->setPayment($payment);

        // Create CoinRemitter invoice
        $invoiceUrl = $this->createCoinRemitterInvoice($payment, $cryptocurrency);
        $payment->setUrl($invoiceUrl);

        return $payment;
    }

    /**
     * Create a CoinRemitter invoice
     * 
     * @param Payment $payment
     * @param string $cryptocurrency
     * @return string Invoice URL
     */
    private function createCoinRemitterInvoice(Payment $payment, $cryptocurrency)
    {
        try {
            // Initialize CoinRemitter with API credentials
            $coinRemitter = new CoinRemitter(
                $this->container->getParameter('coinremitter_api_key'),
                $this->container->getParameter('coinremitter_password')
            );

            // Convert price to USD if needed (CoinRemitter works with USD)
            $priceInUSD = $this->convertToUSD($payment->getPrice(), $payment->getCurrency());

            // Create invoice parameters
            $invoiceParams = [
                'amount' => number_format($priceInUSD, 2, '.', ''),
                'name' => 'EuroTours - Order #' . $payment->getOrder()->getId(),
                'email' => $payment->getOrder()->getEmail(),
                'fiat_currency' => 'USD',
                'expiry_time_in_minutes' => 60, // 1 hour expiry
                'notify_url' => $this->router->generate('crypto_payment_webhook', [], Router::ABSOLUTE_URL),
                'success_url' => $this->router->generate('order_paid', ['order' => $payment->getOrder()->getId()], Router::ABSOLUTE_URL),
                'fail_url' => $this->router->generate('order_detail', ['order' => $payment->getOrder()->getId()], Router::ABSOLUTE_URL),
                'description' => 'Payment for EuroTours booking',
                'custom_data1' => $payment->getId(),
                'custom_data2' => $payment->getIdent(),
            ];

            // Create the invoice
            $response = $coinRemitter->createInvoice($invoiceParams);

            if ($response && isset($response['success']) && $response['success']) {
                // Store additional crypto payment data
                $payment->setCryptoInvoiceId($response['data']['invoice_id']);
                $payment->setCryptoCurrency($cryptocurrency);
                
                return $response['data']['url'];
            } else {
                throw new \Exception('Failed to create CoinRemitter invoice: ' . json_encode($response));
            }

        } catch (\Exception $e) {
            // Log error and fallback
            error_log('CoinRemitter Error: ' . $e->getMessage());
            throw new \Exception('Unable to create crypto payment. Please try again or use card payment.');
        }
    }

    /**
     * Convert price to USD
     * 
     * @param float $price
     * @param string $currency
     * @return float
     */
    private function convertToUSD($price, $currency)
    {
        if ($currency === 'USD') {
            return $price;
        }

        // Use your existing currency service to convert to USD
        // This is a simplified example - adjust based on your currency service
        if ($currency === 'CZK') {
            // Approximate conversion - you should use real exchange rates
            return $price / 23; // Rough CZK to USD conversion
        }

        if ($currency === 'EUR') {
            return $price * 1.1; // Rough EUR to USD conversion
        }

        // Default fallback
        return $price;
    }

    /**
     * Process crypto payment webhook
     * 
     * @param array $webhookData
     * @return bool
     */
    public function processCryptoWebhook($webhookData)
    {
        try {
            if (!isset($webhookData['custom_data1'])) {
                return false;
            }

            $paymentId = $webhookData['custom_data1'];
            $payment = $this->paymentRepository->find($paymentId);

            if (!$payment) {
                return false;
            }

            // Check if payment is confirmed
            if (isset($webhookData['status']) && $webhookData['status'] === 'Paid') {
                return $this->markPaymentAsPaid($payment);
            }

            return true;
        } catch (\Exception $e) {
            error_log('Crypto webhook error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark payment as paid
     * 
     * @param Payment $payment
     * @return bool
     */
    private function markPaymentAsPaid(Payment $payment)
    {
        if ($payment->isPaid()) {
            return false;
        }

        $payment->setPaid(true);
        $payment->setDatetimePaid(new \DateTime());
        
        if ($payment->getOrder()) {
            $payment->getOrder()->setPaid(true);
            $payment->getOrder()->setDatetimePaid(new \DateTime());
            $payment->getOrder()->setStatus(Order::STATUS_PAID);
        }

        $this->paymentRepository->save($payment);
        return true;
    }

    /**
     * Save payment
     * 
     * @param Payment $payment
     */
    public function savePayment(Payment $payment)
    {
        $this->paymentRepository->save($payment);
    }
} 