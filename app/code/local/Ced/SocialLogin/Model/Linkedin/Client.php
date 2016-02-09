<?php

/**

 * CedCommerce

 *

 * NOTICE OF LICENSE

 *

 * This source file is subject to the Open Software License (OSL 3.0)

 * that is bundled with this package in the file LICENSE.txt.

 * It is also available through the world-wide-web at this URL:

  * http://opensource.org/licenses/osl-3.0.php

 *

 * @category    Ced

 * @package     Ced_SocialLogin

 * @author 		CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>

 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)

 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)

 */



/**

 * SocialLogin 	Linkedin/Client Model

 *

 * @category   	Ced

 * @package    	Ced_SocialLogin

 * @author		CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>

 */



class Ced_SocialLogin_Model_Linkedin_Client

{

    const REDIRECT_URI_ROUTE = 'cedsociallogin/linkedin/connect';



    const XML_PATH_ENABLED = 'ced/ced_sociallogin_linkedin/enabled';

    const XML_PATH_CLIENT_ID = 'ced/ced_sociallogin_linkedin/client_id';

    const XML_PATH_CLIENT_SECRET = 'ced/ced_sociallogin_linkedin/client_secret';



    const OAUTH2_REVOKE_URI = 'https://accounts.google.com/o/oauth2/invalidateToken';

    const OAUTH2_TOKEN_URI = 'https://www.linkedin.com/uas/oauth2/accessToken';

    const OAUTH2_AUTH_URI = 'https://www.linkedin.com/uas/oauth2/authorization';

    const OAUTH2_SERVICE_URI = 'https://api.linkedin.com/v1';



    protected $isEnabled = null;

    protected $clientId = null;

    protected $clientSecret = null;

    protected $redirectUri = null;

    protected $state = '';

    protected $scope = array('r_basicprofile', 'r_emailaddress'/*, 'r_fullprofile'*/);
    /*protected $scope = array(
                        'id',
                        'first-name',
                        'last-name',
                        'headline',
                        'picture-url',
                        'email-address',
                        'phone-numbers',
                        'location'
                    );*/

    protected $token = null;



    public function __construct($params = array())

    {

        if(($this->isEnabled = $this->_isEnabled())) {

            $this->clientId = $this->_getClientId();

            $this->clientSecret = $this->_getClientSecret();

            $this->redirectUri = Mage::getModel('core/url')->sessionUrlVar(

                Mage::getUrl(self::REDIRECT_URI_ROUTE)

            );



            if(!empty($params['scope'])) {

                $this->scope = $params['scope'];

            }



            if(!empty($params['state'])) {

                $this->state = $params['state'];

            }

        }

    }



    public function isEnabled()

    {

        return (bool) $this->isEnabled;

    }



    public function getClientId()

    {

        return $this->clientId;

    }



    public function getClientSecret()

    {

        return $this->clientSecret;

    }



    public function getRedirectUri()

    {

        return $this->redirectUri;

    }



    public function getScope()

    {

        return $this->scope;

    }



    public function getState()

    {

        return $this->state;

    }



    public function setState($state)

    {

        $this->state = $state;

    }



    public function getAccess()

    {

        return $this->access;

    }





    public function setAccessToken($token)

    {

        $this->token = json_decode($token);

    }



    public function getAccessToken()

    {

        if(empty($this->token)) {

            $this->fetchAccessToken();

        } else if($this->isAccessTokenExpired()) {

            $this->refreshAccessToken();

        }



        return json_encode($this->token);

    }



    public function createAuthUrl()

    {


 $url =
        self::OAUTH2_AUTH_URI.'?'.

            http_build_query(

                array(

                    'response_type' => 'code',

                    'redirect_uri' => $this->redirectUri,

                    'client_id' => $this->clientId,

                    'scope' => implode(" ", $this->scope),

                    'state' => $this->state,

                    )

            );
        return $url;

    }



    public function api($endpoint, $method = 'GET', $params = array())

