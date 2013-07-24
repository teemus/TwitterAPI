<?php

class TwitterAPI {
    private $consumerKey = 'CONSUMER_KEY_GOES_HERE';
    private $consumerSecret = 'CONSUMER_SECRET_GOES_HERE';
    private $nonceSecret = 'NONCE_SECRET_GOES_HERE_IT_CAN_BE_ANY_RANDOM_STRING';
    private $authorizationUrl = 'https://api.twitter.com/oauth/authorize';
    private $requestTokenUrl = 'https://api.twitter.com/oauth/request_token';
    private $accessTokenUrl = 'https://api.twitter.com/oauth/access_token';
    private $callbackUrl, $accessToken, $accessTokenSecret;
    public  $userId, $screenName;
      
    function __construct($appUrl) {
        $this->callbackUrl = $appUrl;
    }
 
    public function getOAuthAuthorizationURL() {
        $url = $this->authorizationUrl;
        $tokenData = $this->getOAuthRequestToken();
        $url .= '?oauth_token='.$tokenData['oauth_token'];
        return $url;
    }

    public function getOAuthAccessToken($oauthToken, $oauthVerifier) {
        $url = $this->accessTokenUrl;
        $ts = time();
        $oauth = array(
                    'oauth_consumer_key' => $this->consumerKey,
                    'oauth_nonce' => $this->generateNonce($ts),
                    'oauth_signature_method' => 'HMAC-SHA1',
                    'oauth_timestamp' => $ts,
                    'oauth_token' => $oauthToken,
                    'oauth_version' => '1.0',
                    'oauth_verifier' => $oauthVerifier
                    );

        $baseInfo = $this->buildBaseString($url, 'POST', $oauth);
        $compositeKey = rawurlencode($this->consumerSecret).'&';
        $oauthSignature = $this->generateSignature($baseInfo, $compositeKey);
        $oauth['oauth_signature'] = $oauthSignature;
        $header = array($this->buildAuthorizationHeader($oauth), 'Expect:');
        $response = $this->makeHttpRequest($url, $header);
        parse_str($response['response'], $accessTokenData);
        if (isset($accessTokenData['oauth_token']) && isset($accessTokenData['oauth_token_secret'])) {
            $this->accessToken = $accessTokenData['oauth_token'];    
            $this->accessTokenSecret = $accessTokenData['oauth_token_secret'];
            $this->userId = $accessTokenData['user_id'];
            $this->screenName = $accessTokenData['screen_name'];
            return true;
        }
        return false;
    }

    /**
        Sample URL requiring a 'GET' HTTP call
        $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
    */
    public function invokeAPI($url, $method = 'GET', $queryParams = array(), $postParams = array()) {
        $ts = time();
        $apiUrl = $url;
        $oauth = array(
               'oauth_consumer_key' => $this->consumerKey,
               'oauth_nonce' => $this->generateNonce($ts),
               'oauth_signature_method' => 'HMAC-SHA1',
               'oauth_token' => $this->accessToken,
               'oauth_timestamp' => $ts,
               'oauth_version' => '1.0'
              );
        
        if (!empty($queryParams)) {
            $apiUrl .= '?'.http_build_query($queryParams);
            foreach ($queryParams as $key => $value) {
                $oauth[$key] = $value;
            }
        }

        $postData = '';
        if (!empty($postParams)) {
            foreach ($postParams as $key => $value) {
                $postData .= $key.'='.rawurlencode($value);
            }
            $postData .= '&';
        }
        $postData = substr($postData, 0, -1);

        $baseInfo = $this->buildBaseString($url, $method, $oauth);
        $compositeKey = rawurlencode($this->consumerSecret) . '&' . rawurlencode($this->accessTokenSecret);
        $oauthSignature = $this->generateSignature($baseInfo, $compositeKey);
        $oauth['oauth_signature'] = $oauthSignature;
        $header = array($this->buildAuthorizationHeader($oauth), 'Expect:');
        $isPost = ($method == 'POST') ? true : false;
        $response = $this->makeHttpRequest($apiUrl, $header, $isPost, $postData);
        return $response['response'];
    }

    private function makeHttpRequest($url, $header = null, $isPost = true, $postData = '') {
        $ch = curl_init();
        $options = array(
                CURLOPT_HTTPHEADER => $header, 
                CURLOPT_HEADER => false,
                CURLOPT_URL => $url,
                CURLOPT_POST => $isPost,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false
            );
        if ($isPost) {
            $options[CURLOPT_POSTFIELDS] = $postData;
        }    
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return array('response' => $response, 'info' => $info);
    }

    private function getOAuthRequestToken() {
        $url = $this->requestTokenUrl;
        $ts = time();
        $oauth = array(
                    'oauth_consumer_key' => $this->consumerKey,
                    'oauth_nonce' => $this->generateNonce($ts),
                    'oauth_signature_method' => 'HMAC-SHA1',
                    'oauth_timestamp' => $ts,
                    'oauth_version' => '1.0',
                    'oauth_callback' => $this->callbackUrl
                    );

        $baseInfo = $this->buildBaseString($url, 'POST', $oauth);
        $compositeKey = rawurlencode($this->consumerSecret).'&';
        $oauthSignature = $this->generateSignature($baseInfo, $compositeKey);
        $oauth['oauth_signature'] = $oauthSignature;
        $header = array($this->buildAuthorizationHeader($oauth), 'Expect:');
        $response = $this->makeHttpRequest($url, $header);
        parse_str($response['response'], $requestTokenData);
        return $requestTokenData;
    }

    private function buildBaseString($baseURI, $method, $params) {
        $r = array();
        ksort($params);
        foreach ($params as $key => $value) {
            $r[] = $key.'='.rawurlencode($value);
        }
        return $method.'&'.rawurlencode($baseURI).'&'.rawurlencode(implode('&', $r));
    }

    private function buildAuthorizationHeader($oauth) {
        $r = 'Authorization: OAuth ';
        $values = array();
        foreach ($oauth as $key => $value) {
            $values[] = $key.'="'.rawurlencode($value).'"';
        }
        $r .= implode(', ', $values);
        return $r;
    }

    private function generateNonce($ts) {
        return sha1($this->nonceSecret.$ts);
    }

    private function generateSignature($info, $key) {
        return base64_encode(hash_hmac('sha1', $info, $key, true));
    }     
}


