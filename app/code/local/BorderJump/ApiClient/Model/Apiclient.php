<?php
class BorderJump_ApiClient_Model_Apiclient
{
    protected $_VALID_METHODS = array('POST' => 'POST', 'GET' => 'GET', 'PUT' => 'PUT', 'DELETE' => 'DELETE');
    
    protected $_isLoggingEnabled = true;
    protected $_requestBodyArray;
    protected $_requestBody;
    protected $_requestMethod;
    protected $_requestHeaders;
    protected $_requestUrl;
    protected $_url;
    protected $_key;
    protected $_secret;
    protected $_lang;
    protected $_response;
    protected $_zendClient;
    
    /**
     * @param string $url Base URL for the API
     * @param string $key Client account's public key
     * @param string $secret Client account's paired secret key
     * @param string $lang 2 character default language code
     */
    public function __construct ($url, $key=null, $secret=null, $lang='en') {
        $this->_url = rtrim($url, '/');
        $this->_lang = $lang;
        $this->_key = $key;
        $this->_secret = $secret;
        $this->_zendClient = new Zend_Http_Client(null, array('strict' => false));
        $this->_zendClient->setEncType('application/json');
        $this->_prepareSsl();
    }
    
    /**
     * Prepare SSL settings.
     * 
     * @return BorderJump_ApiClient_Model_Apiclient
     */
    private function _prepareSsl() {
        $options = array(
            'https' => array(
                // Verify server side certificate,
                // do not accept invalid or self-signed SSL certificates
                'verify_peer' => true,
                'allow_self_signed' => false,
                
                // Capture the peer's certificate
                'capture_peer_cert' => true
            )
        );
        
        // Create an adapter object and attach it to the HTTP client
        $adapter = new Zend_Http_Client_Adapter_Socket();
        $adapter->setStreamContext($options);
        $this->_zendClient->setAdapter($adapter);
        return $this;
    }
    
    /**
     * Generate the headers needed for a BorderJump API call.
     * 
     * @param array $content
     * @param array $headers Any additional headers to be sent
     * @return array
     */
    protected function _generateHeaders($content, $headers = array()) {
        $datetime = gmstrftime("%Y-%m-%dT%H:%M:%SZ");
        $json = json_encode(Mage::helper('apiclient')->kSortRecursive($content, true));
        $json = utf8_encode($json);
        $body_hash = hash('sha256', utf8_encode($json));
        $concat = utf8_encode($body_hash . $datetime . $this->_secret);
        
        $headers['Content-Type'] = 'application/json';
        $headers['body_hash'] = $body_hash;
        $headers['lang'] = $this->_lang;
        $headers['key'] = $this->_key;
        $headers['datetime'] = $datetime;
        $headers['signature'] = hash('sha256', $concat);
        
        return $headers;
    }
    
    /**
     * Sanitize a field.
     * 
     * @param mixed $value Modified in place.
     * @param string $key
     */
    protected function _sanitizeValue(&$value, $key) {
        $saniKeys = array(
            'postalCode', 'country', 'phoneNumber1', 'phoneNumber2',
            'city', 'firstName', 'lastName', 'region', 'street1',
            'street2', 'street3'
        );
        
        if (! is_string($value)) {
            return;
        }
        
        $value = trim($value);
        
        if (! in_array($key, $saniKeys)) {
            return;
        }
        
        // matches !@#$%^*()+=`~/?;:,[]\{}
        $pattern = '/[!@#$%^*()+=`~\/?;:,[\]\\{}]/';
        $value = preg_replace($pattern, '', $value);
    }
    
    /**
     * Sanitize a request.
     * 
     * @param array $request Modified in place.
     * @return BorderJump_ApiClient_Model_Apiclient
     */
    protected function _sanitize(&$request) {
        array_walk_recursive($request, array($this, '_sanitizeValue'));
        return $this;
    }
    
    /**
     * Prepare the request body.
     * 
     * @param array $content
     * @return string
     */
    protected function _prepareBody($content) {
        $this->_sanitize($content);
        return json_encode($content);
    }
    
    /**
     * Prepare the final URL.
     * 
     * @param string $action
     * @return string
     */
    protected function _prepareUrl($action) {
        $action = trim($action);
        return rtrim($this->_url, '/') . '/' . ltrim($action, '/');
    }
    
