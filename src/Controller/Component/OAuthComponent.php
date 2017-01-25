<?php
namespace OAuthServer\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\App;
use Cake\Network\Exception\NotImplementedException;
use OAuthServer\Model\Repositories;
use OAuthServer\Traits\GetRepositoryTrait;

class OAuthComponent extends Component
{
    use GetRepositoryTrait;

    /**
     * @var \League\OAuth2\Server\AuthorizationServer
     */
    public $Server;

    /**
     * Grant types currently supported by the plugin
     *
     * @var array
     */
    protected $_allowedGrants = ['ClientCredentials','Password','AuthCode','Implicit','RefreshToken'];

    /**
     * @var array
     */
    protected $_defaultConfig = [
        'tokenTTL' => 'PT1H', //TTL 30 * 24 * 60 * 60 in seconds // access tokens will expire after 1 hour
        'supportedGrants' => ['ClientCredentials','Password','AuthCode','Implicit', 'RefreshToken'],
        'repositories' => [
            'accessToken' => [
                'className' => 'OAuthServer.AccessToken'
            ],
            'client' => [
                'className' => 'OAuthServer.Client'
            ],
            'refreshToken' => [
                'className' => 'OAuthServer.RefreshToken'
            ],
            'scope' => [
                'className' => 'OAuthServer.Scope'
            ],
            'authCode' => [
                'className' => 'OAuthServer.AuthCode'
            ],
            'user' => [
                'className' => 'OAuthServer.User'
            ]
        ],
        'authorizationServer' => [
            'className' => 'League\OAuth2\Server\AuthorizationServer',
            'publicKey' =>  ROOT.DS.'oauthKeys/public.key',
            'privateKey' =>  ROOT.DS.'oauthKeys/private.key' 
        ]
    ];

    /**
     * @return \League\OAuth2\Server\AuthorizationServer
     */
    protected function _getAuthorizationServer()
    {
        $serverConfig = $this->config('authorizationServer');
        $serverClassName = App::className($serverConfig['className']);

        // Init our repositories
        $clientRepository = $this->_getRepository('client'); // instance of ClientRepositoryInterface
        $scopeRepository = $this->_getRepository('scope'); // instance of ScopeRepositoryInterface
        $accessTokenRepository = $this->_getRepository('accessToken'); // instance of AccessTokenRepositoryInterface

        // Path to public and private keys
        $privateKey = $serverConfig['privateKey'];
        //$privateKey = new CryptKey('file://path/to/private.key', 'passphrase'); // if private key has a pass phrase
        $publicKey = $serverConfig['publicKey'];

        return new $serverClassName(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $privateKey,
            $publicKey
        );
    }

    /**
     * @param array $config Config array
     * @return void
     */
    public function initialize(array $config)
    {
        $server = $this->_getAuthorizationServer();
//        $server->setSessionStorage($this->_getStorage('session'));
//        $server->setAccessTokenStorage($this->_getStorage('accessToken'));
//        $server->setClientStorage($this->_getStorage('client'));
//        $server->setScopeStorage($this->_getStorage('scope'));
//        $server->setAuthCodeStorage($this->_getStorage('authCode'));
//        $server->setRefreshTokenStorage($this->_getStorage('refreshToken'));

        $supportedGrants = isset($config['supportedGrants']) ? $config['supportedGrants'] : $this->config('supportedGrants');
        foreach ($supportedGrants as $grant) {
            if (!in_array($grant, $this->_allowedGrants)) {
                throw new NotImplementedException(__('The {0} grant type is not supported by the OAuth server',$grant));
            }

            $className = '\\League\\OAuth2\\Server\\Grant\\' . $grant . 'Grant';
            switch($grant) {
                case 'Password':
                    $userRepository = $this->_getRepository('user');
                    $refreshTokenRepository = $this->_getRepository('refreshToken'); 
                    $objGrant = new $className(
                        $userRepository,
                         $refreshTokenRepository
                    );
                    $objGrant->setRefreshTokenTTL(new \DateInterval($this->config('tokenTTL'))); // refresh tokens will expire after 1 month
                    break;
                case 'AuthCode':
                    $authCodeRepository = $this->_getRepository('authCode');
                    $refreshTokenRepository = $this->_getRepository('refreshToken');
                    $objGrant = new $className(
                        $authCodeRepository,
                        $refreshTokenRepository,
                        new \DateInterval($this->config('tokenTTL'))
                    );
                    break;
                case 'Implicit':
                    $objGrant = new $className( new \DateInterval($this->config('tokenTTL'))); 
                    break;
                case 'RefreshToken':
                    $refreshTokenRepository = $this->_getRepository('refreshToken');
                    $objGrant = new $className($refreshTokenRepository);
                    $objGrant->setRefreshTokenTTL(new \DateInterval($this->config('tokenTTL')));
                    break;
/*
                    $objGrant = new $className();
                    if ($grant === 'Password') {
                        $objGrant->setVerifyCredentialsCallback(function ($username, $password) {
                            $controller = $this->_registry->getController();
                            $controller->Auth->constructAuthenticate();
                            $userfield = $controller->components['Auth']['authenticate']['Form']['fields']['username'];
                            $controller->request->data[$userfield] = $username;
                            $controller->request->data['password'] = $password;
                            $loginOk = $controller->Auth->identify();
                            if ($loginOk) {
                                return $loginOk['id'];
                            } else {
                                return false;
                            }
                        });                
                    }
*/
                default:
                    $objGrant = new $className();
            
            }
            $server->enableGrantType(
                $objGrant,
                new \DateInterval($this->config('tokenTTL'))
            );
        }

//        $server->setAccessTokenTTL($this->config('tokenTTL'));

        $this->Server = $server;
    }

    /**
     * @param string $authGrant Grant type
     * @return bool|\Cake\Network\Response|void
     */
    public function checkAuthParams($authGrant)
    {
        $controller = $this->_registry->getController();
        try {
            return $this->Server->getGrantType($authGrant)->checkAuthorizeParams();
        } catch (\OAuthException $e) {
            if ($e->shouldRedirect()) {
                return $controller->redirect($e->getRedirectUri());
            }

            $controller->RequestHandler->renderAs($this, 'json');
            $controller->response->statusCode($e->httpStatusCode);
            $controller->response->header($e->getHttpHeaders());
            $controller->set('response', $e);
            
            return false;
        }
    }
}
