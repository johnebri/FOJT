<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		//$this->load->model('mapp');
		// $this->load->library('session');
		$this->load->helpers('url');
		$this->load->database();
		$this->load->helper('string');
		
		require_once(APPPATH.'controllers/Email.php'); //include Email controller		

		// if (password_verify("john", $hashed)) {
		// 	echo 'match';
		// } else {
		// 	echo 'failed';
		// }
	}

	public function index() {
		$this->load->view('index');
	}

	public function vote($con_id) {
		$data = array(
			'con_id' => $con_id
		);
		$this->load->view('vote', $data);
	}

	public function vote_action() {
		$data = array(
			'con_id' => $this->input->post('con_id'),
			'no_of_votes' => $this->input->post('no_of_votes')
		);

		$cost_per_vote = 50;
		$no_of_votes = $data['no_of_votes'];
		$total_cost = $no_of_votes * $cost_per_vote;

		// call paystack window from here

	}

	public function login() {
		$this->load->view('login');
	}

	public function signup() {
		$this->load->view('signup');
	}

	public function signup_action() {

		$clean = true;
		$fullname = $_POST['fullname'];
		$email = $_POST['email'];
		$password = $_POST['password'];
		$subscription = $_POST['subscription'];
		$role = $_POST['role'];

		$activation_code = random_string('alnum', 25) . time();


		if($subscription == "") {
			$subscription = "no";
		}

		// check length of fullname
		if(strlen($fullname) < 4) {
			echo '<li class="error">Your fullname must have at least 4 characters</li>';
			$clean=false;
		}

		// validate email address pattern
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

			// email pattern is not valid
			echo '<li class="error">Please enter a valid Email Address</li>';
			$clean=false;

		} 
		
		// check if email is already in use
		$query = $this->db->get_where('users', array("email" => $email));
		if($this->db->affected_rows() > 0) {

			// email is already in use
			echo '<li class="error">Email Address is already in use</li>';
			$clean=false;

		} 

		// check length of password
		if(strlen($password) < 6) {
			echo '<li class="error">You password must have at least 6 characters</li>';
			$clean=false;
		}

		if($clean == true) { 
			// hash password
			$hashed_password = password_hash($password, PASSWORD_DEFAULT);

			$tutorData = array(
				'fullname' => $fullname,
				'email' => $email,
				'password' => $hashed_password,
				'subscribe_to_mails' => $subscription,
				'role' => $role,
				'activation_code' => $activation_code
			);
			$this->db->set('date_time', 'NOW()', FALSE);
			$this->db->insert('users', $tutorData);
			echo '<div class="success">Your registration was successfull, Check your mail for a confirmation link</li>';
			
			// send mail here
			$emailObj = new Email();  //create object 
			
			// activation link
			$activationLink = base_url().'activateAccount/'.$activation_code;

			// Email body content
            $body = '<h1>Welcome to Codac</h1>
				<p>Click on the button to activate your account</p>
				<a href="'.$activationLink.'"><button>Activate Your Account</button></a>';
            
        
			$response = $emailObj->sendMail($email, "test@codac.pulaakutrade.com", "Codac", "Activate Your Account",  $body); 
			// echo '<h1>'.$response.'</h1>';
//call function
           
// 				sendMail($to, $from, $from_name, $subject, $body);
		
// 			   $to   = 'john.ebri@yahoo.com';
//             $from = 'test@codac.pulaakutrade.com';
//             $name = 'PHPMailer and CodeIgniter';
//             $subj = 'Test Message';
//             $msg = 'This is mail about testing mailing using PHP.';
	
//             $error=smtpmailer($to,$from, $name ,$subj, $msg);
			
			// clear the form
			echo '<script>document.getElementById("signupform").reset()</script>';
		}

	}

	public function dashboard() {
		$this->load->view('dashboard');
	}

	public function createcourse() {
		$this->load->view('createcourse');
	}

	public function getrole() {
		echo 'getting role';
	}

	public function activateAccount($code) {
		// check code
		$query = $this->db->get_where('users', array('activation_code'=>$code));
		if($this->db->affected_rows()) {
			// code found, activate account
			// get user details
			$result = $query->result_array();
			foreach($result as $val) {
				$user_id = $val["user_id"];
				$activated = $val["activated"];
			}

			// check if user is already activated
			if($activated == 1) {
				// account is already activated
				echo 'your account is already activated';
			} else {
				// account is not activated, activate now
				$data = array(
					'activated' => 1
				);
				$this->db->set($data);
				$this->db->where('user_id', $user_id);
				$this->db->update('users');	

				// check if account activation succeeded
				if ($this->db->affected_rows() > 0) {
					// account activation succeeded
					echo 'account activated successfully';
				} else {
					// account activation failed
					echo 'account activation failed';
				}
			}

		} else {
			// code not found
			echo 'Invalid Activation Code';
		}
	}




} // end of app controller
