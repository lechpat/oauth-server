<?php
namespace OAuthServer\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventDispatcherTrait;
use Cake\Event\EventManager;
use Cake\Log\LogTrait;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

use Cake\Routing\Router;
use CakeDC\Users\Auth\Social\Util\SocialUtils;
use CakeDC\Users\Model\Table\SocialAccountsTable;

/**
 * OAuthClient component
 */
class OAuthClientComponent extends Component
{
    use EventDispatcherTrait;
    use LogTrait;

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'userModel' => 'Origination.Applicants',
//        'path' => ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialLogin', 'prefix' => null],
        'providers' => [
            'facebook' => [
                'className' => 'League\OAuth2\Client\Provider\Facebook',
                'options' => [
                    'graphApiVersion' => 'v2.5',
//                    'redirectUri' => Router::fullBaseUrl() . '/auth/facebook',
                ]
            ],
            'twitter' => [
                'options' => [
//                    'redirectUri' => Router::fullBaseUrl() . '/auth/twitter',
                ]
            ],
            'linkedIn' => [
                'className' => 'League\OAuth2\Client\Provider\LinkedIn',
                'options' => [
//                    'redirectUri' => Router::fullBaseUrl() . '/auth/linkedIn',
                ]
            ],
            'instagram' => [
                'className' => 'League\OAuth2\Client\Provider\Instagram',
                'options' => [
//                    'redirectUri' => Router::fullBaseUrl() . '/auth/instagram',
                ]
            ],
            'google' => [
                'className' => 'League\OAuth2\Client\Provider\Google',
                'options' => [
                    'userFields' => ['url', 'aboutMe'],
//                    'redirectUri' => Router::fullBaseUrl() . '/auth/google',
                ]
            ],
        ],
    ];

    /**
     * Instance of OAuth2 provider.
     *
     * @var \League\OAuth2\Client\Provider\AbstractProvider
     */
    protected $_provider;

    /**
     * Constructor
     *
     * @param \Cake\Controller\ComponentRegistry $registry The Component registry used on this request.
     * @param array $config Array of config to use.
     * @throws \Exception
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        $oauthConfig = Configure::read('OAuth');
        //We unset twitter from providers to exclude from OAuth2 config
        unset($oauthConfig['providers']['twitter']);
        $providers = [];
        foreach ($oauthConfig['providers'] as $provider => $options) {
            if (!empty($options['options']['redirectUri']) &&
                !empty($options['options']['clientId']) &&
                !empty($options['options']['clientSecret'])) {
                $providers[$provider] = $options;
            }
        }
        $oauthConfig['providers'] = $providers;
        Configure::write('OAuth2', $oauthConfig);
        $config = $this->normalizeConfig(array_merge($config, $oauthConfig));
        parent::__construct($registry, $config);
    }

    /**
     * Normalizes providers' configuration.
     *
     * @param array $config Array of config to normalize.
     * @return array
     * @throws \Exception
     */
    public function normalizeConfig(array $config)
    {
        $config = Hash::merge((array)Configure::read('OAuth2'), $config);

        if (empty($config['providers'])) {
            throw new MissingProviderConfigurationException();
        }

        array_walk($config['providers'], [$this, '_normalizeConfig'], $config);

        return $config;
    }

    /**
     * Callback to loop through config values.
     *
     * @param array $config Configuration.
     * @param string $alias Provider's alias (key) in configuration.
     * @param array $parent Parent configuration.
     * @return void
     */
    protected function _normalizeConfig(&$config, $alias, $parent)
    {
        unset($parent['providers']);

        $defaults = [
                'className' => null,
                'options' => [],
                'collaborators' => [],
                'mapFields' => [],
            ] + $parent + $this->_defaultConfig;

        $config = array_intersect_key($config, $defaults);
        $config += $defaults;

        array_walk($config, [$this, '_validateConfig']);

        foreach (['options', 'collaborators'] as $key) {
            if (empty($parent[$key]) || empty($config[$key])) {
                continue;
            }
        
            $config[$key] = array_merge($parent[$key], $config[$key]);
        }   
    }           
                
    /**         
     * Validates the configuration.
     *  
     * @param mixed $value Value. 
     * @param string $key Key.
     * @return void
     * @throws CakeDC\Users\Auth\Exception\InvalidProviderException
     * @throws CakeDC\Users\Auth\Exception\InvalidSettingsException
     */
    protected function _validateConfig(&$value, $key)
    {
        if ($key === 'className' && !class_exists($value)) {
            throw new InvalidProviderException([$value]);
        } elseif (!is_array($value) && in_array($key, ['options', 'collaborators'])) {
            throw new InvalidSettingsException([$key]);
        }
    }
    
    /** 
     * Get the controller associated with the collection.
     *  
     * @return \Cake\Controller\Controller Controller instance
     */ 
    protected function _getController()
    {   
        return $this->_registry->getController();
    }

    /**
     * Get a user based on information in the request.
     *
     * @param \Cake\Network\Request $request Request object.
     * @param \Cake\Network\Response $response Response object.
     * @return bool
     * @throws \RuntimeException If the `CakeDC/Users/OAuth2.newUser` event is missing or returns empty.
     */
    public function authenticate(Request $request, Response $response)
    {
        $user =  $this->getUser($request);
        $this->log($user);
    }   
    
    /**
     * Authenticates with OAuth2 provider by getting an access token and
     * retrieving the authorized user's profile data.
     *
     * @param \Cake\Network\Request $request Request object.
     * @return array|bool
     */
    protected function _authenticate(Request $request)
    {
//        if (!$this->_validate($request)) {
//            return false;
//        }

        $code = $request->data('code');
        $grantType = $this->request->data('grant_type');
        $redirectUri = $this->request->data('redirectUri');
        $state = $this->request->data('state');
        if(!empty($redirectUri)) {
            $this->config('providers.facebook.options.redirectUri', $redirectUri);
            $this->config('providers.facebook.options.state', $state);
        }

        $provider = $this->provider($request);
        try {
            $token = $provider->getAccessToken('authorization_code', compact('code'));
            return compact('token') + $provider->getResourceOwner($token)->toArray();
        } catch (\Exception $e) {
            $message = sprintf(
                "Error getting an access token / retrieving the authorized user's profile data. Error message: %s %s",
                $e->getMessage(),
                $e
            );
            $this->log($message);

            return false;
        }
    }

    /**
     * Validates OAuth2 request.
     *
     * @param \Cake\Network\Request $request Request object.
     * @return bool
     */
    protected function _validate(Request $request)
    {
        if (!array_key_exists('code', $request->query) || !$this->provider($request)) {
            return false;
        }

        $session = $request->session();
        $sessionKey = 'oauth2state';
        $state = $request->query('state');

        if ($this->config('options.state') &&
            (!$state || $state !== $session->read($sessionKey))) {
            $session->delete($sessionKey);

            return false;
        }

        return true;
    }

    /**
     * Maps raw provider's user profile data to local user's data schema.
     *
     * @param array $data Raw user data.
     * @return array
     */
    protected function _map($data)
    {
        if (!$map = $this->config('mapFields')) {
            return $data;
        }

        foreach ($map as $dst => $src) {
            $data[$dst] = $data[$src];
            unset($data[$src]);
        }

        return $data;
    }

    /**
     * Handles unauthenticated access attempts. Will automatically forward to the
     * requested provider's authorization URL to let the user grant access to the
     * application.
     *
     * @param \Cake\Network\Request $request Request object.
     * @param \Cake\Network\Response $response Response object.
     * @return \Cake\Network\Response|null
     */
    public function unauthenticated(Request $request, Response $response)
    {
        $provider = $this->provider($request);
        if (empty($provider) || !empty($request->query['code'])) {
            return null;
        }

        if ($this->config('options.state')) {
            $request->session()->write('oauth2state', $provider->getState());
        }

        $response->location($provider->getAuthorizationUrl());

        return $response;
    }

    /**
     * Returns the `$request`-ed provider.
     *
     * @param \Cake\Network\Request $request Current HTTP request.
     * @return \League\Oauth2\Client\Provider\GenericProvider|false
     */
    public function provider(Request $request)
    {
        if (!$alias = $request->param('provider')) {
            return false;
        }

        if (empty($this->_provider)) {
            $this->_provider = $this->_getProvider($alias);
        }

        return $this->_provider;
    }

    /**
     * Instantiates provider object.
     *
     * @param string $alias of the provider.
     * @return \League\Oauth2\Client\Provider\GenericProvider
     */
    protected function _getProvider($alias)
    {
        if (!$config = $this->config('providers.' . $alias)) {
            return false;
        }

        $this->config($config);

        if (is_object($config) && $config instanceof AbstractProvider) {
            return $config;
        }

        $class = $config['className'];

        return new $class($config['options'], $config['collaborators']);
    }

    /**
     * Find or create local user
     *
     * @param array $data data
     * @return array|bool|mixed
     * @throws MissingEmailException
     */
    protected function _touch(array $data)
    {
        try {
            if (empty($data['provider']) && !empty($this->_provider)) {
                $data['provider'] = SocialUtils::getProvider($this->_provider);
            }
            $user = $this->_socialLogin($data);
        } catch (UserNotActiveException $ex) {
            $exception = $ex;
        } catch (AccountNotActiveException $ex) {
            $exception = $ex;
        } catch (MissingEmailException $ex) {
            $exception = $ex;
        }
        if (!empty($exception)) {
            $args = ['exception' => $exception, 'rawData' => $data];
//            $event = $this->_getController()->dispatchEvent(UsersAuthComponent::EVENT_FAILED_SOCIAL_LOGIN, $args);
            if (method_exists($this->_getController(), 'failedSocialLogin')) {
                $this->_getController()->failedSocialLogin($exception, $data, true);
            }

            return $event->result;
        }

        // If new SocialAccount was created $user is returned containing it
        if ($user->get('social_accounts')) {
//            $this->_getController()->dispatchEvent(UsersAuthComponent::EVENT_AFTER_REGISTER, compact('user'));
        }

        if (!empty($user->username)) {
//            $user = $this->_findUser($user->username);
        }

        return $user;
    }

    /**
     * Get a user based on information in the request.
     *
     * @param \Cake\Network\Request $request Request object.
     * @return mixed Either false or an array of user information
     * @throws \RuntimeException If the `CakeDC/Users/OAuth2.newUser` event is missing or returns empty.
     */
    public function getUser(Request $request)
    {
        $data = $request->session()->read(Configure::read('Users.Key.Session.social'));
        $requestDataEmail = $request->data('email');
        if (!empty($data) && empty($data['uid']) && (!empty($data['email']) || !empty($requestDataEmail))) {
            if (!empty($requestDataEmail)) {
                $data['email'] = $requestDataEmail;
            }
            $user = $data;
            $request->session()->delete(Configure::read('Users.Key.Session.social'));
        } else {
            if (empty($data) && !$rawData = $this->_authenticate($request)) {
                return false;
            }
            if (empty($rawData)) {
                $rawData = $data;
            }

            $provider = $this->_getProviderName($request);
            try {
                $user = $this->_mapUser($provider, $rawData);
            } catch (MissingProviderException $ex) {
                $request->session()->delete(Configure::read('Users.Key.Session.social'));
                throw $ex;
            }
            if ($user['provider'] === SocialAccountsTable::PROVIDER_TWITTER) {
                $request->session()->write(Configure::read('Users.Key.Session.social'), $user);
            }
        }

        if (!$user || !$this->config('userModel')) {
            return false;
        }

        if (!$result = $this->_touch($user)) {
            return false;
        }

        if ($request->session()->check(Configure::read('Users.Key.Session.social'))) {
            $request->session()->delete(Configure::read('Users.Key.Session.social'));
        }

        return $result;
    }

    /**
     * Get the provider name based on the request or on the provider set.
     *
     * @param \Cake\Network\Request $request Request object.
     * @return mixed Either false or an array of user information
     */
    protected function _getProviderName($request = null)
    {
        $provider = false;
        if (!is_null($this->_provider)) {
            $provider = SocialUtils::getProvider($this->_provider);
        } elseif (!empty($request)) {
            $provider = ucfirst($request->param('provider'));
        }

        return $provider;
    }

    /**
     * Get the provider name based on the request or on the provider set.
     *
     * @param string $provider Provider name.
     * @param array $data User data
     * @throws MissingProviderException
     * @return mixed Either false or an array of user information
     */
    protected function _mapUser($provider, $data)
    {
        if (empty($provider)) {
            throw new MissingProviderException(__d('CakeDC/Users', "Provider cannot be empty"));
        }
        $providerMapperClass = "\\CakeDC\\Users\\Auth\\Social\\Mapper\\$provider";
        $providerMapper = new $providerMapperClass($data);
        $user = $providerMapper();
        $user['provider'] = $provider;

        return $user;
    }

    /**
     * @param mixed $data data
     * @return mixed
     */
    protected function _socialLogin($data)
    {
        $options = [
            'use_email' => Configure::read('Users.Email.required'),
            'validate_email' => Configure::read('Users.Email.validate'),
            'token_expiration' => Configure::read('Users.Token.expiration')
        ];

        $userModel = Configure::read('Users.table');
        $User = TableRegistry::get($userModel);
        $user = $User->socialLogin($data, $options);

        return $user;
    }
}
