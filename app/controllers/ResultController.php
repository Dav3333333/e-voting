<?php
namespace Dls\Evoting\controllers;

use Dls\Evoting\controllers\ControllersParent;
use Dls\Evoting\models\Poll;

use Exception;

use FPDF;

class ResultController extends ControllersParent
{
    public function getResultsByPollId(Poll $poll): array
    {
        $stmt = $this->database->prepare("
            SELECT 
                p.id AS post_id,
                p.post_name, 
                c.id AS candidate_id,
                u.id AS user_id,
                u.name AS candidate_name,
                u.image_name,
                u.has_image,
                COUNT(v.id) AS vote_count
            FROM post p
            LEFT JOIN candidate c ON p.id = c.post_id
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN voice v ON c.id = v.candidate_id AND v.poll_id = p.poll_id AND v.post_id = p.id
            WHERE p.poll_id = ? AND c.status != -1
            GROUP BY p.id, c.id
            ORDER BY p.id, vote_count DESC
        ");
        $stmt->execute([$poll->getId()]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $results = [];
        foreach ($rows as $row) {
            $postId = $row['post_id'];
            if (!isset($results[$postId])) {
                $results[$postId] = [
                    'post_id' => $postId,
                    'post_name' => $row['post_name'],
                    'candidates' => []
                ];
            }
            $results[$postId]['candidates'][] = [
                'candidate_id' => $row['candidate_id'],
                'user_id' => $row['user_id'],
                'candidate_name' => $row['candidate_name'],
                'vote_count' => $row['vote_count'],
                'image_name' => $row['image_name'],
                'has_image' => $row['has_image'],
            ];
        }
        // Re-index to have a simple array of posts
        return array_values($results);
    }

    public function getWinnersByPollId(Poll $poll): array
    {
        $stmt = $this->database->prepare("
            SELECT 
                p.post_name, 
                u.name AS candidate_name, 
                COUNT(v.id) AS vote_count
            FROM post p
            LEFT JOIN candidate c ON p.id = c.post_id
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN voice v ON c.id = v.candidate_id AND v.poll_id = p.poll_id AND v.post_id = p.id
            WHERE p.poll_id = ?
            GROUP BY p.id, c.id
            HAVING vote_count = (
                SELECT MAX(vote_sub_count) FROM (
                    SELECT COUNT(v_sub.id) AS vote_sub_count
                    FROM candidate c_sub
                    LEFT JOIN voice v_sub ON c_sub.id = v_sub.candidate_id AND v_sub.poll_id = p.poll_id AND v_sub.post_id = p.id
                    WHERE c_sub.post_id = p.id
                    GROUP BY c_sub.id
                ) AS subquery
            )
            ORDER BY p.id, candidate_name
        ");
        $stmt->execute([$poll->getId()]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getResulOfPostsForEachPoll(Poll $poll): array
    {
        $stmt = $this->database->prepare("
            SELECT 
                p.post_name, 
                u.name AS candidate_name, 
                COUNT(v.id) AS vote_count
            FROM post p
            LEFT JOIN candidate c ON p.id = c.post_id
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN voice v ON c.id = v.candidate_id AND v.poll_id = p.poll_id AND v.post_id = p.id
            WHERE p.poll_id = ?
            GROUP BY p.id, c.id
            ORDER BY p.id, vote_count DESC
        ");
        $stmt->execute([$poll->getId()]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getResultPollPdf(Poll $poll){
         // Nettoyer TOUS les buffers de sortie
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Démarrer un nouveau buffer
        ob_start();

        try {
            error_log("=== DÉBUT GÉNÉRATION PDF POUR SCRUTIN " . $poll->getId() . " ===");
            
            $pollResult = $this->getResultsByPollId($poll);
            
            if (empty($pollResult)) {
                throw new Exception('Aucun résultat pour ce scrutin');
            }

            // Vérifier que FPDF est disponible
            if (!class_exists('FPDF')) {
                throw new Exception('Classe FPDF non trouvée');
            }

            // Créer le PDF
            $pdf = new FPDF();
            $pdf->AddPage();
            
            // Titre
            $pdf->SetFont('Arial', 'B', 16);
            $title = "Résultats du scrutin: " . $poll->getTitle();
            $pdf->Cell(0, 10, $this->convertToPdfText($title), 0, 1, 'C');
            $pdf->Ln(6);

            // Paramètres de mise en page
            $pageWidth = $pdf->GetPageWidth();
            $pageHeight = $pdf->GetPageHeight();
            $leftMargin = 10;
            $rightMargin = 10;
            $bottomMargin = 10;
            $usableWidth = $pageWidth - $leftMargin - $rightMargin;
            $cols = 4;
            $colWidth = $usableWidth / $cols;
            $imgW = min(30, $colWidth - 10);
            $rowHeight = $imgW + 18; // image + text space

            foreach ($pollResult as $result) {
                $pdf->SetFont('Arial', 'B', 14);
                $line = "Post : ". $result['post_name'];
                $pdf->Cell(0, 8, $this->convertToPdfText($line), 0, 1);
                $pdf->Ln(2);

                $totalVoice = array_sum(array_column($result['candidates'], 'vote_count'));

                $idx = 0;
                $startY = $pdf->GetY();

                $lineHName = 6; // line height for name
                $lineHVote = 6; // line height for votes/percent block (can be two lines)
                $padding = 4;
                $blockHeight = $imgW + $lineHName + $lineHVote + $padding;

                foreach ($result['candidates'] as $cand) {
                    $col = $idx % $cols;
                    $x = $leftMargin + $col * $colWidth;

                    // Check page break based on the start of the row and block height
                    if ($startY + $blockHeight > $pageHeight - $bottomMargin) {
                        $pdf->AddPage();
                        $startY = $pdf->GetY();
                    }

                    // Image
                    $imagePath = __DIR__ . '/../../app/images/users/';
                    $imageFile = null;
                    if (!empty($cand['image_name'])) {
                        $candidateImage = $imagePath . basename($cand['image_name']);
                        if (file_exists($candidateImage)) $imageFile = $candidateImage;
                    }
                    if ($imageFile === null) {
                        $defaultImage = $imagePath . 'default-image.png';
                        if (file_exists($defaultImage)) $imageFile = $defaultImage;
                    }

                    $imgX = $x + ($colWidth - $imgW) / 2;
                    $imgY = $startY; // anchor all columns to same top
                    if ($imageFile) {
                        $pdf->Image($imageFile, $imgX, $imgY, $imgW, $imgW);
                    }

                    // Name under image (single-line, truncate if necessary)
                    $nameY = $imgY + $imgW + 2;
                    $pdf->SetXY($x, $nameY);
                    $pdf->SetFont('Arial', 'B', 10);
                    $nameText = $cand['candidate_name'] ?? '';
                    $nameText = $this->truncateTextToWidth($pdf, $nameText, $colWidth - 2);
                    $pdf->Cell($colWidth, $lineHName, $this->convertToPdfText($nameText), 0, 2, 'C');

                    // Votes and percentage under the name
                    $pdf->SetXY($x, $nameY + $lineHName);
                    $pdf->SetFont('Arial', '', 9);
                    $votesText = 'Voix: ' . ($cand['vote_count'] ?? 0);
                    $percent = ($totalVoice == 0) ? 0 : round((($cand['vote_count'] ?? 0) * 100) / $totalVoice, 2);
                    $percentText = 'Pct: ' . $percent . '%';
                    $pdf->MultiCell($colWidth, $lineHVote, $this->convertToPdfText($votesText . "\n" . $percentText), 0, 'C');

                    // Reset Y to start of row for next column
                    $pdf->SetY($startY);

                    $idx++;
                    // if end of row, advance Y by blockHeight
                    if ($idx % $cols == 0) {
                        $pdf->SetY($startY + $blockHeight + 4);
                        $startY = $pdf->GetY();
                    }
                }

                // If last row incomplete, move cursor down to leave space
                if ($idx % $cols != 0) {
                    $pdf->SetY($startY + $blockHeight + 6);
                } else {
                    $pdf->Ln(4);
                }

                // If last row incomplete, move cursor down
                if ($idx % $cols != 0) {
                    $pdf->Ln($rowHeight + 6);
                } else {
                    $pdf->Ln(4);
                }
            }

            // Nettoyer le buffer avant d'envoyer les headers
            ob_clean();

            // Headers PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="results_poll_' . $poll->getId() . '.pdf"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Envoyer le PDF
            $pdf->Output('I');
            
            error_log("=== PDF GÉNÉRÉ AVEC SUCCÈS ===");
            exit;
            
        } catch (Exception $e) {
            // Nettoyer tous les buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            error_log("=== ERREUR CRITIQUE PDF: " . $e->getMessage() . " ===");
            
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors de la génération du PDF',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    // pdf methode
    /**
     * Truncate text to fit within a given width in current PDF font
     */
    private function truncateTextToWidth(FPDF $pdf, string $text, float $maxWidth): string {
        $text = trim($text);
        if ($text === '') return $text;

        // If it already fits, return as is
        if ($pdf->GetStringWidth($this->convertToPdfText($text)) <= $maxWidth) return $text;

        $ellipsis = '...';
        $ellipsisWidth = $pdf->GetStringWidth($this->convertToPdfText($ellipsis));

        $len = mb_strlen($text);
        while ($len > 0) {
            $candidate = mb_substr($text, 0, $len);
            if ($pdf->GetStringWidth($this->convertToPdfText($candidate)) + $ellipsisWidth <= $maxWidth) {
                return $candidate . $ellipsis;
            }
            $len--;
        }

        return $ellipsis;
    }

    /**
     * Convertit le texte UTF-8 pour FPDF
     */
    private function convertToPdfText(string $text): string {
        return iconv('UTF-8', 'windows-1252', $text);
    } 
}


