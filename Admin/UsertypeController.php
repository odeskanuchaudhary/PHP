<?php 

namespace App\Http\Controllers\Admin; 

use App\Models\Usertype;
use App\Models\Userpermission;
use App\Email_template;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Auth\Guard;
use Validator;
use App\Http\Controllers\Controller;
use DB;
use Mail;

class UsertypeController extends Controller{
/**
*
*@varGuard
*/
public $auth;
public function __construct(Guard $auth,Usertype $usertype,Userpermission $userpermission){
	parent::__construct();
	$this->auth = $auth;
	$this->usertype= $usertype;
	$this->userpermission= $userpermission;
	$this->middleware('auth');
}

public function index(){
	if($this->check_permission() == false){
		return redirect('admin/not_permission');
	}
	$i =1;
	if(!empty($_GET['page'])){
		if($_GET['page']>1)
			$i = ($_GET['page']-1)*$this->per_page+1;
	}
	$get_url=$this->get_url();
	$result = $this->usertype->select('*')->orderby('u_type_id','desc');
	$keyword ='';
	if(isset($_GET['keyword'])){
		if(!empty($_GET['keyword'])){
			$keyword = trim($_GET['keyword'].'%');
			$result->where('name','like',$keyword);
			$keyword =trim($_GET['keyword']);
		}
	}
	$result = $result->paginate($this->per_page);
	return view('admin.usertype.index',compact('result','i','keyword','get_url'));
}
public function add(){
	if($this->check_permission() == false){
		return redirect('admin/not_permission');
	}
	$get_url=$this->get_url();
	$parents = $this->usertype->select('u_type_id','name')->get();
	return view('admin.usertype.add',compact('parents','get_url'));
}
public function postadd(Request $request){	
	if($this->check_permission() == false){
		return redirect('admin/not_permission');
	}
	$get_url=$this->get_url();
	$this->validate($request,$this->usertype->usertype_rules());
	$inpt = $request->all();	
	$inpt['created_at']=date("Y-m-d H:i:s");
	unset($inpt['_token']);
	unset($inpt['keyword']);
	unset($inpt['page']);
	Usertype::insertGetId($inpt);

	return redirect('admin/usertype'.$get_url)->with('message', 'success|user type added successfully');
}
public function edit($id){
	if($this->check_permission() == false){
		return redirect('admin/not_permission');
	}
	$get_url=$this->get_url();
	$result = $this->usertype->find($id);
	$parents = $this->usertype->select('u_type_id','name')->get();
	return view('admin.usertype.edit',compact('result','parents','get_url'));
}

public function update($id,Request $request){
	if($this->check_permission() == false){
		return redirect('admin/not_permission');
	}
	$get_url=$this->get_url();
	$result = $this->usertype->find($id);
//Validatetheforminput
	$this->validate($request,$this->usertype->usertype_edit_rules());
	$inpt = $request->all();
	$input['upated_at']=date("Y-m-d H:i:s");
	unset($inpt['_token']);

	$result->update($inpt);
//Flashandhttp_redirect()

	return redirect('admin/usertype'.$get_url)->with('message', 'success|You have successfully updated this user type.');
}
public function destroy($id)
{
	if($this->check_permission() == false){
		return redirect('admin/not_permission');
	}
	$get_url=$this->get_url();
	if(!empty($id)){
		$result = Usertype:: where('u_type_id',$id)->first();
		if(!empty($result)) {
			$user_data=$this->usertype->find($id);
			Usertype::findOrFail($id)->delete();
			return redirect('admin/usertype'.$get_url)->with('message', 'success|You have successfully deleted a Usertype');
		}	
	} else {
		return redirect('admin/usertype'.$get_url);
	}
}

public function access_rights($id){
	$get_url=$this->get_url();
	$sr_no=1;
	$controllers = $this->ctr_arg;
	$u_type_id = $id;

	$result = $this->userpermission->select()->where('u_type_id','=',$id)->get();
	$select_arg = array();
	if($result->first()){
		foreach($result as $row){
			$select_arg[$row['menu']]['up_read'] = $row['up_read']; 	
			$select_arg[$row['menu']]['up_add'] = $row['up_add']; 
			$select_arg[$row['menu']]['up_edit'] = $row['up_edit']; 	
			$select_arg[$row['menu']]['up_delete'] = $row['up_delete']; 
			$select_arg[$row['menu']]['all'] = 0;
			if( $row['up_read'] == 1 and  $row['up_add'] == 1  and  $row['up_edit'] == 1  and  $row['up_delete'] == 1)	
				$select_arg[$row['menu']]['all'] = 1;
		}
	}

	return view('admin.usertype.access_rights',compact('controllers','get_url','sr_no','u_type_id','select_arg'));
}


public function access_update($id,Request $request){
	$get_url=$this->get_url();

	$inpt = $request->all();
	$controllers = $this->ctr_arg;
	unset($inpt['_token']);

	foreach($controllers as $key=>$val){
		$result = $this->userpermission->select()->where('u_type_id','=',$id)->where('menu','=',$key)->get();

		$data = array();
		$data['up_read'] =0;
		$data['up_add'] =0;
		$data['up_edit'] =0;
		$data['up_delete'] =0;
		$data['u_type_id'] =$id;
		$data['menu'] =$key;

		if(isset($inpt['all'][$key])){
			$data['up_read'] =$data['up_add'] =$data['up_edit'] =$data['up_delete'] =1;
		}	 
		else{
			if(isset($inpt['up_read'][$key])) $data['up_read'] =1;
			if(isset($inpt['up_add'][$key])) $data['up_add'] =1;
			if(isset($inpt['up_edit'][$key])) $data['up_edit'] =1;
			if(isset($inpt['up_delete'][$key])) $data['up_delete'] =1;
		}
		if($result->first()){	
			$where['menu'] = $key;
			$where['u_type_id'] = $id; 
			$this->userpermission->edit_data('it_users_permission',$data,$where);
		}
		else{
			Userpermission::insertGetId($data);
		}
	}

//Flashandhttp_redirect()

	return redirect('admin/usertype'.$get_url)->with('message', 'success|You have successfully updated this user type.');
}



public function change_status($id){
	if($this->check_permission() == false){
		return redirect('admin/not_permission');
	}
	if(!empty($id)){
		$status = '';
		$get_url = $this->get_url();
		$result = Usertype:: where('u_type_id',$id)->first();
		if(!empty($result)) {
			if($result->status == 1) $status = 0; else $status = 1;
			Usertype::where('u_type_id',$id)->update(array('status' => $status));
			return redirect('admin/usertype'.$get_url)->with('message', 'success|Usertype status changed successfully.');
		} else {
			return redirect('admin/usertype'.$get_url);
		}
	} 
}

public function deleteall()
{
	if($this->check_permission() == false){
		return redirect('admin/not_permission');
	}
	$msg = '';
	if(isset($_POST['field']))
	{
		$id_data = $_POST['field']; 
		if(!empty($id_data))
		{
			foreach($id_data as $id)
			{
				$usertype = Usertype::find($id);
				$usertype->delete();
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