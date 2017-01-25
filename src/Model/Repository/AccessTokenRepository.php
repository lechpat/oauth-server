<?php

namespace OAuthServer\Model\Repository;

use Cake\ORM\Entity;

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;

use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;


class AccessTokenRepository extends AbstractRepository implements AccessTokenRepositoryInterface
{
    /**
     * Create a new access token
     *
     * @param ClientEntityInterface  $clientEntity
     * @param ScopeEntityInterface[] $scopes
     * @param mixed                  $userIdentifier
     *
     * @return AccessTokenEntityInterface
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        $this->loadModel('OAuthServer.AccessTokens');
        $token = $this->AccessTokens->newEntity([
            //'oauth_token' => $token,
           // 'session_id' => $sessionId,
            //'expires' => $expireTime,
        ]);
        return $token;
    }

    /**
     * Persists a new access token to permanent storage.
     *
     * @param AccessTokenEntityInterface $accessTokenEntity
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {

    }

    /**
     * Revoke an access token.
     *
     * @param string $tokenId
     */
    public function revokeAccessToken($tokenId)
    {

    }

    /**
     * Check if the access token has been revoked.
     *
     * @param string $tokenId
     *
     * @return bool Return true if this token has been revoked
     */
    public function isAccessTokenRevoked($tokenId)
    {

    }

    /**
     * {@inheritdoc}

     * @param string $token Token to check
     * @return \League\OAuth2\Server\Entity\AbstractTokenEntity
     */
    public function get($token)
    {
        $this->loadModel('OAuthServer.AccessTokens');
        $result = $this->AccessTokens->find()
            ->select([
                'oauth_token',
                'expires'
            ])
            ->where([
                'oauth_token' => $token
            ])
            ->first();

        if ($result) {
            return (new AccessTokenEntity($this->server))->setId($result->oauth_token)
                ->setExpireTime($result->expires);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param \League\OAuth2\Server\Entity\AccessTokenEntity $token Token entity
     * @return mixed
     */
    public function getScopes(AccessTokenEntity $token)
    {
        $this->loadModel('OAuthServer.AccessTokenScopes');
        $result = $this->AccessTokenScopes->find()
            ->contain([
                'Scopes'
            ])
            ->where([
                'oauth_token' => $token->getId()
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
     * @param string $token Token
     * @param int $expireTime Time token expires
     * @param int|string $sessionId Session id
     * @return void
     */
    public function create($token, $expireTime, $sessionId)
    {
        $this->loadModel('OAuthServer.AccessTokens');
        $token = $this->AccessTokens->newEntity([
            'oauth_token' => $token,
            'session_id' => $sessionId,
            'expires' => $expireTime,
        ]);
        $this->AccessTokens->save($token);
    }

    /**
     * {@inheritdoc}
     *
     * @param \League\OAuth2\Server\Entity\AccessTokenEntity $token Token entity
     * @param \League\OAuth2\Server\Entity\ScopeEntity $scope Scope entity
     * @return void
     */
    public function associateScope(AccessTokenEntity $token, ScopeEntity $scope)
    {
        $this->loadModel('OAuthServer.AccessTokenScopes');
        $tokenScope = $this->AccessTokenScopes->newEntity([
            'oauth_token' => $token->getId(),
            'scope_id' => $scope->getId(),
        ]);
        $this->AccessTokenScopes->save($tokenScope);
    }

    /**
     * {@inheritdoc}
     *
     * @param \League\OAuth2\Server\Entity\AccessTokenEntity $token Token entity
     * @return void
     */
    public function delete(AccessTokenEntity $token)
    {
        $this->loadModel('OAuthServer.AccessTokens');
        $accessToken = $this->AccessTokens->findByOauthToken($token->getId())
            ->first();
        $this->AccessTokens->delete($accessToken, ['cascade' => true]);
    }
}
