<?php
namespace OAuthServer\Model\Repository;

use Cake\Datasource\ModelAwareTrait;
use League\OAuth2\Server\Repositories\RepositoryInterface;

abstract class AbstractRepository implements RepositoryInterface
{
    use ModelAwareTrait;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->modelFactory('Table', ['Cake\ORM\TableRegistry', 'get']);
    }
}
