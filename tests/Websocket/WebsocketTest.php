<?php

namespace SwooleTW\Http\Tests\Websocket;

use Mockery as m;
use InvalidArgumentException;
use SwooleTW\Http\Tests\TestCase;
use SwooleTW\Http\Websocket\Websocket;
use SwooleTW\Http\Websocket\Rooms\RoomContract;

class WebsocketTest extends TestCase
{
    public function testSetBroadcast()
    {
        $websocket = $this->getWebsocket();
        $this->assertFalse($websocket->getIsBroadcast());

        $websocket->broadcast();
        $this->assertTrue($websocket->getIsBroadcast());
    }

    public function testSetTo()
    {
        $websocket = $this->getWebsocket()->to($foo = 'foo');
        $this->assertTrue(in_array($foo, $websocket->getTo()));

        $websocket->toAll($bar = ['foo', 'bar', 'seafood']);
        $this->assertSame($bar, $websocket->getTo());
    }

    public function testSetSender()
    {
        $websocket = $this->getWebsocket()->setSender($fd = 1);
        $this->assertSame($fd, $websocket->getSender());
    }

    public function testJoin()
    {
        $room = m::mock(RoomContract::class);
        $room->shouldReceive('add')
            ->with($sender = 1, $name = 'room')
            ->once();

        $websocket = $this->getWebsocket($room)
            ->setSender($sender)
            ->join($name);
    }

    public function testJoinAll()
    {
        $room = m::mock(RoomContract::class);
        $room->shouldReceive('addAll')
            ->with($sender = 1, $names = ['room1', 'room2'])
            ->once();

        $websocket = $this->getWebsocket($room)
            ->setSender($sender)
            ->joinAll($names);
    }

    public function testLeave()
    {
        $room = m::mock(RoomContract::class);
        $room->shouldReceive('delete')
            ->with($sender = 1, $name = 'room')
            ->once();

        $websocket = $this->getWebsocket($room)
            ->setSender($sender)
            ->leave($name);
    }

    public function testLeaveAll()
    {
        $room = m::mock(RoomContract::class);
        $room->shouldReceive('deleteAll')
            ->with($sender = 1, $names = ['room1', 'room2'])
            ->once();

        $websocket = $this->getWebsocket($room)
            ->setSender($sender)
            ->leaveAll($names);
    }

    public function testCallbacks()
    {
        $websocket = $this->getWebsocket();

        $websocket->on('foo', function () {
            return 'bar';
        });

        $this->assertTrue($websocket->eventExists('foo'));
        $this->assertFalse($websocket->eventExists('bar'));

        $this->expectException(InvalidArgumentException::class);
        $websocket->on('invalid', 123);
    }

    public function testReset()
    {
        $websocket = $this->getWebsocket();
        $websocket->setSender(1)
            ->broadcast()
            ->to('foo');

        $websocket->reset(true);

        $this->assertNull($websocket->getSender());
        $this->assertFalse($websocket->getIsBroadcast());
        $this->assertSame([], $websocket->getTo());
    }

    protected function getWebsocket(RoomContract $room = null)
    {
        return new Websocket($room ?? m::mock(RoomContract::class));
    }
}
