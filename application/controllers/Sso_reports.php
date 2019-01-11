<?php
/**
 * Created by Astiostech.
 * User: hamid
 * Date: 1/3/2018
 * Time: 01:39 PM
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'third_party/Cli.php';

use Stunt\Console\Cli as Cli;

class Sso_reports extends CI_Controller {
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
            $this->cli->out("\n[white_bold]SSO Reports :[reset]\n")->new_line();
            $this->cli->command_help("AD_list_all","List all Active Directory Users.");
            $this->cli->command_help("AD_list_enabled","List all enabled Active Directory Users.");
            $this->cli->command_help("AD_list_disabled","List all disabled Active Directory Users.");
            $this->cli->command_help("AD_list_changed","List all changed Active Directory Users since last sync.");
            $this->cli->command_help("AD_staff/staffid|extension|mobile","Show Staff info.search by staffid,extension or mobile number.");
        }
        else
        {
            echo "it's not CLI.";
        }
    }

    public function AD_list_all()
    {
        $this->load->model('ActiveDirectory_model','AD');
        $list = $this->AD->get_all();

        foreach ($list as $staff)
        {
            $this->cli->print_staff($staff,array('displayname','exten'));
        }

    }


}