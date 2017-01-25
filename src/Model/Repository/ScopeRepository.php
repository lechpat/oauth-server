<?php

namespace OAuthServer\Model\Repository;

use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class ScopeRepository extends AbstractRepository implements ScopeRepositoryInterface
{
    /**
     * Return information about a scope.
     *
     * @param string $identifier The scope identifier
     *
     * @return ScopeEntityInterface
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        $this->loadModel('OAuthServer.Scopes');
        $result = $this->Scopes->find()
            ->where([
                'id' => $identifier
            ]);

        if (!$result->isEmpty()) {
            $scope = $result->first();
            return $scope;
        }
    }

    /**
     * Given a client, grant type and optional user identifier validate the set of scopes requested are valid and optionally
     * append additional scopes or remove requested scopes.
     *
     * @param ScopeEntityInterface[] $scopes
     * @param string                 $grantType
     * @param ClientEntityInterface  $clientEntity
     * @param null|string            $userIdentifier
     *
     * @return ScopeEntityInterface[]
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    )
    {
        return $scopes;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $scope Scope
     * @param null|string $grantType Type of grant
     * @param null|string $clientId Client
     * @return \League\OAuth2\Server\Entity\EntityTrait
     */
    public function get($scope, $grantType = null, $clientId = null)
    {
        $this->loadModel('OAuthServer.Scopes');
        $result = $this->Scopes->find()
            ->where([
                'id' => $scope
            ])
            ->first();

        if ($result) {
            return (new ScopeEntity($this->server))->hydrate([
                    'id' => $result->id,
                    'description' => $result->description,
                ]);
        }
    }
}
