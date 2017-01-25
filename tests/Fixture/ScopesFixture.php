<?php
namespace OAuthServer\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ScopesFixture
 *
 */
class ScopesFixture extends TestFixture
{

    /**
     * Table name
     *
     * @var string
     */
    public $table = 'oauth_scopes';

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'string', 'length' => 40, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'description' => ['type' => 'string', 'length' => 200, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
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
            'id' => '352d7e09-6a32-45fa-81b5-ebdecb985755',
            'description' => 'Lorem ipsum dolor sit amet'
        ],
    ];
}
