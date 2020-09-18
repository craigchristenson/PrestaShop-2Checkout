<?php

/**
 * makes API calls with 2CO platform
 * Class TwoCheckoutApi
 * @package TwoCheckout
 */
class TwoCheckoutApi
{
    /**
     * used for auth with 2co api
     * @var string
     */
    private $sellerId;

    /**
     * used for auth with 2co api
     * @var string
     */
    private $secretKey;

    /**
     * used for inline JWT
     * @var string
     */
    private $secretWord;

    /**
     * place test order
     * @var int
     */
    private $testOrder;


    const   API_URL = 'https://api.2checkout.com/rest/';
    const   JWT_TOKEN_URL = 'https://secure.2checkout.com/checkout/api/encrypt/generate/signature';
    const   API_VERSION = '6.0';

    /**
     * TwoCheckoutApi constructor.
     */
    public function __construct()
    {

    }

    /**
     * @return string
     */
    public function getSecretWord()
    {
        return $this->secretWord;
    }

    /**
     * @param mixed $secretWord
     */
    public function setSecretWord(string $secretWord)
    {
        $this->secretWord = $secretWord;
    }

    /**
     * @return null
     */
    public function getSellerId()
    {
        return $this->sellerId;
    }

    /**
     * @param null $sellerId
     * @return TwoCheckoutApi
     */
    public function setSellerId($sellerId)
    {
        $this->sellerId = $sellerId;

        return $this;
    }

    /**
     * @return null
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @param null $secretKey
     * @return TwoCheckoutApi
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     *  sets the header with the auth has and params
     * @return array
     * @throws Exception
     */
    private function getHeaders()
    {
        if (!$this->sellerId || !$this->secretKey) {
            throw new Exception('Merchandiser needs a valid 2Checkout SellerId and SecretKey to authenticate!');
        }
        $gmtDate = gmdate('Y-m-d H:i:s');
        $string = strlen($this->sellerId) . $this->sellerId . strlen($gmtDate) . $gmtDate;
        $hash = hash_hmac('md5', $string, $this->secretKey);

        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Accept: application/json';
        $headers[] = 'X-Avangate-Authentication: code="' . $this->sellerId . '" date="' . $gmtDate . '" hash="' . $hash . '"';;

        return $headers;
    }

    /**
     * @param        $endpoint
     * @param        $params
     * @param string $method
     * @return mixed
     * @throws Exception
     */
    public function call($endpoint, $params, $method = 'POST')
    {
        // if endpoint does not starts or end with a '/' we add it, as the API needs it
        if ($endpoint[0] !== '/') {
            $endpoint = '/' . $endpoint;
        }
        if ($endpoint[-1] !== '/') {
            $endpoint = $endpoint . '/';
        }

        try {
            $url = self::API_URL . self::API_VERSION . $endpoint;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, JSON_UNESCAPED_UNICODE));
            }
            $response = curl_exec($ch);

            if ($response === false) {
                exit(curl_error($ch));
            }
            curl_close($ch);

            return json_decode($response, true);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param $params
     * @return mixed
     * @throws Exception
     */
    public function getInlineSignature($params)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::JWT_TOKEN_URL);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getJWTokenHeaders());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            $response = curl_exec($ch);

            if ($response === false) {
                exit(curl_error($ch));
            }
            curl_close($ch);
            $result = json_decode($response, true);

            return $result['signature'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    /**
     * @return string[]
     * @throws Exception
     */
    private function getJWTokenHeaders()
    {
        if (!$this->sellerId || !$this->getSecretWord()) {
            throw new Exception('Merchandiser needs a valid 2Checkout SellerId and SecretWord to authenticate!');
        }

        $header = $this->encode(json_encode(['alg' => 'HS512', 'typ' => 'JWT']));
        $payload = $this->encode(json_encode(['sub' => $this->getSellerId(), 'iat' => time(), 'exp' => time() + 3600]));
        $signature = $this->encode(
            hash_hmac('sha512', "$header.$payload", $this->getSecretWord(), true)
        );
        $jwtToken = implode('.', [$header, $payload, $signature]);

        return [
            'content-type: application/json',
            'cache-control: no-cache',
            'merchant-token: ' . $jwtToken
        ];
    }

    /**
     * @param $data
     * encodes an array
     * @return string|string[]
     */
    private function encode($data)
    {

        return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
    }
}
