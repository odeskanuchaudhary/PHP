<?php
namespace App\Http\Controllers\Front;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Auth\Guard;
use App\Models\Member;
use App\Models\Company;
use App\Models\Portal_Stats;
use Validator;
use Session;
use DB;
use Mail;
use PDO;
use Response;
use Input;
use Redirect;
use App\Http\Controllers\Controller;

class MemberController extends Controller
{
    public $auth;
    public function __construct(Guard $auth, Member $member)
    {
        parent::__construct();
        //$this->auth=$auth;
        //$this->member=$member;
    }
   
   public function register(Request $request)
    {
        $get_url   = $this->get_url();
        $member_id = mt_rand(100000000, 999999999);
        return view('front.member.register', compact('result', 'get_url', 'member_id', 'comp_id'))->with('message', 'The username or password you have entered is invalid.');
    } 
   
    public function add_relationships(Request $request)
    {
        $get_url   = $this->get_url();
        $member_id = mt_rand(100000000, 999999999);
        return view('front.member.add_relationships', compact('result', 'get_url', 'member_id', 'comp_id'))->with('message', 'The username or password you have entered is invalid.');
    }
	
   
    public function profile($id)
    {
        $front_login = Session::get('front_login');
        $username    = Session::get('username');
        if (!empty($front_login) || !empty($username)) {
		 $get_url = $this->get_url();
			$conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$stmt = $conn->prepare("SELECT * FROM Members WHERE ID=".$id); 
			$stmt->execute(); 
			$row = $stmt->fetch();
			if(!empty($_POST)){
			 $stmt = "UPDATE Members SET Name = '$_POST[Name]',Email = '$_POST[Email]',Company = '$_POST[Company]',Phone = '$_POST[Phone]' ,Password = '$_POST[Password]' WHERE ID ='$id'";
			  $result = $conn->query($stmt);
			  return redirect('profile/'.$row['ID'])->with('message', 'successfully Update Tracking Info')->withInput();
			}
			 return view('front.member.profile', compact('row', 'get_url', 'UpdateDetails', 'company'));
        } else {
              return redirect('http://portal.ensable.com');
        }
    }
	
	public function trackinglist()
	{
		$front_login = Session::get('front_login');
		 
		 if (!empty($front_login)) 
		 {
		$member_id = Session::get('member_id');
		$per_page_html = '';
		$get_url = $this->get_url();
		$keyword ='';
		$i =1;
		$conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
		$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		
		if(!empty($_GET['keyword'])){
		 $keyword = $_GET['keyword'];
		 $stmt = $conn->prepare("SELECT * FROM Tracking WHERE (Website like '%".$keyword."%') OR (Company like '%".$keyword."%') OR (Email like '%".$keyword."%')");
		 $stmt->execute();
		}
		else{
		
		//pagignation...
		/*$search_keyword = '';
		$sql =$conn->prepare("select * from Tracking");
		$sql->execute();
		$count = $sql->rowCount();
		$page = 1;
		$start=1;
		if(!empty($_POST["page"])) 
		{
		   $page = $_POST["page"];
		   $start =($page-1) * 10;
		}
		$limit=" limit " . $start . " , 10";
		
		$sql->bindValue(':keyword', '%' . $search_keyword . '%', PDO::PARAM_STR);
		$sql->execute();
		$row_count = $sql->rowCount();
		if(!empty($row_count)){
		$per_page_html .= "<div style='text-align:center;margin:20px 0px;'>";
		$page_count=ceil($row_count/10);
		if($page_count>1) {
			for($i=1;$i<=$page_count;$i++){
				if($i==$page){
					$per_page_html .= '<input type="submit" name="page" value="' . $i . '" class="btn-page current" />';
				} else {
					$per_page_html .= '<input type="submit" name="page" value="' . $i . '" class="btn-page" />';
				}
			}
		}
		$per_page_html .= "</div>";
		}
		$query = $sql.$limit;
		$pdo_statement = $conn->prepare($query);
		$pdo_statement->bindValue(':keyword', '%' . $search_keyword . '%', PDO::PARAM_STR);
		$pdo_statement->execute();
		$result = $pdo_statement->fetchAll();
		*/
		//pagignation...
		
	
		$stmt = $conn->prepare("select * from Tracking WHERE PartnerID = ".$member_id);
		$stmt->execute();
		}
		return view('front.member.trackinglist',compact('result','i','keyword','get_url','stmt','per_page_html'));
		} else {
            return redirect('http://portal.ensable.com');
        }
	}
	
	 public function addtracking(Request $request)
		 {
		 $front_login = Session::get('front_login');
		 if (!empty($front_login)) {
		 
		 $get_url   = $this->get_url();
		// $PartnerID = mt_rand(100000000, 999999999);
		 $PartnerID = Session::get('member_id');
		if(!empty($_POST)){
				$conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$stmt = "INSERT INTO Tracking (PartnerID,Company,Contact,Title,Website,Email,Label) VALUES ('$PartnerID','$_POST[Company]','$_POST[Contact]','$_POST[Title]','$_POST[Title]','$_POST[Email]','$_POST[Label]')";
				$conn->exec($stmt);
				return redirect('trackinglist')->with('message', 'successfully Inserted Tracking Info.');
			}
		 return view('front.member.add_tracking', compact('result', 'get_url', 'PartnerID', 'comp_id'));
		 } else {
              return redirect('http://portal.ensable.com');
        }
    }
	
	 public function edittracking($id)
    {
        $front_login = Session::get('front_login');
       	if (!empty($front_login)) {
		 $get_url = $this->get_url();
			$conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$stmt = $conn->prepare("SELECT * FROM Tracking WHERE TrackingID=".$id); 
			$stmt->execute(); 
			$row = $stmt->fetch();
			if(!empty($_POST)){
			 $stmt = "UPDATE Tracking SET Company = '$_POST[Company]',Title = '$_POST[Title]',Website = '$_POST[Website]',PartnerID = '$_POST[PartnerID]',Email = '$_POST[Email]',Label = '$_POST[Label]' WHERE TrackingID ='$id'";
			  $result = $conn->query($stmt);
			  return redirect('trackinglist')->with('message', 'Successfully Update Tracking Info')->withInput();
			}
			 return view('front.member.edit_tracking', compact('row', 'get_url', 'UpdateDetails', 'company'));
        } else {
              return redirect('http://portal.ensable.com');
        }
    }
	
	public function deletetracking($id) 
	{
		$conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$stmt = "DELETE FROM Tracking WHERE TrackingID =".$id;
		$conn->exec($stmt);
		return redirect('trackinglist')->with('message', 'Successfully Delete Tracking')->withInput();
	}
	
}



