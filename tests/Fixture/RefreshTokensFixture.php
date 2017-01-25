<?php
namespace OAuthServer\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RefreshTokensFixture
 *
 */
class RefreshTokensFixture extends TestFixture
{

    /**
     * Table name
     *
     * @var string
     */
    public $table = 'oauth_refresh_tokens';

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'refresh_token' => ['type' => 'string', 'length' => 40, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'oauth_token' => ['type' => 'string', 'length' => 40, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'expires' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['refresh_token'], 'length' => []],
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
            'refresh_token' => '63fc9dc0-ec2c-4bb3-867c-555dfcecdd8b',
            'oauth_token' => 'Lorem ipsum dolor sit amet',
            'expires' => 1
        ],
    ];
}
