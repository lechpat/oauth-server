<?php
namespace OAuthServer\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;
use OAuthServer\Controller\Component\OAuthClientComponent;

/**
 * OAuthServer\Controller\Component\OAuthClientComponent Test Case
 */
class OAuthClientComponentTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \OAuthServer\Controller\Component\OAuthClientComponent
     */
    public $OAuthClient;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $registry = new ComponentRegistry();
        $this->OAuthClient = new OAuthClientComponent($registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->OAuthClient);

        parent::tearDown();
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testInitialization()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
