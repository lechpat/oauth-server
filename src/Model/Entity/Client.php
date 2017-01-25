<?php

namespace OAuthServer\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use Cake\Utility\Text;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class Client extends Entity implements ClientEntityInterface
{
    /**
     * Get the client's identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_properties['id'];
    }

    /**
     * Get the client's name.
     *
     * @return string
     */
    public function getName()
    {
        $this->_properties['name'];
    }

    /**
     * Returns the registered redirect URI (as a string).
     *
     * Alternatively return an indexed array of redirect URIs.
     *
     * @return string|string[]
     */
    public function getRedirectUri()
    {
        $this->_properties['redirect_uri'];
    }

    /**
     * Create a new, pretty (as in moderately, not beautiful - that can't be guaranteed ;-) random client secret
     *
     * @return void
     */
    public function generateSecret()
    {
        $this->client_secret = Security::hash(Text::uuid(), 'sha1', true);
        $this->_original['client_secret'] = $this->client_secret;
    }

    /**
     * @return \Cake\Datasource\EntityInterface|mixed|null
     */
    protected function _getParent()
    {
        if (empty($this->parent_model)) {
            return null;
        }
        $parentTable = TableRegistry::get($this->parent_model);
        
        return $parentTable->get($this->parent_id);
    }

    /**
     * @param string $name Existing name
     * @return string
     */
    protected function _getName($name)
    {
        $parent = $this->parent;

        return $parent ? $parent->name : $name;
    }
}
