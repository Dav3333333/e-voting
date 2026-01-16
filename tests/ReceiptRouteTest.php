<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../vendor/autoload.php';

use Dls\Evoting\controllers\Controller;

class ReceiptRouteTest extends TestCase
{
    public function testGetReceiptWithInvalidPollOrCard(): void
    {
        $controller = new Controller();

        // invalid poll
        $res = $controller->getVoteReceiptPdfForCard(999999, 'xxxx');
        $this->assertIsArray($res);
        $this->assertEquals('fail', $res['status'] ?? 'fail');

        // invalid card on existing poll id (use 1 which may or may not exist depending on DB)
        $res2 = $controller->getVoteReceiptPdfForCard(1, 'nonexistent_code');
        $this->assertIsArray($res2);
        $this->assertEquals('fail', $res2['status'] ?? 'fail');
    }
}
