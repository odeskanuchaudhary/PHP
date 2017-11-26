<?php
namespace App\Http\Controllers\Admin;
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
class Company_Controller extends Controller{

	public $auth;
	public function __construct(Guard $auth,Company $company) {
		parent::__construct();
		//$this->auth=$auth;
		$this->company=$company;
		//$this->middleware('auth');
	}
	 public function index() {
		$username =  Session::get('username'); 
		if(!empty($username)){
		$get_url = $this->get_url();
		$keyword ='';
		$i =1;
		return view('admin.company.index',compact('result','i','keyword','get_url'));
		}
		else{
		return redirect('admin/login');
		}
	}

	public function add() {
		$username =  Session::get('username'); 
		if(!empty($username)){
		$get_url=$this->get_url();
		
		if(!empty($_POST['Company']))
		{
			$Active = 1;
			$conn = new PDO("sqlsrv:server = tcp:ensable.database.windows.net,1433; Database = Ensable", "Ensable", "Startit1");
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$stmt = "INSERT INTO CompanyDB (Company,Website,Phone,DataCom,Inc5000,Hoovers,Facebook,Yelp,BBB,YouTube,LinkedIn,Twitter,Logo,Locations,International,Revenue,Employees,Founded,Competitors,MarketValue,Sales,EmployeeGrowth,EarthQuakes,NaturalDisasters,Shipping,Advertising,City,State,ZIP,NameServer,EmailServer,EmailServerDC,EmailServerDCLatLong,EmailServerIP,WebsiteDC,WebsiteDCLatLong,WebsiteIP,CDN,Framework,ServerHardware,EmailProviders,Anayltics,Media,Widgets,CRM,PhoneSystem,ISP,PhoneCarrier,Ips,POS,SSL,Documentation,Conferencing,Updated) VALUES ('$_POST[Company]','$_POST[Website]','$_POST[Phone]','$_POST[DataCom]','$_POST[Inc5000]','$_POST[Hoovers]','$_POST[Facebook]','$_POST[Yelp]','$_POST[BBB]','$_POST[YouTube]','$_POST[LinkedIn]','$_POST[Twitter]','$_POST[Logo]','$_POST[Locations]','$_POST[International]','$_POST[Revenue]','$_POST[Employees]','$_POST[Founded]','$_POST[Competitors]','$_POST[MarketValue]','$_POST[Sales]','$_POST[EmployeeGrowth]','$_POST[EarthQuakes]','$_POST[NaturalDisasters]','$_POST[Shipping]','$_POST[Advertising]','$_POST[City]','$_POST[State]','$_POST[ZIP]','$_POST[NameServer]','$_POST[EmailServer]','$_POST[EmailServerDC]','$_POST[EmailServerDCLatLong]','$_POST[EmailServerIP]','$_POST[WebsiteDC]','$_POST[WebsiteDCLatLong]','$_POST[WebsiteIP]','$_POST[CDN]','$_POST[Framework]','$_POST[ServerHardware]','$_POST[EmailProviders]','$_POST[Anayltics]','$_POST[Media]','$_POST[Widgets]','$_POST[CRM]','$_POST[PhoneSystem]','$_POST[ISP]','$_POST[PhoneCarrier]','$_POST[Ips]','$_POST[POS]','$_POST[SSL]','$_POST[Documentation]','$_POST[Conferencing]','$_POST[Updated]')";
			$conn->exec($stmt);
			return redirect('admin/company/add')->with('message', 'Company Record successfully added.');
		}
		return view('admin.company.add', compact('get_url','result'));
		}
		else{
		return redirect('admin/login');
		}
	}

	public function edit_company() {
		$get_url=$this->get_url();
		return view('admin.company.edit', compact('get_url','result'));
	}


	public function edit(Request $request) {
		$get_url = $this->get_url();	
		return view('admin.company.edit', compact('get_url','result'));
	}


	public function update($id,Request $request) {
		$result = $this->company->find($id);
		$get_url=$this->get_url();
		$this->validate($request,$this->company->company_edit_rules($id));
		$input=Input::all();	
		$input['updated_at']=date("Y-m-d H:i:s");
		$input['status']=1;
		unset($input['_token']);
		unset($input["keyword"]);	
		unset($input["page"]);	
		Company::where('id',$id)->update($input);
		$updated_data = $this->company->find($id);
		return redirect('admin/company'.$get_url)->with('message', 'success|You have successfully updated this Company.');
	}

	public function destroy($id){
		$user_data=$this->company->find($id);
		$get_url=$this->get_url();
		if($id != Session::get('id')) {
			DB::table('Members')->where('company_id', $id)->delete();
			DB::table('prospects')->where('comp_id', $id)->delete();
			DB::table('company')->where('id', $id)->delete();
		      return redirect('admin/company'.$get_url)->with('message', 'success|You have successfully deleted  Company');
		} else {
		      return redirect('admin/company'.$get_url)->with('message', 'danger|You cannot delete the user because Company login now...');
		}   
	}

	public function change_status($id)
	{
			$get_url = $this->get_url();
			if(!empty($id)){
				$status = '';
					$result = Company:: where('id',$id)->first();
					if(!empty($result)) {
						if($result->status == 1) $status = 0; else $status = 1;
						Company::where('id',$id)->update(array('status' => $status)); //info,warning,danger,success
						return redirect('admin/company'.$get_url)->with('message', 'success|Company status changed successfully.');
					} else {
					return redirect('admin/company'.$get_url)->with('message', 'danger|You cannot change the status ');
				}	
			} 
	}

	public function deleteall() {
		$msg = '';
		if(isset($_POST['field']))
		{
			$id_data = $_POST['field']; 
			if(!empty($id_data))
			{
				foreach($id_data as $id)
				{
					$traing_data = Company::find($id);
					$traing_data->delete();
				}	
				$msg='success';
			}
		}else{
			$msg='Please select atleast one field.';	
		}
		echo json_encode($msg);	
		exit;
	}
	
}