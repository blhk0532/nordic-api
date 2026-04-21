<?php

namespace Shahkochaki\Ami\Tests;

use Clue\React\Ami\Client;
use React\EventLoop\LoopInterface;
use React\Promise\FulfilledPromise;
use React\Promise\Promise;
use React\SocketClient\ConnectorInterface;
use React\Stream\Stream;
use Shahkochaki\Ami\Parser;

class Factory extends \Shahkochaki\Ami\Factory
{
    public function __construct(LoopInterface $loop, ConnectorInterface $connector, Stream $stream)
    {
        parent::__construct($loop, $connector);
        $this->stream = $stream;
    }

    /**
     * Create client.
     *
     *
     * @return Promise
     */
    public function create(array $options = [])
    {
        return new FulfilledPromise(new Client($this->stream, new Parser));
    }
}
