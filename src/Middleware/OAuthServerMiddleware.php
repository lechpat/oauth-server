<?php
namespace OAuthServer\Middleware;

use Cake\Core\App;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Middleware\ResourceServerMiddleware;
use OAuthServer\Model\Repository;
use OAuthServer\Traits\GetRepositoryTrait;
use Cake\Core\InstanceConfigTrait;

class OAuthServerMiddleware
{
    use InstanceConfigTrait;
    use GetRepositoryTrait;
    
    /**
     * @var \League\OAuth2\Server\AuthorizationServer
     */
    public $authServer;
    /**
     * @var \League\OAuth2\Server\ResourceServer
     */
    public $resourceServer;

    /**
     * Grant types currently supported by the plugin
     *
     * @var array
     */
    protected $_allowedGrants = ['ClientCredentials','Password','AuthCode','Implicit','RefreshToken','FacebookAuthCode'];

    /**
     * Default config
     *
     * These are merged with user-provided config when the object is used.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'OAuthPath' => '/oauth',
        'tokenTTL' => 'PT1H', //TTL 30 * 24 * 60 * 60 in seconds // access tokens will expire after 1 hour
        'continue' => false,
        'supportedGrants' => ['ClientCredentials','Password','AuthCode','Implicit', 'RefreshToken','FacebookAuthCode'],
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
            ],
            'social' => [
                'className' => 'OAuthServer.SocialAccount'
            ]
        ],
        'authorizationServer' => [
            'className' => 'League\OAuth2\Server\AuthorizationServer',
            'publicKey' =>  ROOT.DS.'oauthKeys/public.key',
            'privateKey' =>  ROOT.DS.'oauthKeys/private.key'
        ],
        'resourceServer' => [
            'className' => 'League\OAuth2\Server\ResourceServer',
            'publicKey' => ROOT.DS.'oauthKeys/public.key'
        ]
    ];

    /**
     * Constructor
     *
     * @param array $config Array of config.
     */
    public function __construct(array $config = [])
    {
        $this->config($config);
    }

    public function __invoke($request, $response, $next)
    {/*
        if(strpos($request->getUri()->getPath(), '/api/applicants') !== 0) {
            //return $next($request, $response);
        } else {
            $this->initialize_resource($this->config());     
            try {
                $request = $this->resourceServer->validateAuthenticatedRequest($request);
                \Cake\Log\Log::write('error',$request);
            } catch (OAuthServerException $exception) {
                return $exception->generateHttpResponse($response);
                // @codeCoverageIgnoreStart
            } catch (\Exception $exception) {
                return (new OAuthServerException($exception->getMessage(), 0, 'unknown_error', 500))
                    ->generateHttpResponse($response);
                // @codeCoverageIgnoreEnd
            }

            // Pass the request and response on to the next responder in the chain
            return $next($request, $response);        
        }
       */ 
        if(strpos($request->getUri()->getPath(), $this->config('OAuthPath') . '/token_revoke') === 0) {
            return $response->withStatus(200);
        } 

        if(strpos($request->getUri()->getPath(), $this->config('OAuthPath') . '/account/access_token') === 0) {
            //$this->authServer = $this->_getAuthorizationServer();
            $this->_config['repositories']['user']['className'] = 'Account';
        }
        elseif(strpos($request->getUri()->getPath(), $this->config('OAuthPath') . '/applicant/access_token') === 0) {
            //$this->authServer = $this->_getAuthorizationServer();
            $this->_config['repositories']['user']['className'] = 'Applicant';
        }
        elseif(strpos($request->getUri()->getPath(), $this->config('OAuthPath') . '/access_token') !== 0) {
            return $next($request, $response);
        }
        $this->initialize_auth($this->config());

        try {
            $response = $this->authServer->respondToAccessTokenRequest($request, $response);
            \Cake\Log\Log::write('error',$response);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
            // @codeCoverageIgnoreStart
        } catch (\Exception $exception) {
            return (new OAuthServerException($exception->getMessage(), 0, 'unknown_error', 500))
                ->generateHttpResponse($response);
            // @codeCoverageIgnoreEnd
        }

        // Pass the request and response on to the next responder in the chain
        return $next($request, $response);
    }

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
     * @return \League\OAuth2\Server\ResourceServer
     */
    protected function _getResourceServer()
    {   
        $serverConfig = $this->config('resourceServer');
        $serverClassName = App::className($serverConfig['className']);
        
        // Init our repositories
        $accessTokenRepository = $this->_getRepository('accessToken'); // instance of AccessTokenRepositoryInterface
        
        //$privateKey = new CryptKey('file://path/to/private.key', 'passphrase'); // if private key has a pass phrase
        $publicKey = $serverConfig['publicKey'];
        
        return new $serverClassName(
            $accessTokenRepository,
            $publicKey
        );
    }

    public function initialize_resource(array $config)
    {
        $server = $this->_getResourceServer();
        $this->resourceServer = $server; 
    }

    public function initialize_auth(array $config)
    {
        $server = $this->_getAuthorizationServer();
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
                case 'FacebookAuthCode':
                    $className = '\\OAuthServer\\Grant\\SocialAccountGrant';
                    $socialAccountRepository = $this->_getRepository('social');
                    $refreshTokenRepository = $this->_getRepository('refreshToken');
                    $objGrant = new $className(
                        $socialAccountRepository,
                        $refreshTokenRepository
                    );
                    $objGrant->setRefreshTokenTTL(new \DateInterval($this->config('tokenTTL'))); // refresh tokens will expire after 1 month
                    break;

                default:
                    $objGrant = new $className();

            }
            $server->enableGrantType(
                $objGrant,
                new \DateInterval($this->config('tokenTTL'))
            );
        }

        $this->authServer = $server;
    }
}
