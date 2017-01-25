<?php
namespace OAuthServer\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use OAuthServer\Model\Table\RefreshTokensTable;

/**
 * OAuthServer\Model\Table\RefreshTokensTable Test Case
 */
class RefreshTokensTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \OAuthServer\Model\Table\RefreshTokensTable
     */
    public $RefreshTokens;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.o_auth_server.refresh_tokens',
        'plugin.o_auth_server.access_tokens',
        'plugin.o_auth_server.sessions',
        'plugin.o_auth_server.session_scopes',
        'plugin.o_auth_server.scopes',
        'plugin.o_auth_server.access_token_scopes',
        'plugin.o_auth_server.auth_code_scopes',
        'plugin.o_auth_server.auth_codes',
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
        $config = TableRegistry::exists('RefreshTokens') ? [] : ['className' => 'OAuthServer\Model\Table\RefreshTokensTable'];
        $this->RefreshTokens = TableRegistry::get('RefreshTokens', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->RefreshTokens);

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
