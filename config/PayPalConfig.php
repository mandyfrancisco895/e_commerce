<?php
// config/PayPalConfig.php

class PayPalConfig {
    private static $instance = null;
    private $mode;
    private $clientId;
    private $clientSecret;
    private $currency;
    private $returnUrl;
    private $cancelUrl;
    
    private function __construct() {
        // Load .env file
        $this->loadEnv();
        
        $this->mode = getenv('PAYPAL_MODE') ?: 'sandbox';
        $this->currency = getenv('PAYPAL_CURRENCY') ?: 'PHP';
        $this->returnUrl = getenv('PAYPAL_RETURN_URL');
        $this->cancelUrl = getenv('PAYPAL_CANCEL_URL');
        
        if ($this->mode === 'sandbox') {
            $this->clientId = getenv('PAYPAL_SANDBOX_CLIENT_ID');
            $this->clientSecret = getenv('PAYPAL_SANDBOX_SECRET');
        } else {
            $this->clientId = getenv('PAYPAL_LIVE_CLIENT_ID');
            $this->clientSecret = getenv('PAYPAL_LIVE_SECRET');
        }
    }
    
    private function loadEnv() {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                if (!array_key_exists($name, $_ENV)) {
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getMode() {
        return $this->mode;
    }
    
    public function getClientId() {
        return $this->clientId;
    }
    
    public function getClientSecret() {
        return $this->clientSecret;
    }
    
    public function getCurrency() {
        return $this->currency;
    }
    
    public function getReturnUrl() {
        return $this->returnUrl;
    }
    
    public function getCancelUrl() {
        return $this->cancelUrl;
    }
    
    public function getApiUrl() {
        if ($this->mode === 'sandbox') {
            return 'https://api-m.sandbox.paypal.com';
        }
        return 'https://api-m.paypal.com';
    }
}