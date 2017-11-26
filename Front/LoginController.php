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

class LoginController extends Controller
{
    public $auth;
    public function __construct(Guard $auth, Member $member)
    {
        parent::__construct();
        //$this->auth=$auth;
        //$this->member=$member;
    }
   // public function index()
   // {
     // Index
 //   }
	
    public function index(Request $request)
    {
        if ($_POST != NULL) {
            $Email    = $_REQUEST['email'];
            $Password = $_REQUEST['password'];
            $form_id  = $request->form_id;
            $conn     = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare("SELECT * FROM Members WHERE Email = '" . $Email . "' AND Password ='" . $Password . "'");
            $stmt->execute();
            $row = $stmt->fetch();
            if (!empty($row['ID'])) {
                Session::put('front_login', $row['Name']);
                Session::put('Email', $row['Email']);
                Session::put('member_id', $row['ID']);
                return redirect('index.php/trackinglist')->with('message', 'Welocme to Member Portal.');
            } else {
                return redirect('')->with('message', 'The username or password you entered is incorrect')->withInput();
            }
            
            return redirect('')->with('message', 'The username and password is required.')->withInput();
        }
        return view('front.login.login');
    }
    
    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect('http://portal.ensable.com');
		// return view('front.login.login');
    }
    
    public function change_password()
    {
	$username =  Session::get('front_login'); 
	if(!empty($username)){
	if(!empty($_POST)){
	 if (!empty($_POST['currpassword']))
	  {
	 	if(!empty($_POST['password'] ) && !empty($_POST['conf_password']))
		{
		if($_POST['password'] == $_POST['conf_password'])
		 {
		  $Password = $_REQUEST['currpassword'];
			$id= Session::get('member_id');
			$conn     = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare("SELECT * FROM Members WHERE Password ='" . $Password . "'");
			 $stmt = $conn->prepare("SELECT * FROM Members WHERE ID = '" . $id . "' AND Password ='" . $Password . "'");
             $stmt->execute();
             $row = $stmt->fetch();
			if(!empty($row['ID']))
				{
				$stmt = "UPDATE Members SET Password = '$_POST[password]' WHERE ID ='$id'";
				$result = $conn->query($stmt);
				 return redirect('change_password')->with('message', 'Update successfully password.')->withInput();
				}
			}
			return redirect('change_password')->with('message', 'New password conform password not match.')->withInput();
		}
		 return redirect('change_password')->with('message', 'Please Enter New password and Conform password.')->withInput();
     }
		return redirect('change_password')->with('message', 'Current password New password and Conform password required.')->withInput();
    }
	return view('front.login.change_password', compact('users_details'));
	} 
	else {   return redirect('http://portal.ensable.com'); }
	}

}



