<?php
declare(strict_types=1);

namespace TutuRu\Tests\EtcdConfig;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Bridge\SimpleCache\SimpleCacheBridge;
use TutuRu\Config\Exception\ConfigPathNotExistExceptionInterface;
use TutuRu\Config\Exception\ConfigValueUpdateExceptionInterface;
use TutuRu\Config\Exception\InvalidConfigExceptionInterface;
use TutuRu\EtcdConfig\EtcdConfig;
use TutuRu\EtcdConfig\MutableEtcdConfig;

class EtcdConfigTest extends BaseTest
{
    private const CHECKED_CACHE_NS = 'tutu_env_config_etcd_';


    private function createBaseFixture()
    {
        $client = $this->createEtcdClient();
        $client->setValue(self::CONFIG_ROOT_DIR . '/nodeOne/nodeA', 'A');
        $client->setValue(self::CONFIG_ROOT_DIR . '/nodeOne/nodeB', 'B');
        $client->setValue(self::CONFIG_ROOT_DIR . '/nodeOne/nodeC/subNode', '3rd');
        $client->setValue(self::CONFIG_ROOT_DIR . '/nodeTwo', 'Two');
        $client->setValue(self::CONFIG_ROOT_DIR . '/nodeThree/test', 'test');
        $client->setValue(self::CONFIG_ROOT_DIR . '/nodeThree/subNode/0', '00');
        $client->setValue(self::CONFIG_ROOT_DIR . '/nodeThree/subNode/1', '11');
        $client->setValue(self::CONFIG_ROOT_DIR . '/nodeArray/0', 'Zero');
        $client->setValue(self::CONFIG_ROOT_DIR . '/nodeArray/1', 'One');
        $client->setValue(self::CONFIG_ROOT_DIR . '/nodeArray/2', 'Two');
        $client->setValue(self::CONFIG_ROOT_DIR . '/nodePartialArray/1', 'One');
        $client->setValue(self::CONFIG_ROOT_DIR . '/nodePartialArray/2', 'Two');
    }


    public function testLoad()
    {
        $this->createBaseFixture();

        $clientFactory = new EtcdClientMockFactory($this);
        $clientFactory->createFromEnv(self::CONFIG_ROOT_DIR)
            ->expects($this->exactly(1))
            ->method('getDirectoryNodesAsArray');

        new EtcdConfig(self::CONFIG_ROOT_DIR, null, null, $clientFactory);
    }


    public function testLoadWithCache()
    {
        $this->createBaseFixture();

        $clientFactory = new EtcdClientMockFactory($this);
        $clientFactory->createFromEnv(self::CONFIG_ROOT_DIR)
            ->expects($this->exactly(0))
            ->method('getDirectoryNodesAsArray');

        $cache = new SimpleCacheBridge(new ArrayCachePool());
        $cache->set(self::CHECKED_CACHE_NS . self::CONFIG_ROOT_DIR, ['nodeOne' => 'test']);

        new EtcdConfig(self::CONFIG_ROOT_DIR, $cache, null, $clientFactory);
    }


    public function testLoadWithEmptyCache()
    {
        $this->createBaseFixture();

        $clientFactory = new EtcdClientMockFactory($this);
        $clientFactory->createFromEnv(self::CONFIG_ROOT_DIR)
            ->expects($this->exactly(1))
            ->method('getDirectoryNodesAsArray');

        $cache = new SimpleCacheBridge(new ArrayCachePool());

        new EtcdConfig(self::CONFIG_ROOT_DIR, $cache, null, $clientFactory);
        $this->assertArrayHasKey(
            'nodeOne',
            $cache->get(self::CHECKED_CACHE_NS . self::CONFIG_ROOT_DIR)
        );
    }


    public function testLoadWithNotExistingDir()
    {
        $this->expectException(InvalidConfigExceptionInterface::class);

        $clientFactory = new EtcdClientMockFactory($this);
        new EtcdConfig(self::CONFIG_ROOT_DIR, null, null, $clientFactory);
    }


