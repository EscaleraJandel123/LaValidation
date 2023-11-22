<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class MainController extends Controller {

    public function __construct(){
        parent::__construct();
        // $this->call->library('session');
        $this->getusers = $this->User_model->getusers();
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
    
    // public function logout() {
    //     $this->session->unset_userdata('userEmail');

    //     redirect('login');
    // }
    
    public function create() {
        $email = $this->io->post('email');
        $password = $this->io->post('password');
        $data = array(
            "email" => $email,
            "password"=> password_hash($password,PASSWORD_DEFAULT),
            "status" => "unverified",
        );
        $this->User_model->addUser($data);
        redirect('login');
    }
    public function auth() {
        $email = $this->io->post('email');
        $password = $this->io->post('password');
        
        $users = $this->getusers;
        foreach ($users as $user) {
            if ($email == $user['email']) {
                if (password_verify($password, $user['password'])) {
                    if($user['status'] == "unverified")
                    {
                        $recepient_email = $email;
                        $subject = "Email Verification";
                        $content = "click the link to verify <a href='" . site_url("pending") . "/" . $user['id'] . "'>Link</a>";
                        $this->sendVerify($recepient_email,$subject,$content);
                        
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

    public function pending($id)
    {
        $data = $this->User_model->searchUser($id);
        $data['status'] = "verified";
        if($data['status'] == "verified")
        {
            $this->User_model->updateToken($id,$data);
            $this->session->set_userdata('userEmail', $data['email']);
            $this->call->view('verified',$data);
        } else {
            $this->call->view('unverified');
        }
    }

    public function sendVerify($recepient_email,$subject,$content)
    {
        $this->email->sender('alejandrogino950@gmail.com', 'Lavalust Activity');
        $this->email->recipient($recepient_email);
        $this->email->subject($subject);
        $this->email->email_content($content,"html");
        $this->email->send();
    }

    Public function uploadFile(){
        $this->call->library('upload', $_FILES["fileToUpload"]);
		$this->upload
			->set_dir('public')
			->allowed_extensions(array('jpg'))
			->allowed_mimes(array('image/jpeg'))
			->is_image();
		If($this->upload->do_upload()) {
			$data['filename'] = $this->upload->get_filename();
            
            $email = $this->io->post('email');
            $name = $this->io->post('name');
            $subject = $this->io->post('subject');
            $content = $this->io->post('content');
            $path = "public/" . $this->upload->get_filename();
            $this->send($email, $name, $subject, $content, $path);
			echo 'success';
		} else {
			$data['errors'] = $this->upload->get_errors();
			$this->call->view('upload_form', $data);
		}
    }

    Public function send($email,$name,$subject,$content,$path)
    {
        $fullContent = "Hello, <br><br>This is a sample email.<br>These are the email's contents: <br>" . $content;
        $this->email->recipient($email);
        $this->email->sender($this->session->userdata('userEmail'), $name);

        $this->email->subject($subject);
        $this->email->email_content($fullContent,'html');
        $this->email->attachment($path);
        $this->email->send();
    }
}

