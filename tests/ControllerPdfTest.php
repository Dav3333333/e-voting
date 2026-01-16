<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../vendor/autoload.php';

use Dls\Evoting\controllers\Controller;
use Dls\Evoting\models\Poll;
use Dls\Evoting\models\Post;
use Dls\Evoting\models\User;

class ControllerPdfTest extends TestCase
{
    private function makePoll(): Poll {
        $now = new DateTime();
        $later = (clone $now)->modify('+1 day');
        return new Poll(1, 'Test Poll', $now, $later, 'in_progress', 'desc', false, true);
    }

    private function makeUser(int $id, string $name = 'User'): User {
        return new User($id, $name, $name . '@example.com', 'mat' . $id, 'rfid' . $id, false, null, false, 'active');
    }

    public function testPdfGenerationWithManyPosts(): void
    {
        $controller = new Controller();
        $poll = $this->makePoll();
        $user = $this->makeUser(1, 'Alice');

        $posts = [];
        $votes = [];
        $n = 30; // many posts
        for ($i = 1; $i <= $n; $i++) {
            // create 3 candidates for each post
            $candA = ['candId' => ($i * 10) + 1, 'user_id' => ($i * 100) + 1, 'name' => 'CandA' . $i];
            $candB = ['candId' => ($i * 10) + 2, 'user_id' => ($i * 100) + 2, 'name' => 'CandB' . $i];
            $candC = ['candId' => ($i * 10) + 3, 'user_id' => ($i * 100) + 3, 'name' => 'CandC' . $i];
            $candidateList = [$candA, $candB, $candC];
            $post = new Post($i, $poll->getId(), 'Post ' . $i, $candidateList);
            $posts[] = $post;

            // vote for candidate B for even posts, else A
            $votes[$i] = ($i % 2 === 0) ? $candB['candId'] : $candA['candId'];
        }

        $pdf = $controller->buildVoteReceiptPdfForUserFromData($poll, $user, $posts, $votes);

        // PDF header
        $this->assertIsString($pdf);
        $this->assertStringContainsString('%PDF', substr($pdf, 0, 8));

        // check that some candidate names appear in the PDF binary
        $this->assertStringContainsString('CandA1', $pdf);
        $this->assertStringContainsString('CandB2', $pdf);
    }

    public function testManySimultaneousGenerations(): void
    {
        $controller = new Controller();
        $poll = $this->makePoll();

        $posts = [];
        $votes = [];
        for ($i = 1; $i <= 8; $i++) {
            $cand = ['candId' => $i, 'user_id' => $i, 'name' => 'Cand' . $i];
            $posts[] = new Post($i, $poll->getId(), 'Post ' . $i, [$cand]);
            $votes[$i] = $cand['candId'];
        }

        // simulate multiple users generating PDFs concurrently (sequential in unit test)
        for ($u = 1; $u <= 10; $u++) {
            $user = $this->makeUser($u, 'User' . $u);
            $pdf = $controller->buildVoteReceiptPdfForUserFromData($poll, $user, $posts, $votes);
            $this->assertStringContainsString('%PDF', substr($pdf, 0, 8));
            // ensure title present
            $this->assertStringContainsString('Test Poll', $pdf);
        }

        $this->assertTrue(true); // reached end without exceptions
    }
}
