<?php

use App\Controller\ICalController;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ICalControllerTest extends TestCase
{
    public function testProcessICal()
    {
        //Create a dummy LoggerInterface object
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        //Create a dummy Request object
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        //Define the behavior of the getContent() method
        $content = "BEGIN:VEVENT\nUID:1\nDTSTART;VALUE=DATE:2024-02-25\nDTEND;VALUE=DATE:2024-02-26\nSUMMARY:Test Event\nEND:VEVENT";
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn($content);

        //Create an object of the ICalController class, passing a dummy logger object
        $icalController = new ICalController($logger);

        // Wywołujemy metodę processICal
        $response = $icalController->processICal($request);

        // We check whether the method returns a valid HTTP response
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}
