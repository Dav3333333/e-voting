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
                u.name AS candidate_name, 
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
                'candidate_name' => $row['candidate_name'],
                'vote_count' => $row['vote_count'], 
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
                throw new Exception(message: $pollResult['message']['post_name']);
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
            $title = "Resultat Pour le strutin: " . $poll->getTitle() ;
            $pdf->Cell(0, 10, $this->convertToPdfText($title), 0, 1, 'C');
            $pdf->Ln(10);
            
            // Contenu
            foreach ($pollResult as $key => $result) {
                $pdf->SetFont('Arial', 'B', 15);
                $line = "Post : ". $result['post_name'];
                $pdf->Cell(0, 8, $this->convertToPdfText($line), 0, 1);
                
                $pdf->SetFont("Arial", 'B', 12);
                
                // creation of result table
                $pdf->Cell(60, 10, "Noms candidat", 1, 0, "C"); 
                $pdf->Cell(40, 10, "Voix obtenu", 1, 0, "C");
                $pdf->Cell(40, 10, 'Voix Totale',1, 0, "C");
                $pdf->Cell(40, 10, "Pourcentage", 1, 0, "C"); 
                $pdf->Ln();

                $totalVoice = array_sum(array_column($result['candidates'], 'vote_count'));

                foreach ($result['candidates'] as $key => $candData) {
                    $pdf->SetFont('Arial', '', 11);
                    $pdf->Cell(60, 10, $this->convertToPdfText($candData['candidate_name']), 1, 0, 'C');
                    $pdf->Cell(40, 10, $this->convertToPdfText($candData['vote_count']), 1, 0, 'C');
                    $pdf->Cell(40, 10, $this->convertToPdfText($totalVoice), 1, 0, 'C');
                    $pdf->Cell(40, 10, $this->convertToPdfText(($totalVoice == 0)?0:$candData['vote_count'] *100/$totalVoice), 1, 0, 'C');
                    $pdf->Ln();
                }
            }

            // Nettoyer le buffer avant d'envoyer les headers
            ob_clean();

            // Headers PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="cards_poll_' . $poll->getId() . '.pdf"');
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
     * Convertit le texte UTF-8 pour FPDF
     */
    private function convertToPdfText(string $text): string {
        return iconv('UTF-8', 'windows-1252', $text);
    } 
}


