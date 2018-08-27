<?php
/**
 * Created by Hamid.
 * User:
 * Date: 19/01/2018
 * Time: 12:21 PM
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'third_party/Cli.php';

use Stunt\Console\Cli as Cli;

class Utem_sso extends CI_Controller {
    public $cli;

    public function __construct()
    {
        parent::__construct();

        set_time_limit(0);
        $this->cli = new Cli('', '', array());

    }

    public function index()
    {
        if(is_cli())
        {
            $this->cli->out("[green_bold]SSO program help :.(".date('Y-m-d H:i:s').").[reset]\n")->new_line();
            $this->cli->out("[white_bold]- leech : get all ldap users.[reset]\n")->new_line();
            $this->cli->out("[white_bold]- insert_new_frpbx/id : insert id to freepbx [reset]\n")->new_line();
        }
    }

    public function show_all_ad_users()
    {
        $this->load->model('ActiveDirectory_model','AD');
        $all_users = $this->AD->get_all();
        $counter=0;
        foreach($all_users as $user)
        {$counter++;
            $name=$user['displayname'];
            $staff_id=$user['samaccountname'];
            $this->cli->out("[green_bold]- $counter) ");
            $this->cli->out("[green_bold]- $staff_id---");
            $this->cli->out("[white_bold]- $name")->new_line();
        }
        $this->cli->out("Total Users : $counter")->new_line();
    }

}