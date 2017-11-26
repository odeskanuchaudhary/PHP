<?php
namespace App\Http\Controllers\Admin;
use App\Models\Member;
use App\Models\Company;
use App\Models\Portal_Sales;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Auth\Guard;
use Validator;
use App\Http\Controllers\Controller;
use DB;
use Mail;
use File;
use Input;
use Session;
use PDO;
use Intervention\Image\Facades\Image;
class Member_Controller extends  Controller{
/** 
*@varGuard
*/
	public $auth;
	public function __construct(Guard $auth,Member $member) {
		parent::__construct();
		//$this->auth=$auth;
		//$this->middleware('auth');
		$this->member=$member;
		$this->per_page =15;
		}

public function admin_login(Request $request)
 {
if(!empty($request->username))
{
	if($request->username == 'Ensable' && $request->password == 'Ensable7')
	{
		Session::put('username', $request->username);
		Session::put('password', $request->password);
		return redirect('admin/member_users');
	}
	else
	{
	return redirect('admin/login')->with('message', 'The username or password you entered is incorrect.');
	 }
	}
	else {
	return redirect('admin/login')->with('message', 'The username and password is required.');
	}
return redirect('admin/login');		
}
	public function index() {
		$username =  Session::get('username'); 
		if(!empty($username)){
		$get_url = $this->get_url();
		$keyword ='';
		$i =1;
		$conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$stmt = $conn->prepare("select * from Members");
		$stmt->execute();
		return view('admin.member_users.index',compact('result','i','keyword','get_url','stmt'));
		}
		else{
		return redirect('admin/login');
		}
	}
	
	public function add_member() {
		$username =  Session::get('username'); 
		if(!empty($username)){
		
		 $member_id = mt_rand(100000000,999999999);
		 $conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
		 $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		 if(!empty($_POST)){
		 
		  $stmt = "INSERT INTO Members (ID,Name,Email,Company,Phone,Active,Leads,Sales,Comissions,MRC,JoinDate,UpsellReports,LinkedInLists,TenantLists,Credits,LinkedInID,FiberAddresses,Password,LeadTypes,OfficeMoves,NewExecutives,VentureCapital,Acquisitions,NaturalDisasters,EmploymentGrowth,FiberConstruction,DesktopSupportJobs,GeneralITJobs,ITSecurityJobs,ProgrammingJobs,NewBuildingConstruction,CompanyNews) VALUES ('$_POST[ID]','$_POST[Name]','$_POST[Email]','$_POST[Company]','$_POST[Phone]','$_POST[Active]','$_POST[Leads]','$_POST[Sales]','$_POST[Comissions]','$_POST[MRC]','$_POST[JoinDate]','$_POST[UpsellReports]','$_POST[LinkedInLists]','$_POST[TenantLists]','$_POST[Credits]','$_POST[LinkedInID]','$_POST[FiberAddresses]','$_POST[Password]','$_POST[LeadTypes]','$_POST[OfficeMoves]','$_POST[NewExecutives]','$_POST[VentureCapital]','$_POST[Acquisitions]','$_POST[NaturalDisasters]','$_POST[EmploymentGrowth]','$_POST[FiberConstruction]','$_POST[DesktopSupportJobs]','$_POST[GeneralITJobs]','$_POST[ITSecurityJobs]','$_POST[ProgrammingJobs]','$_POST[NewBuildingConstruction]','$_POST[CompanyNews]')";
			$conn->exec($stmt);
			return redirect('admin/member_users')->with('message', 'Member Record successfully added.');
			}
		 $get_url=$this->get_url();
		return view('admin.member_users.add_member', compact('get_url','result','member_id'));
		}
		else{
		return redirect('admin/login');
		}
	}
	