    /**
     * Prepare the headers.
     * 
     * @param array $headers
     * @return array
     */
    protected function _prepareHeaders($headers) {
        return $this->_generateHeaders($headers);
    }
    
    /**
     * Log the API request.
     * 
     * @return BorderJump_ApiClient_Model_Apiclient
     */
    public function logRequest() {
        Mage::log(array(
            'REQUEST URL' => $this->_requestUrl,
            'REQUEST HEADERS' => $this->_requestHeaders,
            'REQUEST METHOD' => $this->_requestMethod,
            'REQUEST BODY' => $this->_requestBodyArray
        ));
        return $this;
    }
    
    /**
     * Log the API response.
     * 
     * @return BorderJump_ApiClient_Model_Apiclient
     */
    public function logResponse() {
        $response = $this->_response;
        $responseBody = $response->getBody();
        $responseBodyArray = json_decode($responseBody, true);
        
        Mage::log(array(
            'RESPONSE STATUS CODE' => $response->getStatus(),
            'RESPONSE BODY' => $responseBodyArray,
            'RESPONSE RAW BODY' => $responseBody,
            'RESPONSE HEADERS' => $response->getHeaders()
        ));
        return $this;
    }
    
    /**
     * Set API key.
     * 
     * @param string $key
     * @return BorderJump_ApiClient_Model_Apiclient
     */
    public function setKey($key) {
        $this->_key = $key;
        return $this;
    }
    
    /**
     * Set API secret.
     * 
     * @param string $secret
     * @return BorderJump_ApiClient_Model_Apiclient
     */
    public function setSecret($secret) {
        $this->_secret = $secret;
        return $this;
    }
    
    /**
     * Set language.
     * 
     * @param string $lang
     * @return BorderJump_ApiClient_Model_Apiclient
     */
    public function setLang($lang) {
        $this->_lang = $lang;
        return $this;
    }
    
    /**
     * Set if logging is enabled.
     * 
     * @param bool $bool
     * @return BorderJump_ApiClient_Model_Apiclient
     */
    public function setIsLoggingEnabled($bool) {
        if ($bool === true) {
            $this->_isLoggingEnabled = true;
        } elseif ($bool === false) {
            $this->_isLoggingEnabled = false;
        }
        return $this;
    }
    
    /**
     * Get the last response.
     * 
     * @return null|Zend_Http_Response
     */
    public function getLastResponse() {
        return $this->_response;
    }
    
    /**
     * Gets the error string from the last response.
     * 
     * @return string|null Will return null if there was no error or if the response body isn't json encoded.
     */
    public function getError() {
        $responseArray = json_decode($this->_response->getBody(), true);
        if (isset($responseArray['response']['error'])) {
            return $responseArray['response']['error'];
        }
        return null;
    }
    
    /**
     * Make a BorderJump API call.
     *
     * @param string $action The endpoint, e.g. "/merchant/order". Can end or begin with or without slashes.
     * @param string $method The HTTP method to use. Case insensitive, but using all caps is clearer.
     * @param array $content The array that will be JSON encoded for the request body.
     * @param array $headers Extra headers to be sent with the mandatory BorderJump API headers.
     */
    public function call($action, $method = "POST", $content = array(), $headers = array()) {
        $this->_requestBodyArray = $content;
        $this->_requestBody = $this->_prepareBody($content);
        $this->_requestUrl = $this->_prepareUrl($action);
        $this->_requestHeaders = $this->_prepareHeaders($headers);
        $this->_requestMethod = strtoupper($method);
        
        if (! in_array($this->_requestMethod, $this->_VALID_METHODS)) {
            Mage::throwException("$method is not a valid method.");
        }
        
        $this->_zendClient->setUri($this->_requestUrl);
        $this->_zendClient->setHeaders($this->_requestHeaders);
        $this->_zendClient->setMethod($this->_requestMethod);
        $this->_zendClient->setRawData($this->_requestBody);
        
        if ($this->_isLoggingEnabled) {
            $this->logRequest();
        }
        
        $this->_response = $this->_zendClient->request();
        
        if ($this->_isLoggingEnabled) {
            $this->logResponse();
        }
        
        return json_decode($this->_response->getBody(), true);
    }
}
