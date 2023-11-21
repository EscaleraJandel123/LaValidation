<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class MainController extends Controller {

    public function __construct(){
        parent::__construct();
        $this->call->library('session');
        $this->call->library('email');
        $this->call->model('Login_model');
        $this->getusers = $this->Login_model->getusers();
    }
	public function login(){
        return $this->call->view('login');
    }
	public function register(){
        return $this->call->view('register');
    }
	public function upload(){
        return $this->call->view('upload_form');
    }
    public function logout()
    {
        $this->session->sess_destroy();
        redirect('login');
    }

    public function create() {
        // retrieve form input values
        $email = $this->io->post('email');
        $password = $this->io->post('password');
        // add user inputs to an array and continue with the insertion of data to the database with hashed token & password for security
        $data = array(
            "email" => $email,
            "password"=> password_hash($password,PASSWORD_DEFAULT),
            "token" => "unverified",
        );
        $this->login_model->addUser($data);
        redirect('/login');
    }
    public function auth() {
        $email = $this->io->post('email');
        $password = $this->io->post('password');
        $users = $this->getusers;
        foreach ($users as $user) {
            if ($email == $user['email']) {
                if (password_verify($password, $user['password'])) {
                    if($user['token'] == "unverified")
                    {
                        $recepient_email = $email;
                        $subject = "Email Verification";
                        $content = "Hello,<br><br>This is a LAVLAUST4 email.<br>Proceed to this <a href='" . site_url("pendingVerification") . "/" . $user['id'] . "'>Link</a> to verify your account.<br><br>Best regards,<br>Your Name";
                        $this->sendEmailVerification($recepient_email,$subject,$content);
                        
                        $this->call->view('unverified');
                        return;
                    } else {
                        $this->session->set_userdata('userEmail', $user['email']);
                        $data['email'] = $this->session->userdata('userEmail');
                        $this->call->view('verified',$data);
                        return;
                    }
                } else {
                    redirect('login');
                    return;
                }
            }
        }
        redirect('login');
    }

    public function pendingVerification($id)
    {
        $data = $this->login_model->searchUser($id);
        $data['token'] = "verified";
        if($data['token'] == "verified")
        {
            $this->login_model->updateToken($id,$data);
            $this->session->set_userdata('userEmail', $data['email']);
            $this->call->view('verified',$data);
        } else {
            $this->call->view('unverified');
        }
    }

    public function sendEmailVerification($recepient_email,$subject,$content)
    {
        $this->email->sender('alejandrogino950@gmail.com', 'Lavalust Activity');
        $this->email->recipient($recepient_email);
        $this->email->subject($subject);
        $this->email->email_content($content,"html");
        $this->email->send();
    }

}