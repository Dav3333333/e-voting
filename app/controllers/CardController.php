<?php 
namespace Dls\Evoting\controllers;

use ArrayObject; 

require_once(__DIR__ . '/../../vendor/autoload.php');


use Dls\Evoting\controllers\ControllersParent; 
use Dls\Evoting\controllers\PostController;

use Dls\Evoting\models\Card;
use Dls\Evoting\models\Poll;
use Dls\Evoting\models\Post;
use Dls\Evoting\models\User;


use PDO;
use FPDF;
use Exception;

class CardController extends ControllersParent
{

    public function getAll():array{
        $cards = [];

        $q = $this->database->query("SELECT * FROM card");
        $q->execute();

        while($c = $q->fetch(PDO::FETCH_ASSOC)){
            $cards[] = new Card(
                id:$c["id"], 
                poll_id:$c["poll_id"], 
                code_card:$c["card_code"],
                used:$c["used"], 
                linkable:$c['linkableToUser'], 
                linkedUser:$c['linkedUser']
            );
        }
        return $cards;
    }

    public function getCardByCode(string $code_card):Card|null{
        foreach($this->getAll() as $key => $value){
            if($value->get_code_card() == $code_card && $value instanceof Card){
                return $value;
            }
        }
        return null;
    }

    public function getCardOfPoll(Poll $poll):array{
        $cards = [];
        if($poll->getIsCard_user_link_mode()){
            foreach ($this->getAll() as $key => $value) {
                if($value->get_poll_id() == $poll->getId() && $value->isLinkable()){
                    $cards[] = $value;
                }
            }
            return $cards;
        }

        foreach ($this->getAll() as $key => $value) {
            if($value->get_poll_id() == $poll->getId()){
                $cards[] = $value;
            }
        }
        return $cards;
    }

    public function deleteCardOfPoll(Poll $poll):bool{
        $q = $this->database->prepare("DELETE FROM card WHERE poll_id = ?"); 
        return $q->execute(array($poll->getId()));
    }

    // public function generateCardForPoll(Poll $poll, int $n):bool{
    //     try {
    //         $this->deleteCardOfPoll($poll);
    //         $linkable = $poll->getMode() === "user-link-cardmode" ? 1 : 0;

    //         $a = 1;
    //         while($a <= $n){
    //             $code = substr("".(time() + $a * $poll->getId() * $n), -5);
    //             if(!$this->isCardExisting($code)){
    //                 $q = $this->database->prepare(
    //                     "INSERT INTO card (poll_id, card_code, used, linkableToUser, linkedUser) VALUES (?, ?, ?, ?, ?)"
    //                 );
    //                 $q->execute([
    //                     $poll->getId(),
    //                     $code,
    //                     0,
    //                     $linkable,
    //                     0
    //                 ]);
    //             } else {
    //                 $n++;
    //             }
    //             $a++;
    //         }
    //         return true;
    //     } catch (Exception $e) {
    //         error_log("=== ERREUR GENERATE CARD POLL: " . $e->getMessage() . " ===");
    //         return false;
    //     }
    // }

    public function generateCardForPoll(Poll $poll, int $n):bool{
        try {
            // SUPPRIMÉ : $this->deleteCardOfPoll($poll); 
            // On ne supprime plus les cartes existantes, on AJOUTE simplement les manquantes.

            // Sécurité pour le mode linkable (s'adapte à getIsCard_user_link_mode() ou au texte brut)
            $linkable = ($poll->getIsCard_user_link_mode() || $poll->getMode() === "user-link-cardmode") ? 1 : 0;

            $a = 1;
            $generated = 0;
            
            // On boucle jusqu'à ce qu'on ait réellement créé le nombre $n de cartes uniques requis
            while($generated < $n){
                $code = substr("".(time() + $a * $poll->getId() * $n), -5);
                if(!$this->isCardExisting($code)){
                    $q = $this->database->prepare(
                        "INSERT INTO card (poll_id, card_code, used, linkableToUser, linkedUser) VALUES (?, ?, ?, ?, ?)"
                    );
                    $q->execute([
                        $poll->getId(),
                        $code,
                        0,
                        $linkable,
                        0
                    ]);
                    $generated++;
                }
                $a++;
                
                // Éviter une boucle infinie en cas de problème de génération de timestamp
                if ($a > ($n * 10)) {
                    break;
                }
            }
            return true;
        } catch (Exception $e) {
            error_log("=== ERREUR GENERATE CARD POLL: " . $e->getMessage() . " ===");
            return false;
        }
    }


