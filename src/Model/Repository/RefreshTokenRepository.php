<?php

namespace OAuthServer\Model\Repository;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository extends AbstractRepository implements RefreshTokenRepositoryInterface
{
    /**
     * Creates a new refresh token
     *
     * @return RefreshTokenEntityInterface
     */
    public function getNewRefreshToken()
    {
        $this->loadModel('OAuthServer.RefreshTokens');
        return $this->RefreshTokens->newEntity();
    }

    /**
     * Create a new refresh token_name.
     *
     * @param RefreshTokenEntityInterface $refreshTokenEntity
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $this->loadModel('OAuthServer.RefreshTokens');
        $this->RefreshTokens->save($refreshTokenEntity);
    }

    /**
     * Revoke the refresh token.
     *
     * @param string $tokenId
     */
    public function revokeRefreshToken($tokenId)
    {

    }

    /**
     * Check if the refresh token has been revoked.
     *
     * @param string $tokenId
     *
     * @return bool Return true if this token has been revoked
     */
    public function isRefreshTokenRevoked($tokenId)
    {

    }
    /**
     * {@inheritdoc}
     *
     * @param string $token Token
     * @return string
     */
    public function get($token)
    {
        $this->loadModel('OAuthServer.RefreshTokens');
        $result = $this->RefreshTokens->find()
            ->where([
                'refresh_token' => $token
            ])
            ->first();

        if ($result) {
            $token = (new RefreshTokenEntity($this->server))->setId($result->refresh_token)
                ->setExpireTime($result->expires)
                ->setAccessTokenId($result->oauth_token);

            return $token;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param string $token Token
     * @param int $expireTime Expiry time
     * @param string $accessToken Access token
     * @return void
     */
    public function create($token, $expireTime, $accessToken)
    {
        $this->loadModel('OAuthServer.RefreshTokens');
        $refreshToken = $this->RefreshTokens->newEntity([
            'refresh_token' => $token,
            'oauth_token' => $accessToken,
            'expires' => $expireTime,
        ]);
        $this->RefreshTokens->save($refreshToken);
    }

    /**
     * {@inheritdoc}
     *
     * @param \League\OAuth2\Server\Entity\RefreshTokenEntity $token Refresh token
     * @return void
     */
    public function delete(RefreshTokenEntity $token)
    {
        $this->loadModel('OAuthServer.RefreshTokens');
        $this->RefreshTokens->deleteAll([
            'refresh_token' => $token->getId()
        ]);
    }
}
