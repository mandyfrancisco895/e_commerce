<?php
// app/models/PayPalPayment.php

require_once __DIR__ . '/../../config/PayPalConfig.php';

class PayPalPayment {
    private $config;
    private $accessToken;
    
    public function __construct() {
        $this->config = PayPalConfig::getInstance();
    }
    
    /**
     * Get PayPal OAuth Access Token
     */
    private function getAccessToken() {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }
        
        $url = $this->config->getApiUrl() . '/v1/oauth2/token';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_USERPWD, $this->config->getClientId() . ':' . $this->config->getClientSecret());
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Accept-Language: en_US'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to get PayPal access token: ' . $response);
        }
        
        $data = json_decode($response, true);
        $this->accessToken = $data['access_token'];
        
        return $this->accessToken;
    }
    
    /**
     * Create PayPal Order
     * 
     * @param array $orderData Contains: amount, currency, description, items
     * @return array PayPal order details with approval URL
     */
    public function createOrder($orderData) {
        try {
            $accessToken = $this->getAccessToken();
            $url = $this->config->getApiUrl() . '/v2/checkout/orders';
            
            // Prepare order items for PayPal
            // IMPORTANT: Each item's unit_amount must be the PER-UNIT price (not subtotal).
            // PayPal calculates item_total internally as sum(unit_amount * quantity),
            // so item_total in the breakdown MUST exactly equal that sum — or PayPal returns 422.
            $items = [];
            $computedItemTotal = 0.0;

            foreach ($orderData['items'] as $item) {
                $unitPrice  = round(floatval($item['price']), 2);
                $quantity   = intval($item['quantity']);

                // Accumulate the true item total so our breakdown matches PayPal's own math
                $computedItemTotal += $unitPrice * $quantity;

                $items[] = [
                    'name'        => substr($item['name'], 0, 127), // PayPal max 127 chars
                    'description' => substr($item['description'] ?? '', 0, 127),
                    'quantity'    => (string)$quantity,
                    'unit_amount' => [
                        'currency_code' => $this->config->getCurrency(),
                        'value'         => number_format($unitPrice, 2, '.', '')
                    ]
                ];
            }

            // Round to 2 decimals to avoid floating-point drift
            $computedItemTotal = round($computedItemTotal, 2);

            // If the caller passed a total that doesn't match the item sum (e.g. due to
            // rounding), trust the computed sum so PayPal's validation passes.
            $orderTotal = $computedItemTotal;

            // Build PayPal order structure
            $payload = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $orderData['order_id'] ?? uniqid('ORDER-'),
                        'description'  => $orderData['description'] ?? 'Empire Streetwear Purchase',
                        'amount'       => [
                            'currency_code' => $this->config->getCurrency(),
                            'value'         => number_format($orderTotal, 2, '.', ''),
                            'breakdown'     => [
                                // item_total MUST equal sum of (unit_amount.value * quantity)
                                'item_total' => [
                                    'currency_code' => $this->config->getCurrency(),
                                    'value'         => number_format($computedItemTotal, 2, '.', '')
                                ]
                            ]
                        ],
                        'items' => $items
                    ]
                ],
                'application_context' => [
                    'brand_name' => 'EMPIRE STREETWEAR',
                    'landing_page' => 'BILLING',
                    'user_action' => 'PAY_NOW',
                    'return_url' => $this->config->getReturnUrl(),
                    'cancel_url' => $this->config->getCancelUrl()
                ]
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 201) {
                throw new Exception('PayPal order creation failed: ' . $response);
            }
            
            $result = json_decode($response, true);
            
            // Extract approval URL
            $approvalUrl = null;
            foreach ($result['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    $approvalUrl = $link['href'];
                    break;
                }
            }
            
            return [
                'success' => true,
                'order_id' => $result['id'],
                'approval_url' => $approvalUrl,
                'status' => $result['status']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Capture PayPal Order Payment
     * 
     * @param string $orderId PayPal Order ID
     * @return array Capture result
     */
    public function captureOrder($orderId) {
        try {
            $accessToken = $this->getAccessToken();
            $url = $this->config->getApiUrl() . '/v2/checkout/orders/' . $orderId . '/capture';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 201) {
                throw new Exception('Payment capture failed: ' . $response);
            }
            
            $result = json_decode($response, true);
            
            return [
                'success' => true,
                'order_id' => $result['id'],
                'status' => $result['status'],
                'payer' => $result['payer'],
                'purchase_units' => $result['purchase_units']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get Order Details
     * 
     * @param string $orderId PayPal Order ID
     * @return array Order details
     */
    public function getOrderDetails($orderId) {
        try {
            $accessToken = $this->getAccessToken();
            $url = $this->config->getApiUrl() . '/v2/checkout/orders/' . $orderId;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new Exception('Failed to get order details: ' . $response);
            }
            
            return json_decode($response, true);
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}