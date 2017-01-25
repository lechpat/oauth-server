<?php

namespace OAuthServer\Model\Repository;

use Cake\Log\Log;
use Cake\ORM\Entity;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository extends AbstractRepository implements AuthCodeRepositoryInterface
{
    /**
     * Creates a new AuthCode
     *
     * @return AuthCodeEntityInterface
     */
    public function getNewAuthCode()
    {

    }

    /**
     * Persists a new auth code to permanent storage.
     *
     * @param AuthCodeEntityInterface $authCodeEntity
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {

    }

    /**
     * Revoke an auth code.
     *
     * @param string $codeId
     */
    public function revokeAuthCode($codeId)
    {

    }

    /**
     * Check if the auth code has been revoked.
     *
     * @param string $codeId
     *
     * @return bool Return true if this code has been revoked
     */
    public function isAuthCodeRevoked($codeId)
    {

    }
    
    /**
     * {@inheritdoc}
     *
     * @param string $code Code
     * @return \League\OAuth2\Server\Entity\AuthCodeEntity|void
     */
    public function get($code)
    {
        $this->loadModel('OAuthServer.AuthCodes');
        $result = $this->AuthCodes->find()
            ->where([
                'code' => $code,
                'expires >=' => time()
            ])
            ->first();

        if ($result) {
            $token = new AuthCodeEntity($this->server);
            $token->setId($result->code);
            $token->setRedirectUri($result->redirect_uri);
            $token->setExpireTime($result->expires);

            return $token;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param string $token Token
     * @param int $expireTime Expire time
     * @param int $sessionId Session
     * @param string $redirectUri Redirect
     * @return void
     */
    public function create($token, $expireTime, $sessionId, $redirectUri)
    {
        $this->loadModel('OAuthServer.AuthCodes');
        $code = $this->AuthCodes->newEntity([
            'code' => $token,
            'redirect_uri' => $redirectUri,
            'session_id' => $sessionId,
            'expires' => $expireTime,
        ]);
        $this->AuthCodes->save($code);
    }

    /**
     * {@inheritdoc}
     *
     * @param \League\OAuth2\Server\Entity\AuthCodeEntity $token Auth code
     * @return array
     */
    public function getScopes(AuthCodeEntity $token)
    {
        $this->loadModel('OAuthServer.AuthCodeScopes');
        $result = $this->AuthCodeScopes->find()
            ->contain([
                'Scopes'
            ])
            ->where([
                'auth_code' => $token->getId()
            ])
            ->map(function (Entity $scope) {
                return (new ScopeEntity($this->server))->hydrate([
                        'id' => $scope->scope->id,
                        'description' => $scope->scope->description,
                    ]);
            });

        return $result->toArray();
    }

    /**
     * {@inheritdoc}
     *
     * @param \League\OAuth2\Server\Entity\AuthCodeEntity $token Auth code
     * @param \League\OAuth2\Server\Entity\ScopeEntity $scope Scopes
     * @return void
     */
    public function associateScope(AuthCodeEntity $token, ScopeEntity $scope)
    {
        $this->loadModel('OAuthServer.AuthCodeScopes');
        $codeScope = $this->AuthCodeScopes->newEntity([
            'auth_code' => $token->getId(),
            'scope_id' => $scope->getId(),
        ]);
        $this->AuthCodeScopes->save($codeScope);
    }

    /**
     * {@inheritdoc}
     *
     * @param \League\OAuth2\Server\Entity\AuthCodeEntity $token Auth code
     * @return void
     */
    public function delete(AuthCodeEntity $token)
    {
        $this->loadModel('OAuthServer.AuthCodes');
        $this->AuthCodes->deleteAll([
                'code' => $token->getId()
            ]);
    }
}