    // public function linkUserToCard(User $user, Poll $poll):Card|null|string{
    //     try {
    //         //check 
    //         $check = $this->database->prepare("SELECT * FROM card WHERE poll_id = ? AND linkedUser = ? LIMIT 1");
    //         $check->execute([$poll->getId(), $user->getId()]);

    //         if ($check->rowCount() == 1) {
    //             $cardData = $check->fetch(PDO::FETCH_ASSOC);
    //             return $this->getCardByCode($cardData['card_code']);
    //         }

    //         // getting the nextAvailble Card
    //         $q =  $this->database->prepare("SELECT * FROM `card` c 
    //                                     WHERE c.poll_id = ? 
    //                                     AND c.linkableToUser = 1
    //                                     AND (c.linkedUser IS NULL OR c.linkedUser = '')
    //                                     AND c.card_code NOT IN (SELECT v.card_code FROM voice AS v)
    //                                     LIMIT 1");
    //         $q->execute(array($poll->getId()));
        
            
    //         $cardData = $q->fetch(PDO::FETCH_ASSOC); 

    //         if(!$cardData){
    //             return $cardData;
    //         }

    //         $insQ = $this->database->prepare("UPDATE card SET linkedUser = ? WHERE id = ?");
    //         $insQ->execute(array($user->getId(), $cardData['id']));
    //         return $this->getCardByCode($cardData['card_code']);
    //     } catch (\Throwable $th) {
    //         return $th->getMessage();
    //     }
    // }

