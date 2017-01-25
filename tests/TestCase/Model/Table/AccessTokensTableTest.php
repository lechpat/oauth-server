<?php
namespace OAuthServer\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use OAuthServer\Model\Table\AccessTokensTable;

/**
 * OAuthServer\Model\Table\AccessTokensTable Test Case
 */
class AccessTokensTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \OAuthServer\Model\Table\AccessTokensTable
     */
    public $AccessTokens;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.o_auth_server.access_tokens',
        'plugin.o_auth_server.sessions',
        'plugin.o_auth_server.session_scopes',
        'plugin.o_auth_server.scopes',
        'plugin.o_auth_server.access_token_scopes',
        'plugin.o_auth_server.auth_code_scopes',
        'plugin.o_auth_server.auth_codes',
        'plugin.o_auth_server.refresh_tokens',
        'plugin.o_auth_server.clients'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('AccessTokens') ? [] : ['className' => 'OAuthServer\Model\Table\AccessTokensTable'];
        $this->AccessTokens = TableRegistry::get('AccessTokens', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->AccessTokens);

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
}
