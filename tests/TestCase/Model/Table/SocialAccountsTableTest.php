<?php
namespace OAuthServer\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use OAuthServer\Model\Table\SocialAccountsTable;

/**
 * OAuthServer\Model\Table\SocialAccountsTable Test Case
 */
class SocialAccountsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \OAuthServer\Model\Table\SocialAccountsTable
     */
    public $SocialAccounts;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.o_auth_server.social_accounts',
        'plugin.o_auth_server.users'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('SocialAccounts') ? [] : ['className' => 'OAuthServer\Model\Table\SocialAccountsTable'];
        $this->SocialAccounts = TableRegistry::get('SocialAccounts', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->SocialAccounts);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