    public function linkUserToCard(User $user, Poll $poll):Card|null|string{
        try {
            // 1. Vérifier si cet utilisateur a déjà une carte assignée pour ce scrutin
            $check = $this->database->prepare("SELECT * FROM card WHERE poll_id = ? AND linkedUser = ? LIMIT 1");
            $check->execute([$poll->getId(), $user->getId()]);

            if ($check->rowCount() == 1) {
                $cardData = $check->fetch(PDO::FETCH_ASSOC);
                return $this->getCardByCode($cardData['card_code']);
            }

            // 2. Récupérer la prochaine carte disponible
            // CORRECTION : On accepte l'attribution même si linkableToUser est à 0 ou 1 lors d'un import CSV direct,
            // ou alors on s'assure qu'on cherche les cartes de ce pool non occupées.
            $q = $this->database->prepare("SELECT * FROM `card` c 
                                        WHERE c.poll_id = ? 
                                        AND (c.linkedUser IS NULL OR c.linkedUser = '' OR c.linkedUser = 0)
                                        AND c.card_code NOT IN (SELECT v.card_code FROM voice AS v)
                                        LIMIT 1");
            $q->execute(array($poll->getId()));
        
            $cardData = $q->fetch(PDO::FETCH_ASSOC); 

            if(!$cardData){
                return null; // Plus de cartes disponibles dans le pool
            }

            // 3. Assigner la carte à l'utilisateur
            $insQ = $this->database->prepare("UPDATE card SET linkedUser = ?, linkableToUser = 1 WHERE id = ?");
            $insQ->execute(array($user->getId(), $cardData['id']));
            
            return $this->getCardByCode($cardData['card_code']);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    private function truncateText(string $text, int $length): string{
        return (strlen($text) > $length) ? substr($text,0, $length).'...' : $text;
    }

    public function linkUsersFileToCards(){

    }

    public function generatePDFFileCardForPoll(Poll $poll):string{
        $cards = $this->getCardOfPoll($poll);
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,10, utf8_decode("Cartes de vote pour le scrutin: " . $poll->getTitle()),0,1,'C');
        $pdf->SetFont('Arial','',12);
        foreach ($cards as $key => $value) {
            $pdf->Cell(0,10, utf8_decode("Code de la carte n°" . ($key+1) . ": " . $value->get_code_card()),0,1);
        }
        $filePath = __DIR__ . '/../../public/pdf/cards_poll_' . $poll->getTitle() . '.pdf';
        $pdf->Output('F', $filePath);
        return $filePath;
    }

    public function generatePdfTempFileCardForPoll(Poll $poll): void {
        // Nettoyer TOUS les buffers de sortie
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Démarrer un nouveau buffer
        ob_start();

        try {
            error_log("=== DÉBUT GÉNÉRATION PDF POUR SCRUTIN " . $poll->getId() . " ===");

            // Vérifier que FPDF est disponible
            if (!class_exists('FPDF')) {
                throw new Exception('Classe FPDF non trouvée');
            }

            $rows = [];

            // Si le mode est NULL, on le met en cardmode par défaut
            if (!$poll->hasMode()) {
                // Mettre à jour en DB
                $pc = new PollController();
                $pc->setPollMode($poll, 'cardmode');
                // IMPORTANT: Mettre à jour l'objet Poll en mémoire pour refléter le changement
                $poll->setMode('cardmode');
                error_log("Mode de scrutin absent, passage en cardmode par défaut pour poll " . $poll->getId());
            }

            // Si le scrutin est en mode carte et qu'il n'y a encore aucune carte, on en génère quelques-unes automatiquement
            if (!$this->hasCard($poll)) {
                $defaultCardCount = 10;
                error_log("=== Génération automatique de $defaultCardCount cartes pour le scrutin " . $poll->getId() . " ===");
                $this->generateCardForPoll($poll, $defaultCardCount);
            }

            // Si on est en user-link-cardmode SEULEMENT (pas en cardmode simple)
            if ($poll->getIsCard_user_link_mode()) {
                error_log("=== Mode user-link-cardmode détecté, récupération des enrolements ===");
                $q = $this->database->prepare("SELECT u.matricule AS matricule, u.name AS name, u.email AS email, e.card_code AS card_code
                                              FROM enrolements e
                                              JOIN users u ON u.id = e.id_user
                                              WHERE e.id_poll = ?
                                              ORDER BY u.email ASC");
                $q->execute([$poll->getId()]);
                $rows = $q->fetchAll(PDO::FETCH_ASSOC);
                error_log("=== Enrolements trouvés: " . count($rows) . " ===");

                // if no enrolements, fallback to cards linked to users (if any)
                if (empty($rows)) {
                    error_log("=== Pas d'enrolements trouvés, recherche des cartes liées aux utilisateurs ===");
                    $q2 = $this->database->prepare("SELECT u.matricule AS matricule, u.name AS name, u.email AS email, c.card_code AS card_code
                                                   FROM card c
                                                   JOIN users u ON u.id = c.linkedUser
                                                   WHERE c.poll_id = ? AND c.linkableToUser = 1 AND c.linkedUser IS NOT NULL AND c.linkedUser != ''
                                                   ORDER BY u.email ASC");
                    $q2->execute([$poll->getId()]);
                    $rows = $q2->fetchAll(PDO::FETCH_ASSOC);
                    error_log("=== Cartes liées trouvées: " . count($rows) . " ===");
                }
            }

            // Si on a des rows user-card, on produit le PDF suivant la structure demandée
            if (!empty($rows)) {
                $pdf = new FPDF();
                $pdf->AddPage();

                // Titre
                $pdf->SetFont('Arial', 'B', 16);
                $title = "Cartes liées aux utilisateurs - Scrutin: " . $poll->getTitle();
                $pdf->Cell(0, 10, $this->convertToPdfText($title), 0, 1, 'C');
                $pdf->Ln(6);

                // En-tête de tableau
                $pdf->SetFont('Arial', 'B', 11);
                $pdf->Cell(35, 8, $this->convertToPdfText('Matricule'), 1, 0, 'L');
                $pdf->Cell(60, 8, $this->convertToPdfText('Nom'), 1, 0, 'L');
                $pdf->Cell(60, 8, $this->convertToPdfText('Email'), 1, 0, 'L');
                $pdf->Cell(30, 8, $this->convertToPdfText('Code'), 1, 1, 'L');

                // Contenu
                $pdf->SetFont('Arial', '', 10);
                foreach ($rows as $r) {
                    $pdf->Cell(35, 7, $this->convertToPdfText($r['matricule'] ?? ''), 1, 0, 'L');
                    $pdf->Cell(60, 7, $this->convertToPdfText($this->truncateText($r['name'], 27) ?? ''), 1, 0, 'L');
                    $pdf->Cell(60, 7, $this->convertToPdfText($this->truncateText($r['email'], 28) ?? ''), 1, 0, 'L');
                    $pdf->Cell(30, 7, $this->convertToPdfText($r['card_code'] ?? ''), 1, 1, 'L');
                }

                // Nettoyer le buffer avant d'envoyer les headers
                ob_clean();

                // Headers PDF
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="cards_poll_' . $poll->getTitle() . '_users.pdf"');
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');

                $pdf->Output('I');
                exit;
            }

            // Sinon, fallback au listing simple des cartes comme précédemment
            $cards = $this->getCardOfPoll($poll);
            if (empty($cards)) {
                throw new Exception('Aucune carte trouvée pour ce scrutin');
            }

            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial','B',16);
            $pdf->Cell(0,10, $this->convertToPdfText("Cartes de vote pour le scrutin: " . $poll->getTitle()),0,1,'C');
            $pdf->SetFont('Arial','',12);
            foreach ($cards as $key => $value) {
                $pdf->Cell(0,10, $this->convertToPdfText("Code de la carte n°" . ($key+1) . ": " . $value->get_code_card()),0,1);
            }

            // Nettoyer le buffer avant d'envoyer les headers
            ob_clean();

            // Headers PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="cards_poll --'.$poll->getTitle().'.pdf"');
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

    /**
     * Convertit le texte UTF-8 pour FPDF
     */
    private function convertToPdfText(string $text): string {
        return iconv('UTF-8', 'windows-1252', $text);
    }


    public function countCardOfPoll(Poll $poll):int{
        $count = 0;
        foreach ($this->getAll() as $key => $value) {
            if($value->get_poll_id() == $poll->getId()){
                $count++;
            }
        }
        return $count;
    }

    public function hasCard(Poll $poll):bool{
        return $this->countCardOfPoll($poll) > 0;
    }

    public function cardExists(string $code_card):bool{
        foreach ($this->getAll() as $key => $value) {
            if($value->get_code_card() == $code_card){
                return true;
            }
        }
        return false;
    }

    public function markCardAsUsed(Card $card):bool{
        $q = $this->database->prepare("UPDATE card SET used = 1 WHERE id = ?");
        return $q->execute(array($card->get_id()));
    }

    // public function isCardExistingAndUnused(string $code_card):bool{
    //     foreach ($this->getAll() as $key => $value) {
    //         if($value->get_code_card() == $code_card && $value->isUsed() == false){
    //             return true;
    //         }
    //     }
    //     return false;
    // }

    // public function isCardExisting(string $code_card):bool{
    //     foreach ($this->getAll() as $key => $value) {
    //         if($value->get_code_card() == $code_card){
    //             return true;
    //         }
    //     }
    //     return false;
    // }

    // public function isCardOfPoll(Poll $poll, Card $card):bool{
    //     foreach ($this->getCardOfPoll($poll) as $key => $value) {
    //         if($value->get_code_card() == $card->get_code_card() && $value->get_poll_id() == $poll->getId()){
    //             return true;
    //         }
    //     }
    //     return false;
    // }

    // public function isValidCardForPoll(Poll $poll, Card $code_card):bool{
    //     return $this->isCardOfPoll($poll, $code_card) 
    //             && $this->isCardExistingAndUnused($code_card->get_code_card())
    //             && ($poll->getMode() ==  $code_card->getMode());
    // }

    public function isCardExistingAndUnused(string $code_card):bool{
        foreach ($this->getAll() as $key => $value) {
            if($value->get_code_card() == $code_card && $value->isUsed() == false){
                return true;
            }
        }
        return false;
    }

    public function isCardExisting(string $code_card):bool{
        foreach ($this->getAll() as $key => $value) {
            if($value->get_code_card() == $code_card){
                return true;
            }
        }
        return false;
    }

    public function isCardOfPoll(Poll $poll, Card $card):bool{
        foreach ($this->getCardOfPoll($poll) as $key => $value) {
            if($value->get_code_card() == $card->get_code_card() && $value->get_poll_id() == $poll->getId()){
                return true;
            }
        }
        return false;
    }

    public function isValidCardForPoll(Poll $poll, Card $code_card):bool{
        // 1. Vérifications de base (Scrutin correspondant et carte non utilisée)
        if (!$this->isCardOfPoll($poll, $code_card) || !$this->isCardExistingAndUnused($code_card->get_code_card())) {
            return false;
        }

        // 2. Normalisation et vérification croisée du mode
        $pollMode = $poll->getMode(); // "user-link-cardmode" ou "cardmode"
        
        if ($pollMode === "user-link-cardmode") {
            // Pour ce mode, la carte doit obligatoirement être "linkable"
            return $code_card->isLinkable();
        }

        if ($pollMode === "cardmode") {
            // Pour le mode simple, la carte ne doit pas imposer de liaison utilisateur
            return !$code_card->isLinkable();
        }

        return false;
    }

}
