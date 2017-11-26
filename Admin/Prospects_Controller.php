<?php
namespace App\Http\Controllers\Admin;
use App\Models\Prospects;
use App\Models\Portal_Sales;
use App\Models\Portal_Users;
use App\Models\Company;
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
use Intervention\Image\Facades\Image;
class Prospects_Controller extends Controller
{
    /**
     *@varGuard
     */
    public $auth;
    public function __construct(Guard $auth, Prospects $prospects)
    {
        parent::__construct();
        $this->auth = $auth;
        $this->prospects = $prospects;
        $this->middleware('auth');
    }
    public function index()
    {
        $i = 1;
        if (!empty($_GET['page'])) {
            if ($_GET['page'] > 1)
                $i = ($_GET['page'] - 1) * $this->per_page + 1;
        }
        $get_url = $this->get_url();
        $result = $this->prospects->select('*')->orderby('id', 'desc');
        $keyword = '';
        if (isset($_GET['keyword'])) {
            if (!empty($_GET['keyword'])) {
                $keyword = trim($_GET['keyword'] . '%');
                $result->where('title', 'like', $keyword)->orwhere('website', 'like', $keyword);
                $keyword = trim($_GET['keyword']);
            }
        }
        $sale_name = $temp_cnt = array();
        $prospects = Prospects::where('status', 1)->orderby('id', 'desc')->get();
        if (!empty($prospects)) {
            $data = Company::where('status', 1)->orderby('id', 'desc')->get();
        }
        $result = $result->paginate($this->per_page);
        return view('admin.prospects.index', compact('result', 'i', 'keyword', 'get_url', 'data'));
        
    }
    
    
    
    public function add()
    {
        
        $get_url = $this->get_url();
        
        $comp_id = Company::where('status', 1)->orderby('id', 'desc')->get();
        
        return view('admin.prospects.add', compact('get_url', 'result', 'comp_id'));
        
    }
    
    
    
    public function postadd(Request $request)
    {
        
        $get_url = $this->get_url();
        
        $c_name = $request->c_name;
        
        $company = Company::where('id', $c_name)->first();
        
        $this->validate($request, $this->prospects->prospects_rules());
        
        $input = Input::except('_token');
        
        $input['created_at'] = date("Y-m-d H:i:s");
        
        $input['status'] = 1;
        
        unset($input['confirm']);
        
        unset($input["keyword"]);
        
        unset($input["page"]);
        
        unset($input["company"]);
        
        $id = Prospects::insertGetId($input);
        
        return redirect('admin/prospects' . $get_url)->with('message', 'success|Training added successfully');
        
    }
    
    
    
    public function edit($id)
    {
        
        $get_url = $this->get_url();
        
        $company = Company::where('status', 1)->orderby('id', 'desc')->get();
        
        $result = $this->prospects->find($id);
        
        return view('admin.prospects.edit', compact('result', 'get_url', 'company'));
        
    }
    
    
    
    public function update($id, Request $request)
    {
        
        $result = $this->prospects->find($id);
        
        $get_url = $this->get_url();
        
        $this->validate($request, $this->prospects->prospects_edit_rules($id));
        
        $c_id = $request->comp_id;
        
        $company = Company::where('id', $c_id)->first();
        
        $input = Input::all();
        
        $input['updated_at'] = date("Y-m-d H:i:s");
        
        $input['status'] = 1;
        
        unset($input['_token']);
        
        unset($input["keyword"]);
        
        unset($input["page"]);
        
        Prospects::where('id', $id)->update($input);
        
        $updated_data = $this->prospects->find($id);
        
        return redirect('admin/prospects' . $get_url)->with('message', 'success|You have successfully updated this Training.');
        
    }
    
    
    
    public function destroy($id)
    {
        
        $user_data = $this->prospects->find($id);
        
        $get_url = $this->get_url();
        
        if ($id != Session::get('id')) {
            
            Prospects::findOrFail($id)->delete();
            
            return redirect('admin/prospects' . $get_url)->with('message', 'success|You have successfully deleted  Training');
            
        } else {
            
            return redirect('admin/prospects' . $get_url)->with('message', 'danger|You cannot delete the user because Training login now...');
            
        }
        
    }
    
    
    
    public function change_status($id)
    {
        $get_url = $this->get_url();
         if (!empty($id)) {
            if ($id != Session::get('id')) {
                $status = '';
                $result = Prospects::where('id', $id)->first();
                if (!empty($result)) {
                    if ($result->status == 1)
                        $status = 0;
                    else
                        $status = 1;
                    Prospects::where('id', $id)->update(array(
                        'status' => $status
                    )); //info,warning,danger,success
                    return redirect('admin/prospects' . $get_url)->with('message', 'success|Training status changed successfully.');
                } else {
                    return redirect('admin/prospects' . $get_url);
                }
            } else {
                return redirect('admin/prospects' . $get_url)->with('message', 'danger|You cannot change the status ');
            }
        }
    }
    
    public function deleteall()
    {
        $msg = '';
        if (isset($_POST['field'])) {
            $id_data = $_POST['field'];
            if (!empty($id_data)) {
                foreach ($id_data as $id) {
                    $traing_data = Prospects::find($id);
                    $traing_data->delete();
                }
                $msg = 'success';
            }
        } else {
            
            $msg = 'Please select atleast one field.';
        }
        echo json_encode($msg);
        exit;
    }
    public function details($id)
    {
        $get_url = $this->get_url();
        $i = 1;
		 if (!empty($id)) {
         $member_data = DB::table('company')->join('Members', 'company.id', '=', 'Members.company_id')
		 ->where('Members.company_id', '!=', $id)->get();
		 $sum_lead = DB::table('company')
			 ->join('Members', 'company.id', '=', 'Members.company_id')
    		 ->where('Members.company_id', '!=', $id)
			 ->sum('Members.total_lead');
        }
        return view('admin.prospects.details', compact('result', 'get_url', 'member_data', 'i','sum_lead'));
    }
}