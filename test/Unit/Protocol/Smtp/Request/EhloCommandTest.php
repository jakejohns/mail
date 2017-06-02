<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Protocol\Smtp\Request;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Request\EhloCommand;
use Genkgo\TestMail\AbstractTestCase;

final class EhloCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_executes()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->at(0))
            ->method('connect');

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with("EHLO host.example.com\r\n");

        $command = new EhloCommand('host.example.com');
        $command->execute($connection);
    }

}