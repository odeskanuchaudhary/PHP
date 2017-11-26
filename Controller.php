<?php  
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Session;
use Route;
use Cookie;
use DB;
use File;
use Mail;
use Illuminate\Cookie\CookieJar;
use App\Models\Users;
use App\Models\CustImg;
use App\Models\Userpermission;


abstract class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;
	
	 /** 
     * @var Flash
     */
   // public $flash;
	 public function __construct()
    {
	    //$this->flash = $flash;
		$this->base_path = '/';
		$this->per_page = 20;  
		
		$this->pc_ip = $this->get_cookie();
		
		
		
		$this->ctr_arg = array('users'=>'Users');
    }
	
	 public  function check_permission(){
		$action = Route::getCurrentRoute()->getAction();
		$controller = class_basename($action['controller']);
		 list($controller, $action) = explode('@', $controller);
		 
		 $menu = strtolower(str_replace('Controller','',$controller));
	
		 
		 if($action == 'destroy' || $action == 'deleteall' || $action == 'clear_session')
			 $action_colum = 'up_delete';
		 else if($action == 'change_status' || $action == 'update')
			 $action_colum = 'up_edit';
		 else if($action == 'index')
			 $action_colum = 'up_read';
	 	 else if($action == 'postadd')
			 $action_colum = 'up_add';	 
		 else
			 $action_colum = 'up_'.$action;
		 
	 
	  $u_type_id = session()->get('u_type_id');
	  
	  $result = Userpermission::select()->where('u_type_id','=',$u_type_id)->where('menu','=',$menu)->get()->first();
	  if(!empty($result)){
	 	if($result->$action_colum == 0){ 
			return false;
		}
		// return redirect()->guest('admin/not_permission');	
	  }
		return true;	
	 	
	 
	 }
	 
    public function get_cookie(){
		
		if(!Session::has('front_logged_in')){ 
			Session :: forget('front_logged_in');
			Session :: forget('cust_id');
			Session :: forget('email');
			Session :: forget('first_name');
			Session :: forget('last_name');
			Session :: forget('profile');
			Session :: forget('mobile');
		}  
		
			$pc_ip = cookie::get('pc_ip'); 
			if(empty($pc_ip)){
				return redirect(BASE_URL.'home');
				exit;   
				
			 }
		 return $pc_ip;
	}
	 
	 
	  public function get_cookie_chk(){
			$pc_ip = cookie::get('pc_ip'); 
			if(empty($pc_ip)){ 
				return false;
			 }
		 return $pc_ip;
	}
	 
	 
	 
	 
	
	
	 public   function get_url(){
		if(isset($_GET['keyword']))
		$keyword =trim($_GET['keyword']);
		$page =1;
		if(isset($_GET['page']))
		$page =trim($_GET['page']);
		$get_url='';
		if(!empty($keyword)){
			$keyword =trim($keyword);
			$get_url = '?keyword='.$keyword;
		}else
		$get_url = '?keyword='.'';
		
		if(!empty($page)){
			$page =trim($page);
			$get_url .= '&page='.$page;
		}
		else
		$get_url .= '&page=1';
		return $get_url;
	}
	
	public function confirmation_mail($order_id,$transaction_id){
	
		$order_row = DB::table('it_orders')->where('cust_id',Session::get('cust_id'))->whereRaw("statusid = 2 OR statusid = 7 ")->where('id',$order_id)->orderby('id','DESC')->first();		
		
		
		$email_row = DB::table('it_email_template')->where('id',82)->first();
		$customer_name = Session::get('first_name').' '.Session::get('last_name');
		$date = date('M j, Y');
		$order_number = $order_row->order_no;
		$shipping_name_row = DB::table('it_shipping_prices')->where('id',$order_row->shipping_methodid)->whereRaw("(std_price=".$order_row->shipping_price."  OR exp_price=".$order_row->shipping_price." )")->first();

		$shipping_row = DB::table('it_ordersaddress')->where('order_id',$order_id)->where('id',$order_row->shipping_addressid)->first();
		$country = DB::table('it_countries')->where('id',$shipping_row->country)->first();
		$region = DB::table('it_regions')->where('id',$shipping_row->region)->first();
		$shipping_address = $shipping_row->address1."<br>";
		if($shipping_row->address2!="") $shipping_address .= $shipping_row->address2."<br>";
		$shipping_address .= $shipping_row->city.", ".$region->abbr." ".$shipping_row->postcode."<br>".$country->title.'.';
		if($shipping_row->phone)
		$shipping_address .= '<br> Phone: '.$shipping_row->phone;
		$shipping_name = $shipping_row->firstname." ".$shipping_row->lastname;

		$billing_row = DB::table('it_ordersaddress')->where('order_id',$order_id)->where('id',$order_row->billing_addressid)->first();
		$country = DB::table('it_countries')->where('id',$billing_row->country)->first();
		$region = DB::table('it_regions')->where('id',$billing_row->region)->first();
		$billing_address = $billing_row->address1."<br>";
		if($billing_row->address2!="") $billing_address .= $billing_row->address2."<br>";
		$billing_address .= $billing_row->city.", ".$region->abbr." ".$billing_row->postcode."<br>".$country->title.'.';
		
		if($billing_row->phone)
		$billing_address .= '<br> Phone: '.$billing_row->phone;
	
		$billing_name = $billing_row->firstname." ".$billing_row->lastname;
		
		$order_items = DB::table('it_ordersitems')->where('order_id',$order_id)->get();
		$sizes_result = DB::table('tl_size')->select('size_id','size_name','sale_price')->where('status',1)->get();
		foreach($sizes_result as $size){
			$size_names[$size->size_id] = $size->size_name;
			//$size_names[$size->size_id][1] = $size->sale_price;
		}
		$coupon_details = $details ='';
		
		$details='<table border="1" style="width:100%;border-spacing:0;border-collapse: collapse;border:1px solid #ccc;">
							<tbody>
							<thead style="background-color:#8bacc2;color:#fff;">
								<tr border=1>
									<th style="text-align:left;padding: 8px;vertical-align:top">Canvas</th>
									<th style="text-align:left;padding: 8px; vertical-align:top">Product Details</th>
									<th style="text-align:left;padding: 8px;vertical-align:top">Price</th>
									<th style="text-align:left;padding: 8px;vertical-align:top">Quantity</th>
									<th style="text-align:left;padding: 8px;vertical-align:top">Total</th>
								</tr><thead>';
		
		//$sub_total = 0;$retouch_price=$option_price=0;
		foreach($order_items as $items){
			$sub_total = 0;$retouch_price=$option_price=0;
			$canvas_row  = DB::table('tl_product')->select('prod_id','prod_name')->where('prod_id',$items->prod_id)->first();
			$details .="<tr border=1>
						<td style='padding: 8px;'><img src='".BASE_URL."/assets/photos/order/".$order_id."/".$items->id."/canvas_prev/prev_img_with_border_thumb.jpg'></td>
						<td style='padding: 8px;' nowrap><b>".$canvas_row->prod_name."</b><br><b>Type: </b> ".$size_names[$items->size_id]."<br><b>Option: </b>  3/4&quot; Thickness<br>";
			if($items->retouch_id > 0){
				$retouch = DB::table('tl_retouching')->where('retouch_id',$items->retouch_id)->first();
				$details .="<b>Retouch : </b>".$retouch->name." - $".$retouch->price."<br/>";
				$retouch_price = $retouch->price;
			}
			if($items->option_id > 1){
				$options = DB::table('tl_option')->where('id',$items->option_id)->first();
				$details .="<b>".$options->opt_name.":</b> $".$options->opt_price;
				$option_price = $options->opt_price;
			}
			$price = $items->sale_price; 
			$sub_total = $items->total_sale_price; 

			$details .="</td>
						<td style='padding: 8px;'> $".number_format($price, 2, '.', ',')."</td>
						<td style='padding: 8px;'>".$items->qty."</td>
						<td style='padding: 8px;'> $".number_format($sub_total, 2, '.', ',')."</td>
						</tr></tbody>";	
			
		}
		
		
		$details .="<tr><td style='padding: 8px;' colspan='4' align='right'><b>Total :</b> </td>";
		$details .="<td style='padding: 8px;' colspan='4'><b>$".number_format($order_row->orignal_price, 2, '.', ',')."</b></td></tr>";
		
		$details .="<tr style='border-top:1px solid transparent;'><td style='border-top: medium none !important;padding: 8px;' colspan='4' align='right'><b>Shipping Price($): </b></td>";
			
		
		$details .="<td style='border-top: medium none !important;padding: 8px;' colspan='4'><b>$".number_format($order_row->shipping_price, 2, '.', ',')."</b></td></tr>";
		
		if($order_row->coupons_id >0){
			$details .="<tr style='border-top:1px solid transparent;'><td style='border-top: medium none;padding: 8px;' colspan='4' align='right'><b>Coupon Discount : </b></td>";
			$details .="<td style='border-top: medium none !important;padding: 8px;' colspan='4'><b>$".number_format($order_row->coupons_disc_price, 2, '.', ',')."</b></td></tr>";
		}
		
	
		
		$details .="<tr style='border-top:1px solid transparent;'><td style='border-top: medium none !important;padding: 8px;' colspan='4' align='right'><b>Net Total :</b> </td>";
		$net_total =$order_row->total_price;
		$details .="<td style='border-top: medium none !important;padding: 8px;' colspan='4'><b>$".(number_format($net_total, 2, '.', ','))."</b></td></tr>";
		
		$details .='</tbody></table>';
		
		
		$pay_type = "";
		$order_pay = DB::table('it_orderspayment')->where('order_id',$order_id)->first();	
		if($order_pay){
		$card_no = $order_pay->card_number;	
		$transaction_id = $order_pay->transaction_id;
		$pay_type1 = $order_pay->type;
		$pay_type = ($pay_type1 == 1) ? "Paypal" : (($pay_type1 == 2)  ? "Check" : "Credit card");
		}
		
		
		$message = $email_row->msg;
		$support_email = 'support@namebadge.com';//'admin@office.com';
		$from_name 	   = 'support@namebadge.com';
		if($transaction_id==0) $transaction_id ='--'; 
		$search = array("{CUSTOMER_NAME}","{EMAIL}","{SUPPORT_EMAIL}","{SITE_NAME}","{BASE_URL}","{DETAILS}","{SHIPPING_NAME}","{BILLING_NAME}","{SHIPPING_ADDRESS}","{BILLING_ADDRESS}","{ORDER_NUMBER}","{DATE}","{YEAR}","{TRANSCATID_ID}","{COUPON_DETAILS}","{PAYMENT_BY}");      
		$replace 	= array($customer_name,Session::get('email'),$support_email,SITE_NAME,BASE_URL,$details,$shipping_name,$billing_name,$shipping_address,$billing_address,$order_number,$date,date("Y"),$transaction_id,$coupon_details,$pay_type);
		$msg = str_replace($search, $replace, $message);
		$data = array("msg" => $msg);
		$sub_subject = str_replace(array("{NAME}"), $customer_name,$email_row->subject);
		$user = array("to"=>Session::get('email'),"name"=>$customer_name,"from"=>$support_email,"sub_subject"=>$sub_subject);
		
		Mail::send('front.emails.welcome', $data, function($message) use ($user) {
			$message->from($user['from'],SITE_NAME);
			$message->to($user['to'],$user['name'])->subject($user['sub_subject']);
		});	
		
		return true;
	}
	
	public function check_login()
	{
		if(!Session::has('front_logged_in')){
			return  Redirect('/login')->send();
		}
		
	}
	
	
	public function convert_customer_img($session_id,$cust_id){
		$resImg = CustImg :: where('session_id',$session_id)->where('status', 0)->where('cust_id', 0)->orderBy('id','DESC')->get();
	
		if($resImg->first()){
			
				$destinationPath = getcwd()."/assets/photos/customer_img/".$cust_id;
				File::makeDirectory($destinationPath, 0775, true, true);
				$destinationPath = getcwd()."/assets/photos/customer_img/".$cust_id."/thumb";
				File::makeDirectory($destinationPath, 0775, true, true);
				$destinationPath = getcwd()."/assets/photos/customer_img/".$cust_id."/canvas_img";
				File::makeDirectory($destinationPath, 0775, true, true);
				$destinationPath = getcwd()."/assets/photos/customer_img/".$cust_id."/canvas_prev";
				File::makeDirectory($destinationPath, 0775, true, true);
			
			foreach($resImg as $rsrow){
				
				$file=getcwd()."/assets/photos/session_img/".$session_id."/".$rsrow->src;
				$dest=getcwd()."/assets/photos/customer_img/".$cust_id."/".$rsrow->src;
				if(file_exists($file))
				File::copy($file, $dest);
			
				$file=getcwd()."/assets/photos/session_img/".$session_id."/thumb/".$rsrow->src;
				$dest=getcwd()."/assets/photos/customer_img/".$cust_id."/thumb/".$rsrow->src;
				if(file_exists($file))
				File::copy($file, $dest);
			
				$file=getcwd()."/assets/photos/session_img/".$session_id."/canvas_img/".$rsrow->src;
				$dest=getcwd()."/assets/photos/customer_img/".$cust_id."/canvas_img/".$rsrow->src;
				if(file_exists($file))
				File::copy($file, $dest);
				
				$file=getcwd()."/assets/photos/session_img/".$session_id."/canvas_prev/prev_img_with_border.jpg";
				$dest=getcwd()."/assets/photos/customer_img/".$cust_id."/canvas_prev/prev_img_with_border.jpg";
				if(file_exists($file))
				File::copy($file, $dest);
				
				$file=getcwd()."/assets/photos/session_img/".$session_id."/canvas_prev/prev_img_with_border_thumb.jpg";
				$dest=getcwd()."/assets/photos/customer_img/".$cust_id."/canvas_prev/prev_img_with_border_thumb.jpg";
				if(file_exists($file))
				File::copy($file, $dest);
			
				$file=getcwd()."/assets/photos/session_img/".$session_id."/canvas_prev/prev_img_without_border.jpg";
				$dest=getcwd()."/assets/photos/customer_img/".$cust_id."/canvas_prev/prev_img_without_border.jpg";
				if(file_exists($file))
				File::copy($file, $dest);
			
				$file=getcwd()."/assets/photos/session_img/".$session_id."/canvas_prev/prev_img_without_border_thumb.jpg";
				$dest=getcwd()."/assets/photos/customer_img/".$cust_id."/canvas_prev/prev_img_without_border_thumb.jpg";
				if(file_exists($file))
				File::copy($file, $dest);
			
				$input_res['main_img'] = BASE_URL."assets/photos/customer_img/".$cust_id."/".$rsrow->src;
				DB::table('it_cust_images')->where('id',$rsrow->id)->update($input_res);
			
			
			}
		}
		return;
	}
	
	
	
	
	
	
}