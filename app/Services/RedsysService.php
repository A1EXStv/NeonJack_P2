<?php

namespace App\Services;

class RedsysService
{
    // Test mode credentials
    private const SECRET_KEY  = 'sq7HjrUOBfKmC576ILgskD5srU870gJ7';
    private const MERCHANT_CODE = '999008881';
    private const TERMINAL   = '001';
    public  const TPV_URL    = 'https://sis-t.redsys.es:25443/sis/realizarPago';

    /**
     * Build the three fields needed for the Redsys POST form.
     *
     * @param  int    $amountCents  Amount in euro cents (10€ → 1000)
     * @param  string $order        Unique order reference (12 chars max, digits+letters)
     * @param  string $urlOk
     * @param  string $urlKo
     * @param  string $urlNotification
     * @return array{version: string, params: string, signature: string}
     */
    public function buildPaymentForm(
        int $amountCents,
        string $order,
        string $urlOk,
        string $urlKo,
        string $urlNotification
    ): array {
        $params = [
            'DS_MERCHANT_AMOUNT'          => (string) $amountCents,
            'DS_MERCHANT_ORDER'           => $order,
            'DS_MERCHANT_MERCHANTCODE'    => self::MERCHANT_CODE,
            'DS_MERCHANT_TERMINAL'        => self::TERMINAL,
            'DS_MERCHANT_TRANSACTIONTYPE' => '0',
            'DS_MERCHANT_CURRENCY'        => '978',   // EUR
            'DS_MERCHANT_URLOK'           => $urlOk,
            'DS_MERCHANT_URLKO'           => $urlKo,
            'DS_MERCHANT_MERCHANTURL'     => $urlNotification,
        ];

        $encoded = base64_encode(json_encode($params));
        $signature = $this->sign($encoded, $order);

        return [
            'version'   => 'HMAC_SHA256_V1',
            'params'    => $encoded,
            'signature' => $signature,
        ];
    }

    /**
     * Verify a response from Redsys (notification or redirect).
     * Returns the decoded parameters array on success, or false on invalid signature.
     */
    public function verifyResponse(string $paramsBase64, string $receivedSignature): array|false
    {
        $params = json_decode(base64_decode($paramsBase64), true);
        if (!$params) {
            return false;
        }

        $order = $params['Ds_Order'] ?? $params['DS_MERCHANT_ORDER'] ?? '';
        $expectedSignature = $this->sign($paramsBase64, $order);

        // Base64-url decode both for comparison
        if (!hash_equals(
            $this->base64UrlDecode($expectedSignature),
            $this->base64UrlDecode($receivedSignature)
        )) {
            return false;
        }

        return $params;
    }

    /**
     * Returns true if the response code means the payment was authorised.
     * Redsys: 0000–0099 = success.
     */
    public function isSuccess(array $params): bool
    {
        $code = (int) ($params['Ds_Response'] ?? 9999);
        return $code >= 0 && $code <= 99;
    }

    // -------------------------------------------------------------------------

    private function sign(string $merchantParamsBase64, string $order): string
    {
        $key = base64_decode(self::SECRET_KEY);

        // Redsys HMAC_SHA256_V1: key diversification uses 3DES-CBC, NOT HMAC
        $derivedKey = $this->encrypt3DES($order, $key);

        $rawSignature = hash_hmac('sha256', $merchantParamsBase64, $derivedKey, true);

        return base64_encode($rawSignature);
    }

    /**
     * Redsys key derivation: encrypt data with 3DES-CBC (zero IV, zero padding).
     */
    private function encrypt3DES(string $data, string $key): string
    {
        // Pad data to a multiple of 8 bytes with null bytes
        $pad  = 8 - (strlen($data) % 8);
        $data .= str_repeat("\0", $pad);

        $result = openssl_encrypt(
            $data,
            'DES-EDE3-CBC',
            $key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            "\0\0\0\0\0\0\0\0"
        );

        if ($result === false) {
            throw new \RuntimeException('3DES encryption failed: ' . openssl_error_string());
        }

        return $result;
    }

    private function base64UrlDecode(string $input): string
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }
}
