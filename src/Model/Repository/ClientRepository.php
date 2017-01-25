<?php

namespace OAuthServer\Model\Repository;

use League\OAuth2\Server\Entity\ClientEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ClientRepository extends AbstractRepository implements ClientRepositoryInterface
{
    /**
     * Get a client.
     *
     * @param string      $clientIdentifier   The client's identifier
     * @param string      $grantType          The grant type used
     * @param null|string $clientSecret       The client's secret (if sent)
     * @param bool        $mustValidateSecret If true the client must attempt to validate the secret if the client
     *                                        is confidential
     *
     * @return ClientEntityInterface
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
        $this->loadModel('OAuthServer.Clients');
        $query = $this->Clients->find()
            ->where([
                $this->Clients->aliasField('id') => $clientIdentifier
            ]);

        if ($clientSecret !== null) {
            $query->where([$this->Clients->aliasField('client_secret') => $clientSecret]);
        }

//        if ($redirectUri) {
//            $query->where([$this->Clients->aliasField('redirect_uri') => $redirectUri]);
//        }

        if (!$query->isEmpty()) {
            $client = $query->first();
            return $client;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param string $clientId Client id
     * @param null $clientSecret Client secret
     * @param null $redirectUri Redirect uri
     * @param null $grantType Grant type
     * @return \League\OAuth2\Server\Entity\ClientEntity
     */
/*
    public function get($clientId, $clientSecret = null, $redirectUri = null, $grantType = null)
    {
        $this->loadModel('OAuthServer.Clients');
        $query = $this->Clients->find()
            ->where([
                $this->Clients->aliasField('id') => $clientId
            ]);

        if ($clientSecret !== null) {
            $query->where([$this->Clients->aliasField('client_secret') => $clientSecret]);
        }

        if ($redirectUri) {
            $query->where([$this->Clients->aliasField('redirect_uri') => $redirectUri]);
        }

        $result = $query->first();
        if ($result) {
            $client = new ClientEntity($this->server);
            $client->hydrate([
                'id' => $result->id,
                'name' => $result->name
            ]);

            return $client;
        }
    }
*/
    /**
     * {@inheritdoc}
     *
     * @param \League\OAuth2\Server\Entity\SessionEntity $session Session entity
     * @return \League\OAuth2\Server\Entity\ClientEntity
     */
    public function getBySession(SessionEntity $session)
    {
        $this->loadModel('OAuthServer.Sessions');
        $result = $this->Sessions->find()
            ->contain(['Clients'])
            ->where([
                'Sessions.id' => $session->getId()
            ])
            ->first();

        if ($result) {
            $client = new ClientEntity($this->server);
            $client->hydrate([
                'id' => $result->client->id,
                'name' => $result->client->name,
            ]);

            return $client;
        }
    }
}
