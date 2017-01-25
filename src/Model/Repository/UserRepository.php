<?php
namespace OAuthServer\Model\Repository;

use Cake\Core\Configure;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;

class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    protected $_usersTable = null;
    /**
     * Gets the users table instance
     *
     * @return Table
     */
    public function getUsersTable()
    {
        if ($this->_usersTable instanceof Table) {
            return $this->_usersTable;
        }
        $this->_usersTable = TableRegistry::get(Configure::read('Users.table'));
        return $this->_usersTable;
    }
    /**
     * Set the users table
     *
     * @param Table $table table
     * @return void
     */
    public function setUsersTable(Table $table)
    {
        $this->_usersTable = $table;
    }

    /**
     * Get a user entity.
     *
     * @param string                $username
     * @param string                $password
     * @param string                $grantType    The grant type used
     * @param ClientEntityInterface $clientEntity
     *
     * @return UserEntityInterface
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    )
    {
        $passwordHasher = 'Default';
//        $this->loadModel('OAuthServer.Users');

        $this->loadModel('Origination.Applicants');
        $query = $this->Applicants->find()
            ->where([
                $this->Applicants->aliasField('username') => $username
            ]);

//            ->where([
//                $this->Users->aliasField('id') => $clientIdentifier
//            ]);

//        if ($clientSecret !== null) {
//            $query->where([$this->Users->aliasField('client_secret') => $clientSecret]);
//        }

//        if ($redirectUri) {
//            $query->where([$this->Clients->aliasField('redirect_uri') => $redirectUri]);
//        }

        if (!$query->isEmpty()) {
            $user = $query->first();

            if ($password !== null) {
                $hasher = \Cake\Auth\PasswordHasherFactory::build($passwordHasher); 
                $hashedPassword = $user->get('password');
                if ($hasher->check($password, $hashedPassword)) {
                    $user->unsetProperty('password');
                    return $user;
                }
            }
        }

//        return $this->Users->newEntity();
    }

    
}
