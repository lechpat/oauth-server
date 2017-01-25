<?php
namespace OAuthServer\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AccessTokensFixture
 *
 */
class AccessTokensFixture extends TestFixture
{

    /**
     * Table name
     *
     * @var string
     */
    public $table = 'oauth_access_tokens';

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'oauth_token' => ['type' => 'string', 'length' => 40, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'session_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'expires' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['oauth_token'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'oauth_token' => '197fa346-a502-4e23-9486-9082689f75fa',
            'session_id' => 1,
            'expires' => 1
        ],
    ];
}
