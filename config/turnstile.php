    <?php
    /**
     * Cloudflare Turnstile Configuration
     * 
     * Replace with your actual Turnstile keys from:
     * https://dash.cloudflare.com/?to=/:account/turnstile
     */

    return [
        'site_key' => '0x4AAAAAACCekLWQ-nW80r3O',
        'secret_key' => '0x4AAAAAACCekEw_BIF3dJJcAbZgrs7j3DQ',
        
        'verify_origin' => true, 
        'timeout' => 10, // API request timeout in seconds
    ];