    public function getValueDataProvider()
    {
        return [
            ['nodeTwo', 'Two'],
            ['notExistingNode', null],
            ['nodeOne.notExisting', null],
            ['nodeOne.notExisting.subNode', null],
            ['nodeOne', ['nodeA' => 'A', 'nodeB' => 'B', 'nodeC' => ['subNode' => '3rd']]],
            ['nodeOne.nodeC', ['subNode' => '3rd']],
            ['nodeOne.nodeA', 'A'],
            ['nodeArray', ['Zero', 'One', 'Two']],
            ['nodeThree', ['test' => 'test', 'subNode' => ['00', '11']]],
            ['nodePartialArray', [1 => 'One', 2 => 'Two']],
        ];
    }


    /**
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($node, $expectedResult)
    {
        $this->createBaseFixture();

        $clientFactory = new EtcdClientMockFactory($this);
        $config = new EtcdConfig(self::CONFIG_ROOT_DIR, null, null, $clientFactory);
        $this->assertEquals($expectedResult, $config->getValue($node));
    }


    public function testGetRequiredValue()
    {
        $this->createBaseFixture();

        $clientFactory = new EtcdClientMockFactory($this);
        $config = new EtcdConfig(self::CONFIG_ROOT_DIR, null, null, $clientFactory);
        $this->expectException(ConfigPathNotExistExceptionInterface::class);
        $config->getValue("not-exist", true);
    }


    public function testGetDefaultValue()
    {
        $this->createBaseFixture();

        $clientFactory = new EtcdClientMockFactory($this);
        $config = new EtcdConfig(self::CONFIG_ROOT_DIR, null, null, $clientFactory);
        $this->assertEquals('abc', $config->getValue("not-exist", false, 'abc'));
    }


    public function testGetValueWithExistingCache()
    {
        $this->createBaseFixture();

        $cache = new SimpleCacheBridge(new ArrayCachePool());
        $cache->set(self::CHECKED_CACHE_NS . self::CONFIG_ROOT_DIR, ['nodeTwo' => 'Three']);

        $clientFactory = new EtcdClientMockFactory($this);
        $config = new EtcdConfig(self::CONFIG_ROOT_DIR, $cache, null, $clientFactory);
        $this->assertEquals('Three', $config->getValue('nodeTwo'));
    }


    public function testSetValue()
    {
        $this->createBaseFixture();

        $clientFactory = new EtcdClientMockFactory($this);
        $clientFactory->createFromEnv(self::CONFIG_ROOT_DIR)
            ->expects($this->exactly(1))
            ->method('setValue');

        $config = new MutableEtcdConfig(self::CONFIG_ROOT_DIR, null, null, $clientFactory);
        $config->setValue('nodeTwo', 'Three');
        $this->assertEquals('Three', $config->getValue('nodeTwo'));

        $config = new MutableEtcdConfig(self::CONFIG_ROOT_DIR, null, null, $clientFactory);
        $this->assertEquals('Three', $config->getValue('nodeTwo'));
    }


    public function testSetValueWithCache()
    {
        $this->createBaseFixture();

        $clientFactory = new EtcdClientMockFactory($this);
        $clientFactory->createFromEnv(self::CONFIG_ROOT_DIR)
            ->expects($this->exactly(1))
            ->method('setValue');

        $cache = new SimpleCacheBridge(new ArrayCachePool());

        $config = new MutableEtcdConfig(self::CONFIG_ROOT_DIR, $cache, null, $clientFactory);
        $config->setValue('nodeTwo', 'Three');
        $this->assertEquals(
            'Three',
            $cache->get(self::CHECKED_CACHE_NS . self::CONFIG_ROOT_DIR)['nodeTwo']
        );
    }


    public function testSetValueWithForbiddenNodeName()
    {
        $this->createBaseFixture();

        $this->expectException(ConfigValueUpdateExceptionInterface::class);
        $clientFactory = new EtcdClientMockFactory($this);
        $config = new MutableEtcdConfig(self::CONFIG_ROOT_DIR, null, null, $clientFactory);
        $config->setValue('nodeTwo.List.One', 'One');
    }
}
