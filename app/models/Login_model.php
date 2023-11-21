<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Login_model extends Model {

    public function getusers() {
        return $this->db->table('users')->get_all();
    }

    public function addUser($data)
    {
        return $this->db->table('users')->insert($data);
    }

    public function searchUser($id)
    {
        return $this->db->table('users')->where('id', $id)->get();
    }

    public function updateToken($id, $data) {
        return $this->db->table('users')->where('id', $id)->update($data);
    }
}
?>
