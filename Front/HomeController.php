<?php  
namespace App\Http\Controllers\Front;
use Illuminate\Http\Request;
use App\Http\Requests; 
use Illuminate\Auth\Guard;
use Validator;
use Session;

use Redirect;
use App\Http\Controllers\Controller;

class HomeController extends Controller {

	public $auth;
	public function __construct(Guard $auth)
	{
		parent::__construct();
	} 

	public function index()
	{
	echo 'welcome'; exit;
	//$get_url = $this->get_url();	
		//$result = $this->portalusers->find($id);
	//return view('front.home.home');
	return view('front.login.login');
	}

}