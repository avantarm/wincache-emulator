<?php

namespace Avantarm\WincacheEmulator\Tests;

use Avantarm\WincacheEmulator\WincacheEmulator;
use PHPUnit\Framework\TestCase;

/**
 * Class WincacheEmulatorTest
 *
 * @package Avantarm\WincacheEmulator
 * @coversDefaultClass \Avantarm\WincacheEmulator\WincacheEmulator
 */
class WincacheEmulatorTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp()
    {
        WincacheEmulator::clear();
    }

    /**
     * @covers ::add
     */
    public function testAddNew()
    {
        $this->assertTrue(WincacheEmulator::add('key1', 1));

        $this->assertEquals([], WincacheEmulator::add([
            'key2' => 2,
            'key3' => 3,
        ], null));
    }

    /**
     * @covers ::add
     */
    public function testAddExists()
    {
        WincacheEmulator::set('key1', 1);
        WincacheEmulator::set('key2', 2);

        $this->assertFalse(WincacheEmulator::add('key1', 1));

        $this->assertFalse(WincacheEmulator::add([
            'key1' => 1,
            'key2' => 2,
        ], null));

        $this->assertEquals(['key1' => -1, 'key2' => -1], WincacheEmulator::add([
            'key1' => 1,
            'key2' => 2,
            'key3' => 3,
            'key4' => 4,
        ], null));

        $this->assertTrue(WincacheEmulator::add('key5', 5));
    }

    /**
     * @covers ::cas
     */
    public function testCas()
    {
        $this->assertFalse(WincacheEmulator::cas('key1', 1, 2));

        WincacheEmulator::set('key1', 2);

        $this->assertFalse(WincacheEmulator::cas('key1', 1, 2));

        $this->assertTrue(WincacheEmulator::cas('key1', 2, 3));

        $this->assertEquals(3, WincacheEmulator::get('key1'));
    }

    /**
     * @covers ::clear
     */
    public function testClear()
    {
        WincacheEmulator::set('key1', 1);
        WincacheEmulator::set('key2', 2);

        WincacheEmulator::clear();

        $this->assertFalse(WincacheEmulator::get('key1', $success));
        $this->assertFalse($success);

        $this->assertFalse(WincacheEmulator::get('key2', $success));
        $this->assertFalse($success);

        $info = WincacheEmulator::info(true);

        $this->assertEquals(0, $info['total_item_count']);
    }

    /**
     * @covers ::dec
     */
    public function testDec()
    {
        // It doesn't auto-create key.
        $this->assertFalse(WincacheEmulator::dec('key1', 1, $success));
        $this->assertFalse($success);

        WincacheEmulator::set('key1', 1);

        $this->assertEquals(0, WincacheEmulator::dec('key1', 1, $success));
        $this->assertTrue($success);
    }

    /**
     * @covers ::dec
     */
    public function testDelete()
    {
        $this->assertFalse(WincacheEmulator::delete('key1'));

        WincacheEmulator::set('key1', 1);

        $this->assertTrue(WincacheEmulator::delete('key1'));

        WincacheEmulator::set('key1', 1);
        WincacheEmulator::set('key2', 1);

        $this->assertFalse(WincacheEmulator::delete('key3'));
        $this->assertFalse(WincacheEmulator::delete(['key3', 'key4']));

        $this->assertEquals(['key1', 'key2'], WincacheEmulator::delete(['key1', 'key2', 'key3', 'key4']));
    }

    /**
     * @covers ::exists
     */
    public function testExists()
    {
        $this->assertFalse(WincacheEmulator::exists('key1'));

        WincacheEmulator::set('key1', 1);

        $this->assertTrue(WincacheEmulator::exists('key1'));
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        $this->assertFalse(WincacheEmulator::get('key1', $success));
        $this->assertFalse($success);

        WincacheEmulator::set('key1', 1);

        $this->assertEquals(1, WincacheEmulator::get('key1', $success));
        $this->assertTrue($success);
    }

    /**
     * @covers ::get
     */
    public function testGetMultiple()
    {
        $this->assertEquals([], WincacheEmulator::get(['key1', 'key2'], $success));
        $this->assertTrue($success);

        WincacheEmulator::set('key1', 1);
        WincacheEmulator::set('key2', 2);

        $this->assertEquals(['key1' => 1, 'key2' => 2], WincacheEmulator::get(['key1', 'key2'], $success));
        $this->assertTrue($success);

        $this->assertEquals(['key1' => 1, 'key2' => 2], WincacheEmulator::get(['key1', 'key2', 'key3'], $success));
        $this->assertTrue($success);
    }

    /**
     * @covers ::inc
     */
    public function testInc()
    {
        // It doesn't auto-create key.
        $this->assertFalse(WincacheEmulator::inc('key1', 1, $success));
        $this->assertFalse($success);

        WincacheEmulator::set('key1', 1);

        $this->assertEquals(2, WincacheEmulator::inc('key1', 1, $success));
        $this->assertTrue($success);
    }

    /**
     * @covers ::info
     */
    public function testInfoSummaryOnly()
    {
        $this->assertEquals([
            'total_cache_uptime' => 0,
            'is_local_cache'     => false,
            'total_item_count'   => 0,
            'total_hit_count'    => 0,
            'total_miss_count'   => 0,
        ], WincacheEmulator::info(true));

        WincacheEmulator::set('key1', 1);

        $this->assertEquals([
            'total_cache_uptime' => 0,
            'is_local_cache'     => false,
            'total_item_count'   => 1,
            'total_hit_count'    => 0,
            'total_miss_count'   => 0,
        ], WincacheEmulator::info(true));
    }

    /**
     * @covers ::info
     */
    public function testInfoKey()
    {
        $this->assertEquals([
            'total_cache_uptime' => 0,
            'is_local_cache'     => false,
            'total_item_count'   => 0,
            'total_hit_count'    => 0,
            'total_miss_count'   => 0,
            'ucache_entries'     => [],
        ], WincacheEmulator::info(false, 'key1'));

        WincacheEmulator::set('key1', 1, 100);

        $this->assertEquals([
            'total_cache_uptime' => 0,
            'is_local_cache'     => false,
            'total_item_count'   => 1,
            'total_hit_count'    => 0,
            'total_miss_count'   => 0,
            'ucache_entries'     => [
                [
                    'key_name'    => 'key1',
                    'value_type'  => \gettype(1),
                    'is_session'  => 0,
                    'ttl_seconds' => 100,
                    'age_seconds' => 0,
                    'hitcount'    => 0,
                ]
            ],
        ], WincacheEmulator::info(false, 'key1'));
    }

    /**
     * @covers ::info
     */
    public function testInfoAllKeys()
    {
        $this->assertEquals([
            'total_cache_uptime' => 0,
            'is_local_cache'     => false,
            'total_item_count'   => 0,
            'total_hit_count'    => 0,
            'total_miss_count'   => 0,
            'ucache_entries'     => [],
        ], WincacheEmulator::info());

        WincacheEmulator::set('key1', 1, 100);
        WincacheEmulator::set('key2', []);

        $this->assertEquals([
            'total_cache_uptime' => 0,
            'is_local_cache'     => false,
            'total_item_count'   => 2,
            'total_hit_count'    => 0,
            'total_miss_count'   => 0,
            'ucache_entries'     => [
                [
                    'key_name'    => 'key1',
                    'value_type'  => \gettype(1),
                    'is_session'  => 0,
                    'ttl_seconds' => 100,
                    'age_seconds' => 0,
                    'hitcount'    => 0,
                ],
                [
                    'key_name'    => 'key2',
                    'value_type'  => \gettype([]),
                    'is_session'  => 0,
                    'ttl_seconds' => 0,
                    'age_seconds' => 0,
                    'hitcount'    => 0,
                ],
            ],
        ], WincacheEmulator::info());
    }

    /**
     * @covers ::info
     */
    public function meminfo()
    {
        $this->assertEquals([
            'memory_total'    => 0, // amount of memory in bytes allocated for the user cache
            'memory_free'     => 0, // amount of free memory in bytes available for the user cache
            'num_used_blks'   => 1, // number of memory blocks used by the user cache
            'num_free_blks'   => 1, // number of free memory blocks available for the user cache
            'memory_overhead' => 0, // amount of memory in bytes used for the user cache internal structures
        ], WincacheEmulator::meminfo());
    }

    /**
     * @covers ::set
     */
    public function testSet()
    {
        $this->assertTrue(WincacheEmulator::set('key1', 1));

        $this->assertEquals(1, WincacheEmulator::get('key1', $success));
        $this->assertTrue($success);
    }

    /**
     * @covers ::get
     */
    public function testSetMultiple()
    {
        $this->assertEquals([], WincacheEmulator::set(['key1' => 1, 'key2' => 2], null));

        $this->assertEquals(['key1' => 1, 'key2' => 2], WincacheEmulator::get(['key1', 'key2'], $success));
        $this->assertTrue($success);
    }

    /**
     * Time-consuming tests last.
     */

    /**
     * @covers ::add
     */
    public function testAddTtl()
    {
        WincacheEmulator::add('key1', 1, 1);

        sleep(2);

        $this->assertFalse(WincacheEmulator::get('key1', $success));
        $this->assertFalse($success);
    }

    /**
     * @covers ::add
     */
    public function testSetTtl()
    {
        WincacheEmulator::set('key1', 1, 1);

        sleep(2);

        $this->assertFalse(WincacheEmulator::get('key1', $success));
        $this->assertFalse($success);
    }
}
