<? 
namespace Dls\Evoting\controllers; 

require_once(__DIR__ . '/../../vendor/autoload.php');

use Dls\Evoting\controllers\ControllersParent;

use Dls\Evoting\models\Candidate;
use Dls\Evoting\models\Poll;
use Dls\Evoting\models\Post; 
use Dls\Evoting\models\User;


class VoteController extends ControllersParent{

    public function vote(Poll $poll, Post $post, Candidate $candidate, User $user):bool{
        try {

            return true;
            
        } catch (\Throwable $th) {
            return false;
        }
    }

}