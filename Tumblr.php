<?php

/**
 * Copyright 2013,  Kerem Gunes <http://qeremy.com/>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

/**
 * @class Tumblr v0.1
 *
 * Provides access to the Tumblr API/v2 interface. Before using it
 * be sure that you already know all Tumblr API/v2 specs. See more
 * details here: http://www.tumblr.com/docs/en/api/v2
 */
class Tumblr
{
    const
        // Request-token URL
        REQ_URI = 'http://www.tumblr.com/oauth/request_token',
        // Authorize URL
        AUT_URI = 'http://www.tumblr.com/oauth/authorize',
        // Access-token URL
        ACC_URI = 'http://www.tumblr.com/oauth/access_token';
    
    // Tumblr API/v2 URL
    protected $_apiUrl = 'http://api.tumblr.com/v2';
    
    protected 
        // OAuth Object instance
        $_oauth = null,
        // Tumblr OAuth Consumer Key
        $_consKey,
        // Tumblr Secret Key
        $_secrKey,
        // Tumblr API/v2 response success codes
        $_successStatuses = array(200, 201),
        // Whether response error will thrown or not
        $_throwResponseErrors = false;
    
    /**
     * Initialize a Tumblr Object.
     *
     * @param string $consKey (required)
     * @param string $secrKey (required)
     */
    public function __construct($consKey, $secrKey) {
        $this->_consKey = $consKey;
        $this->_secrKey = $secrKey;
    }
    
    /**
     * Initialize a OAuth Object if required.
     * 
     * Note: That must be called (at least once) before all OAuth authentication-required methods.
     *
     * @param string $consKey (required)
     * @param string $secrKey (required)
     * @return Tumblr::_oauth
     * @throw OAuthException
     */
    public function createOauth() {
        if ($this->_oauth === null) {
            // Needs oauth extension
            if (!extension_loaded('oauth')) {
                throw new TumblrException('OAuth extension not installed.');
            }
            
            // Start session if not exists
            if (!session_id()) {
                session_start();
            }
            
            // Reset state
            if (!isset($_GET['oauth_token']) && $_SESSION['tumblr_oauth_state'] == 1) {
                $_SESSION['tumblr_oauth_state'] = 0;
            }
            
            try {
                $oauth = new OAuth($this->_consKey, $this->_secrKey, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
                $oauth->enableDebug();
                if (!isset($_GET['oauth_token']) && !$_SESSION['tumblr_oauth_state']) {
                    $requestToken = $oauth->getRequestToken(self::REQ_URI);
                    $_SESSION['tumblr_oauth_state']  = 1;
                    $_SESSION['tumblr_oauth_secret'] = $requestToken['oauth_token_secret'];
                    header('Location: '. sprintf('%s?oauth_token=%s', self::AUT_URI, $requestToken['oauth_token']));
                    exit;
                } elseif ($_SESSION['tumblr_oauth_state'] == 1) {
                    $oauth->setToken($_GET['oauth_token'], $_SESSION['tumblr_oauth_secret']);
                    $accessToken = $oauth->getAccessToken(self::ACC_URI);
                    $_SESSION['tumblr_oauth_state']  = 2;
                    $_SESSION['tumblr_oauth_token']  = $accessToken['oauth_token'];
                    $_SESSION['tumblr_oauth_secret'] = $accessToken['oauth_token_secret'];
                }
                // Assign Tumblr::_oauth
                $this->_oauth = $oauth;
            } catch (OAuthException $e) {
                // Throw OAuth Exception
                throw $e;
            }
        }
        
        return $this->_oauth;
    }
    
    /**
     * Make a request to the Tumblr API/v2 URLs.
     *
     * @param string  $uri        (required)
     * @param boolean $oauth      (required, default=true)
     * @param array   $postParams (required, default=true)
     * @return array  $response
     * @throw TumblrException
     */
    public function request($uri, $oauth = true, Array $postParams = null) {
        $response = null;
        try {
            if ($oauth) {
                // Needs session(tumblr_oauth_token & tumblr_oauth_secret)
                if (!isset($_SESSION['tumblr_oauth_token'], $_SESSION['tumblr_oauth_secret'])) {
                    throw new TumblrException(
                        'Both session:tumblr_oauth_token and session:tumblr_oauth_secret are required. '.
                        'Try Tumblr::createOauth before requests if OAuth is required for these requests.');
                }
                $this->_oauth->setToken($_SESSION['tumblr_oauth_token'], $_SESSION['tumblr_oauth_secret']);
            }
            
            if (empty($postParams)) {
                // Make a GET request
                $this->_oauth->fetch($uri);
            } else {
                // Make a POST request
                $this->_oauth->fetch($uri, $postParams, OAUTH_HTTP_METHOD_POST);
            }
            // Store API response 
            $response = $this->_oauth->getLastResponse();
        } catch (OAuthException $e) {
            // Get rid of OAuth Error: "Invalid auth/bad request (got a 404, expected HTTP/1.1 20X or a redirect)"
            // Good idea? I found it! :P
            $response = $e->lastResponse;
        }
        
        // We use always array-type response
        $response = json_decode($response, true);
        if ($response === null) {
            throw new TumblrException('No response has been returned.');
        }
        
        if ( // If throwing response error
            $this->_throwResponseErrors === true
                // And any un-successful response code returned
                && !in_array($response['meta']['status'], $this->_successStatuses)) {
            $error = sprintf('Error: status: %d, msg: %s', 
                $response['meta']['status'], $response['meta']['msg']);
            // Some requests returns extra info
            if (isset($response['response']['errors'])) {
                $error .= ', errors: '. $response['response']['errors'];
            }
            
            throw new TumblrException($error);
        }
        
        return $response;
    }
    
    /**
     * Access to Tumblr::_consKey.
     *
     * @return string Tumblr::_consKey
     */
    public function getApiKey() {
        return $this->_consKey;
    }
    
    /**
     * Access to Tumblr::_apiUrl.
     *
     * @return string Tumblr::_apiUrl
     */
    public function getApiUrl() {
        return $this->_apiUrl;
    }
    
    /**
     * Set whether API response errors will be thrown or not.
     *
     * @param boolean $option (required)
     */
    public function throwResponseErrors($option) {
        $this->_throwResponseErrors = (boolean) $option;
    }
}