	public function edit($id) {
		$username =  Session::get('username'); 
		if(!empty($username)){
		$get_url=$this->get_url();
			$conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$stmt = $conn->prepare("SELECT * FROM Members WHERE ID=".$id); 
			$stmt->execute(); 
			$row = $stmt->fetch();
			if(!empty($_POST['Name'])){
			if(!empty($row)){
			$Active = $_POST['Active_'];
			$OfficeMoves    = !empty($_POST['OfficeMoves']) ? $_POST['OfficeMoves'] : '';
			$NewExecutives    = !empty($_POST['NewExecutives']) ? $_POST['NewExecutives'] : '';
			$VentureCapital    = !empty($_POST['VentureCapital']) ? $_POST['VentureCapital'] : '';
			$Acquisitions    = !empty($_POST['Acquisitions']) ? $_POST['Acquisitions'] : '';
			$NaturalDisasters    = !empty($_POST['NaturalDisasters']) ? $_POST['NaturalDisasters'] : '';
			$EmploymentGrowth    = !empty($_POST['EmploymentGrowth']) ? $_POST['EmploymentGrowth'] : '';
			$FiberConstruction    = !empty($_POST['FiberConstruction']) ? $_POST['FiberConstruction'] : '';
			$DesktopSupportJobs    = !empty($_POST['DesktopSupportJobs']) ? $_POST['DesktopSupportJobs'] : '';
			$GeneralITJobs    = !empty($_POST['GeneralITJobs']) ? $_POST['GeneralITJobs'] : '';
			$ITSecurityJobs    = !empty($_POST['ITSecurityJobs']) ? $_POST['ITSecurityJobs'] : '';
			$ProgrammingJobs    = !empty($_POST['ProgrammingJobs']) ? $_POST['ProgrammingJobs'] : '';
			$NewBuildingConstruction    = !empty($_POST['NewBuildingConstruction']) ? $_POST['NewBuildingConstruction'] : '';
			$CompanyNews    = !empty($_POST['CompanyNews']) ? $_POST['CompanyNews'] : '';
		
    $stmt = "UPDATE Members SET Name = '$_POST[Name]',Email = '$_POST[Email]',Company = '$_POST[Company]',Phone = '$_POST[Phone]',Active = '$Active',Leads = '$_POST[Leads]',Sales = '$_POST[Sales]' ,Comissions = '$_POST[Comissions]',MRC = '$_POST[MRC]' ,JoinDate = '$_POST[JoinDate]',UpsellReports = '$_POST[UpsellReports]' ,LinkedInLists = '$_POST[LinkedInLists]',TenantLists = '$_POST[TenantLists]',Credits = '$_POST[Credits]',LinkedInID = '$_POST[LinkedInID]' ,FiberAddresses = '$_POST[FiberAddresses]' ,Password = '$_POST[Password]', Message = '$_POST[Message]' ,Employees = '$_POST[Employees]',Vertical = '$_POST[Vertical]' ,ZIP1 = '$_POST[ZIP1]', ZIP2 = '$_POST[ZIP2]', LeadTypes = '$_POST[LeadTypes]',OfficeMoves = '$OfficeMoves', NewExecutives = '$NewExecutives',VentureCapital = '$VentureCapital', Acquisitions = '$Acquisitions',NaturalDisasters = '$NaturalDisasters', EmploymentGrowth = '$EmploymentGrowth',FiberConstruction = '$FiberConstruction', DesktopSupportJobs = '$DesktopSupportJobs',GeneralITJobs = '$GeneralITJobs', ITSecurityJobs = '$ITSecurityJobs',ProgrammingJobs = '$ProgrammingJobs', NewBuildingConstruction = '$NewBuildingConstruction', CompanyNews = '$CompanyNews' WHERE ID ='$id'";
			$result = $conn->query($stmt);
			return redirect('admin/member_users')->with('message', 'Member Record successfully update.');
				}
				}
		return view('admin.member_users.edit', compact('get_url','result','row'));
		}
		else{
		return redirect('admin/login');
		}
	}
	
	public function member_tracking($id) {
		$PartnerID = $id;
		$username =  Session::get('username'); 
		if(!empty($username))
		{
			$conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$get_url=$this->get_url();
			if(!empty($_POST)){
				$stmt = "INSERT INTO Tracking (PartnerID,Company,Contact,Title,Website,Email,Label) VALUES ('$_POST[PartnerID]','$_POST[Company]','$_POST[Contact]','$_POST[Title]','$_POST[Website]','$_POST[Email]','$_POST[Label]')";
				$conn->exec($stmt);
				return redirect('admin/tracking_list')->with('message', 'successfully Inserted Tracking Info.');
			}
			$get_url=$this->get_url();
			return view('admin.member_users.member_tracking', compact('get_url','result','PartnerID','row'));
			}
		else{
		return redirect('admin/login');
		}
	}
	
   /*public function member_tracking($id) {
		$PartnerID = $id;
		$username =  Session::get('username'); 
		if(!empty($username))
		{
			$conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$stmt = $conn->prepare("SELECT * FROM Tracking WHERE PartnerID = ". $PartnerID); 
			$stmt->execute(); 
			$row = $stmt->fetch();
			$get_url=$this->get_url();
			if(!empty($_POST)){
				if(!empty($row)){
				$stmt = "UPDATE Tracking SET PartnerID = '$PartnerID',Company = '$_POST[Company]',Contact = '$_POST[Contact]',Title = '$_POST[Title]' WHERE PartnerID =$PartnerID";
				$result = $conn->query($stmt);
				 return redirect('admin/tracking_list')->with('message', 'successfully Update Tracking Info')->withInput();
				}
				
				else{
				$stmt = "INSERT INTO Tracking (PartnerID,Company,Contact,Title,Website) VALUES ('$PartnerID','$_POST[Company]','$_POST[Contact]','$_POST[Title]','$_POST[Title]')";
				$conn->exec($stmt);
				return redirect('admin/tracking_list')->with('message', 'successfully Inserted Tracking Info.');
				}
			}
			$get_url=$this->get_url();
			return view('admin.member_users.member_tracking', compact('get_url','result','PartnerID','row'));
			}
		else{
		return redirect('admin/login');
		}
	}*/
   
