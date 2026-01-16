<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../vendor/autoload.php';

use Dls\Evoting\controllers\Controller;
use Dls\Evoting\models\Poll;
use Dls\Evoting\models\Post;
use Dls\Evoting\models\User;

class VoteCardFlowTest extends TestCase
{
    private function makePoll(): Poll {
        $now = new DateTime();
        $later = (clone $now)->modify('+1 day');
        return new Poll(2, 'Flow Poll', $now, $later, 'in_progress', 'desc', false, true);
    }

    private function makeUser(int $id, string $name = 'User'): User {
        return new User($id, $name, $name . '@example.com', 'mat' . $id, 'rfid' . $id, false, null, false, 'active');
    }

    public function testIsCardCompletedForVotes(): void
    {
        $controller = new Controller();
        $poll = $this->makePoll();

        $posts = [];
        for ($i = 1; $i <= 5; $i++) {
            $posts[] = new Post($i, $poll->getId(), 'Post ' . $i, []);
        }

        // incomplete votes
        $votes = [1 => 11, 2 => 12];
        $this->assertFalse($controller->isCardCompletedForVotes($posts, $votes));

        // complete votes
        $votes = [];
        foreach ($posts as $p) { $votes[$p->getId()] = $p->getId() * 10 + 1; }
        $this->assertTrue($controller->isCardCompletedForVotes($posts, $votes));
    }

    public function testPdfAfterCompletingCardViaBuild(): void
    {
        $controller = new Controller();
        $poll = $this->makePoll();
        $user = $this->makeUser(10, 'Bob');

        $posts = [];
        $votes = [];

        for ($i = 1; $i <= 4; $i++) {
            $cand = ['candId' => $i, 'user_id' => $i, 'name' => 'Cand' . $i];
            $posts[] = new Post($i, $poll->getId(), 'Post ' . $i, [$cand]);
            $votes[$i] = $cand['candId'];
        }

        $this->assertTrue($controller->isCardCompletedForVotes($posts, $votes));

        $pdf = $controller->buildVoteReceiptPdfForUserFromData($poll, $user, $posts, $votes);
        $this->assertStringContainsString('%PDF', substr($pdf, 0, 8));
        $this->assertStringContainsString('Cand1', $pdf);
    }

    public function testRepeatedPdfGenerationAfterComplete(): void
    {
        $controller = new Controller();
        $poll = $this->makePoll();
        $user = $this->makeUser(11, 'Charlie');

        $posts = [];
        $votes = [];
        for ($i = 1; $i <= 6; $i++) {
            $cand = ['candId' => $i, 'user_id' => $i, 'name' => 'Cand' . $i];
            $posts[] = new Post($i, $poll->getId(), 'Post ' . $i, [$cand]);
            $votes[$i] = $cand['candId'];
        }

        // generate the PDF multiple times
        for ($k = 0; $k < 3; $k++) {
            $pdf = $controller->buildVoteReceiptPdfForUserFromData($poll, $user, $posts, $votes);
            $this->assertStringContainsString('%PDF', substr($pdf, 0, 8));
        }
    }
}
