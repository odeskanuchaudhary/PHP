<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Models\Usertype;
use App\Models\Prospects;
use App\Models\Member;
use App\Models\Company;
use App\Http\Requests;
use Illuminate\Auth\Guard;
use App\Http\Controllers\Controller;
use Session;
use DB;
class DashboardController extends Controller
{
/**
*Displayalistingoftheresource.
*
*@returnResponse
*/
	public function __construct(Guard $auth,Usertype $usertype)
	{
	parent::__construct();
		//$this->auth=$auth;
		//$this->middleware('auth');
	}
	public function index()
	{
		$i=$j=$k=$l=1;
		$users= Member::count();
	    $dashboard_data = array();
		$dashboard_data['member'] = DB::table('members')->join('company', 'members.company_id', '=', 'company.id')
            ->select('members.*', 'company.c_name', 'company.website')->get();
		
		$dashboard_data['users_active'] = DB::table('members')->join('company', 'members.company_id', '=', 'company.id')
            ->select('members.*', 'company.c_name', 'company.website')
			->where('members.status',1)->count();
			
		$dashboard_data['users_inactive'] = DB::table('members')->join('company','members.company_id', '=', 'company.id')
            ->select('members.*', 'company.c_name', 'company.website')
			->where('members.status',0)->count();
		
			$company   = Company::count();
			$prospects = Prospects::count();
		
			return view('admin.dashboard',compact('company','prospects','training','users','dashboard_data','i','j','k','l','no_of_users','no_of_sales','data'));
	}
	public  function getLogout()
	{
		Session::flush();
		return redirect('admin/login');
	}	
}
