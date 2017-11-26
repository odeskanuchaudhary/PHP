<?php
namespace App\Http\Controllers\Admin;
use App\Models\Users;
use App\Email_template;
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

class UsersController extends Controller{
/**
*@varGuard
*/
	public $auth;
	public function __construct(Guard $auth,Users $users) {
		parent::__construct();
		$this->auth=$auth;
		$this->users=$users;
		//$this->image_store = getcwd()."/assets/photos/user/";
		$this->image_store =  getcwd().'/assets/photos/user/';
		$this->middleware('auth');
	}

	public function index(Request $request) 
	
	{
	$get_url = $this->get_url();
		$id = Session::get('id');
		if(!empty($id)){
			$result=$this->users->select('*')->where('status',1)->where('id',$id)->first();
			
				if(!empty($_POST)){
				
				$result = $this->users->find($id);
				
				$this->validate($request,$this->users->users_edit_rules($id));
				$input=Input::all();	
				 $input['updated_at']=date("Y-m-d H:i:s");
				if($input["password"]=='')
				{
					unset($input['password']);
				} else{
					$input['password']= md5($input['password']);
				}	
					
		unset($input["keyword"]);	
		unset($input["page"]);	
			Users::where('id',$id)->update($input);
				$updated_data = $this->users->find($id);
				return redirect('admin/users/edit'.$get_url)->with('message', 'success|You have successfully updated this User.');
		    }
		   return view('admin.users.edit',compact('result','get_url','UpdateDetails'));
		}
		return view('admin.users.edit');
		
	}

	/*public function edit($id) {
	
		$get_url = $this->get_url();	
		$result = $this->users->find($id);
		$result1=$this->usertype->select('*')->where('status',1)->get();	
		return view('admin.users.edit',compact('result','get_url','result1'));
	}

	public function update($id,Request $request) {
		$result = $this->users->find($id);
		$get_url=$this->get_url();
		
		$this->validate($request,$this->users->users_edit_rules($id));
		$input=Input::all();	
	   
		$input['updated_at']=date("Y-m-d H:i:s");
		
		if($input["password"]=='')
		{
			unset($input['password']);
		} else{
			$input['password']= md5($input['password']);
		}
	
		if($_FILES && $_FILES['profile']['name']!='')
	    {
			$destinationPath = $this->image_store.$id."/";
			File::deleteDirectory($destinationPath, true);
			
			$image = Input::file('profile');
			$type = 'main';
			$this->generate_image($image,$destinationPath);
			
			$destinationPath = $this->image_store.$id."/50/";
			$type = 'resize';
			$this->generate_image($image,$destinationPath,$type,50,50);
			
			$destinationPath = $this->image_store.$id."/250/";
			$type = 'resize';
			$this->generate_image($image,$destinationPath,$type,118,160);

			
			$input['profile']="main".".".$image->getClientOriginalExtension();
		}
		
		
		unset($input["keyword"]);	
		unset($input["page"]);	

		
		Users::where('id',$id)->update($input);
		// Update session 
		$updated_data = $this->users->find($id);

			if($updated_data->id == Session::get('id')) {
				Session::put('first_name', $updated_data->first_name);
				Session::put('last_name', $updated_data->last_name);
				Session::put('profile', $updated_data->profile);
			}
		
		return redirect('admin/users'.$get_url)->with('message', 'success|You have successfully updated this User.');
	}

	public function destroy($id){
		$user_data=$this->users->find($id);
		$get_url=$this->get_url();
		
		if($id != Session::get('id')) {
		   Users::findOrFail($id)->delete();
		 	    $this->delete_image($this->image_store.$id."/50/");
				$this->delete_image($this->image_store.$id."/250/");
				$this->delete_image($this->image_store.$id."/");
		      return redirect('admin/users'.$get_url)->with('message', 'success|You have successfully deleted  User');
		} else {
		      return redirect('admin/users'.$get_url)->with('message', 'danger|You cannot delete the user because user login now...');
		}   
	}

	public function change_status($id){
		$get_url = $this->get_url();
			if(!empty($id)){
				if($id != Session::get('id')) {
					$status = '';
					$result = Users:: where('id',$id)->first();
					if(!empty($result)) {
						if($result->status == 1) $status = 0; else $status = 1;
						Users::where('id',$id)->update(array('status' => $status)); //info,warning,danger,success
						return redirect('admin/users'.$get_url)->with('message', 'success|Users plan status changed successfully.');
					} else {
						return redirect('admin/users'.$get_url);
					}
				} else {
					
					return redirect('admin/users'.$get_url)->with('message', 'danger|You cannot change the status because user login now...');
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
					$users = Users::find($id);
					$users->delete();
					$this->delete_image($this->image_store.$id."/50/");
					$this->delete_image($this->image_store.$id."/250/");
					$this->delete_image($this->image_store.$id."/");
				}	
				$msg='success';
			}
		}else{
			$msg='Please select atleast one field.';	
		}
		echo json_encode($msg);	
		exit;
	}
	public 	function delete_image($destinationPath){
				File::deleteDirectory($destinationPath);
		}

	public 	function generate_image($image,$destinationPath,$type='main',$width='',$height=''){
				$image_name = "main".".";
				File::makeDirectory($destinationPath, 0775, true, true);
				if($type == 'main'){
				$main_path = $destinationPath.$image_name.$image->getClientOriginalExtension();
				Image::make($image->getRealPath())->save($main_path);
				}
				
				if($type == 'resize'){
					$resize_path = $destinationPath.$image_name.$image->getClientOriginalExtension();
					Image::make($image->getRealPath())->resize($width,$height)->save($resize_path);
				}
				
		}
		
	public 	function clear_session(){
		
			$get_url = $this->get_url();
				$chk_date = date("Y-m-d H:i:s", strtotime("-2 week"));
			
			
				$cust_cart = CustCart :: select('id')->where('cust_id',0)->where('updated_at','<',$chk_date)->get();
				foreach($cust_cart as $row){
					
					
					$cust_cartdata = CustCartData :: where('cart_id',$row->id);
					$cust_cartdata->delete();
					
					$path =  getcwd()."/assets/photos/carts_img/".$row->id."/";
					$this->delete_image($path);
							
				}
				$cust_cart = CustCart :: where('cust_id',0)->where('updated_at','<',$chk_date);
				$cust_cart->delete();
				
			
				$cust_session = CustSession :: select('id')->where('cust_id',0)->where('updated_at','<',$chk_date)->get();
				foreach($cust_session as $row){
					
					$cust_cart = CustCart :: where('session_id',$row->id)->get();
					if(empty($cust_cart->first())){
						
							$cust_sessiondata = CustSessionData :: where('session_id',$row->id);
						    $cust_sessiondata->delete();	
							
							$cust_sessiondel = CustSession :: where('id',$row->id);
							$cust_sessiondel->delete();
							
							$cust_imgdel = CustImg :: where('session_id',$row->id);
							$cust_imgdel->delete();
								
							$path =  getcwd()."/assets/photos/session_img/".$row->id."/";
							$this->delete_image($path);
					}
				}
			return redirect('admin/users'.$get_url)->with('message', 'success|Session db data removed successfully.');
	}	*/
		
}