   public function member_trackinglist($id) 
   {
		$username =  Session::get('username'); 
		if(!empty($username)){
		$get_url = $this->get_url();
		$keyword ='';
		$i =1;
		$conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$stmt = $conn->prepare("select * from Tracking WHERE PartnerID = ".$id);
		$stmt->execute();
		return view('admin.member_users.membertrackinglist',compact('result','i','keyword','get_url','stmt','id'));
		}
		else{
		return redirect('admin/login');
   }
   
   }
   
   
   
   public function tracking_list()
	 {
		$username =  Session::get('username'); 
		if(!empty($username)){
		$get_url = $this->get_url();
		$keyword ='';
		$i =1;
		$conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$stmt = $conn->prepare("select * from Tracking");
		$stmt->execute();
		return view('admin.member_users.trackinglist',compact('result','i','keyword','get_url','stmt'));
		}
		else{
		return redirect('admin/login');
		}
	}



	public function addtracking() 
	{
		$username =  Session::get('username'); 
		$PartnerID = mt_rand(100000000,999999999);
		if(!empty($username))
		{
			$conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$get_url=$this->get_url();
			if(!empty($_POST)){
				$stmt = "INSERT INTO Tracking (PartnerID,Company,Contact,Title,Website,Email,Label) VALUES ('$PartnerID','$_POST[Company]','$_POST[Contact]','$_POST[Title]','$_POST[Website]','$_POST[Email]','$_POST[Label]')";
				$conn->exec($stmt);
				return redirect('admin/tracking_list')->with('message', 'successfully Inserted Tracking Info.');
			}
			$get_url=$this->get_url();
			return view('admin.member_users.add_tracking', compact('get_url','result','PartnerID','row'));
			}
		else{
		return redirect('admin/login');
		}
	}


public function edit_tracking($id) {
		//$PartnerID = $id;
		$username =  Session::get('username'); 
		if(!empty($username))
		{
			$conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$stmt = $conn->prepare("SELECT * FROM Tracking WHERE TrackingID = ". $id); 
			$stmt->execute(); 
			$row = $stmt->fetch();
			$get_url=$this->get_url();
			if(!empty($_POST)){
				if(!empty($row)){
				$stmt = "UPDATE Tracking SET PartnerID = '$_POST[PartnerID]',Company = '$_POST[Company]',Contact = '$_POST[Contact]',Title = '$_POST[Title]',Website = '$_POST[Website]',Email = '$_POST[Email]',Label = '$_POST[Label]' WHERE TrackingID =$id";
				$result = $conn->query($stmt);
				 return redirect('admin/tracking_list')->with('message', 'successfully Update Tracking Info')->withInput();
				}
			}
			$get_url=$this->get_url();
			return view('admin.member_users.edit_tracking', compact('get_url','result','PartnerID','row'));
			}
		else{
		return redirect('admin/login');
		}
	}

	public function delete_tracking($id) 
	{
		$conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$stmt = "DELETE FROM Tracking WHERE TrackingID =".$id;
		$conn->exec($stmt);
		return redirect('admin/tracking_list')->with('message', 'successfully Delete Tracking')->withInput();
	}


public function relationship()
	 {
		$username =  Session::get('username'); 
		if(!empty($username)){
		$get_url = $this->get_url();
		$keyword ='';
		$i =1;
		$conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$stmt = $conn->prepare("select * from Relationships");
		$stmt->execute();
		return view('admin.member_users.relationships',compact('result','i','keyword','get_url','stmt'));
		}
		else{
		return redirect('admin/login');
		}
	}
	
	public function add_relationship()
	{
		
	
	$username = Session::get('username');
	if(!empty($username))
		{
		$get_url = $this->get_url();
		$keyword='';
	 	if(!empty($_POST['FirstName']))
			{
				$Active = 1;
					$conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
					$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$stmt = "INSERT INTO Relationships (FirstName,LastName,Company,Title,Email,Phone,Website,Industry,Picture,Region,LinkedInProfile,PublicProfile,TitleType,Executive,ContactType,Updated,Relationships) VALUES ('$_POST[FirstName]','$_POST[LastName]','$_POST[Company]','$_POST[Title]','$_POST[Email]','$_POST[Phone]','$_POST[Website]','$_POST[Industry]','$_POST[Picture]','$_POST[Region]','$_POST[LinkedInProfile]','$_POST[PublicProfile]','$_POST[TitleType]','$_POST[Executive]','$_POST[ContactType]','$_POST[Updated]','$_POST[Relationships]')";
					$conn->exec($stmt);
					return redirect('admin/member_users/relationship')->with('message', 'Relationship Information successfully added')->withInput();
    			 }
		 return view('admin.member_users.add_relationship',compact('result','i','keyword','get_url','stmt'));	
		} else { return redirect('admin/login');	
	 }
   }
}