    {

        if(empty($this->token)) {
            $this->fetchAccessToken();

        } else if($this->isAccessTokenExpired()) {

            $this->refreshAccessToken();

        }



        $url = self::OAUTH2_SERVICE_URI.$endpoint;


        $method = strtoupper($method);



        $params = array_merge(array(

            'oauth2_access_token' => $this->token->access_token

        ), $params);



        $response = $this->_httpRequest($url, $method, $params);



        return $response;

    }



    public function revokeToken()

    {

        if(empty($this->token)) {

            throw new Exception(

                Mage::helper('sociallogin')

                    ->__('No access token available.')

            );

        }



        if(empty($this->token->refresh_token)) {

            throw new Exception(

                Mage::helper('sociallogin')

                    ->__('No refresh token, nothing to revoke.')

            );

        }



        $this->_httpRequest(

            self::OAUTH2_REVOKE_URI,

            'POST',

           array(

               'token' => $this->token->refresh_token

           )

        );

    }



    protected function fetchAccessToken()

    {

        if(empty($_REQUEST['code'])) {

            throw new Exception(

                Mage::helper('sociallogin')

                    ->__('Unable to retrieve access code.')

            );
        }


        $response = $this->_httpRequest(

            self::OAUTH2_TOKEN_URI,

            'GET',

            array(

                'code' => $_REQUEST['code'],

                'redirect_uri' => $this->redirectUri,

                'client_id' => $this->clientId,

                'client_secret' => $this->clientSecret,

                'grant_type' => 'authorization_code'

            )

        ); 



        $response->created = time();



        $this->token = $response;

    }



    protected function refreshAccessToken()

    {

        if(empty($this->token->refresh_token)) {

            throw new Exception(

                Mage::helper('sociallogin')

                    ->__('No refresh token, unable to refresh access token.')

            );

        }



        $response = $this->_httpRequest(

            self::OAUTH2_TOKEN_URI,

            'POST',

            array(

                'client_id' => $this->clientId,

                'client_secret' => $this->clientSecret,

                'refresh_token' => $this->token->refresh_token,

                'grant_type' => 'refresh_token'

            )

        );



        $this->token->access_token = $response->access_token;

        $this->token->expires_in = $response->expires_in;

        $this->token->created = time();

    }



    protected function isAccessTokenExpired() {

        // If the token is set to expire in the next 30 seconds.

        $expired = ($this->token->created + ($this->token->expires_in - 30)) < time();



        return $expired;

    }



    protected function _httpRequest($url, $method = 'GET', $params = array())

    {

        $client = new Zend_Http_Client($url, array('timeout' => 60));


        switch ($method) {

            case 'GET':

                $client->setParameterGet($params);

                break;

            case 'POST':

                $client->setParameterPost($params);

                break;

            case 'DELETE':

                break;

            default:

                throw new Exception(

                    Mage::helper('sociallogin')

                        ->__('Required HTTP method is not supported.')

                );

        }

;
        $response = $client->request($method);



        Mage::log($response->getStatus().' - '. $response->getBody());
        $decoded_response = json_decode($response->getBody());


        if($response->isError()) {

            $status = $response->getStatus();
            if(($status == 400 || $status == 401)) {

                if(isset($decoded_response->error->message)) {

                    $message = $decoded_response->error->message;

                } else {

                    $message = Mage::helper('sociallogin')

                        ->__('Unspecified OAuth error occurred.');

                }



                throw new Ced_SocialLogin_LinkedinOAuthException($message);

            } else {

                $message = sprintf(

                    Mage::helper('sociallogin')

                        ->__('HTTP error %d occurred while issuing request.'),

                    $status

                );



                throw new Exception($message);

            }

        }



        return $decoded_response;

    }



    protected function _isEnabled()

    {

        return $this->_getStoreConfig(self::XML_PATH_ENABLED);

    }



    protected function _getClientId()

    {

        return $this->_getStoreConfig(self::XML_PATH_CLIENT_ID);

    }



    protected function _getClientSecret()

    {

        return $this->_getStoreConfig(self::XML_PATH_CLIENT_SECRET);

    }



    protected function _getStoreConfig($xmlPath)

    {

        return Mage::getStoreConfig($xmlPath, Mage::app()->getStore()->getId());

    }



}



class Ced_SocialLogin_LinkedinOAuthException extends Exception

{}

