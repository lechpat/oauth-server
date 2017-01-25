<?php
namespace OAuthServer\Model\Entity;

use Cake\ORM\Entity;
use \League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;

/**
 * User Entity
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 */

class User extends Entity implements UserEntityInterface
{
//    use EntityTrait;
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];

    protected $_virtual = ['identifier'];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
//        'password',
//        'token'
    ];

    /**
     * @return mixed
     */
    public function getIdentifier()
    {
        return isset($this->_properties['id']) ? $this->_properties['id'] : null;
    }

    /**
     * @param mixed $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->set('id',$identifier);
    }

    protected function _getIdentifier()
    {
        return $this->_properties['id'];
    }
}
