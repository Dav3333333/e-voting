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

    public function generateCardForPoll(Poll $poll, int $n):bool{
        try {
            $this->deleteCardOfPoll($poll);
            // $name = $poll->getId() . "-" . $poll->getTitle();
            // for ($i=0; $i < $n; $i++) { 
            //     // $code = str_split(md5($name ."".(time()+$i)), 10)[0];
            //     $code = substr("".(time()+$i*$poll->getId()*$n), -5);
            //     $q = $this->database->prepare("INSERT INTO card (poll_id, card_code, used) VALUES(?,?,?)");
                
            //     $q->execute(array($poll->getId(), $code, false));
            // }
            $a = 1;
            while($a <= $n){
                // $code = str_split(md5($name ."".(time()+$i)), 10)[0];
                $code = substr("".(time()+$a*$poll->getId()*$n), -5);
                if(!$this->isCardExisting($code)){
                    $q = $this->database->prepare("INSERT INTO card (poll_id, card_code, used) VALUES(?,?,?)");
                    
                    $q->execute(array($poll->getId(), $code, false));
                }else{
                    $n++;
                }
                $a++;
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function linkUserToCard(User $user, Poll $poll):Card|null{
        try {
            //code...
            // getting the nextAvailble Card
            $q =  $this->database->prepare("SELECT * FROM `card` c WHERE c.card_code NOT IN (SELECT v.card_code FROM voice AS v) AND c.poll_id = ? LIMIT 1");
            $q->execute(array($poll->getId()));
            
            $cardData = $q->fetch(PDO::FETCH_ASSOC); 
            $c_data = count($cardData);
    
            if($c_data === 1){
                $insQ = $this->database->prepare("UPDATE `card` SET linkableToUser = 1,  linkedUser = ? WHERE id = ?");
                if($insQ->execute(array($user->getId(), $cardData['id']))){
                    return $this->getCardByCode($cardData['card_code']);
                } 
                return null;
            }
            return null;
        } catch (\Throwable $th) {
            throw new Exception("Linking user error", code:401);
        }

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
        $filePath = __DIR__ . '/../../public/pdf/cards_poll_' . $poll->getId() . '.pdf';
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
            
            $cards = $this->getCardOfPoll($poll);
            error_log("Nombre de cartes: " . count($cards));
            
            if (empty($cards)) {
                throw new Exception('Aucune carte trouvée pour ce scrutin');
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
            $title = "Cartes de vote pour: " . $poll->getTitle();
            $pdf->Cell(0, 10, $this->convertToPdfText($title), 0, 1, 'C');
            $pdf->Ln(10);
            
            // Contenu
            $pdf->SetFont('Arial', '', 12);
            foreach ($cards as $key => $card) {
                $line = "Carte " . ($key + 1) . " : " . $card->get_code_card();
                $pdf->Cell(0, 8, $this->convertToPdfText($line), 0, 1);
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
        return $this->isCardOfPoll($poll, $code_card) && $this->isCardExistingAndUnused($code_card->get_code_card());
    }

}
