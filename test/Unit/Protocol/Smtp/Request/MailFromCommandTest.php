<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Protocol\Smtp\Request;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Request\MailFromCommand;
use Genkgo\TestMail\AbstractTestCase;

final class MailFromCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_executes()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->once())
            ->method('send')
            ->with("MAIL FROM:<me@localhost>\r\n");

        $command = new MailFromCommand(new EmailAddress('me@localhost'));
        $command->execute($connection);
    }

}