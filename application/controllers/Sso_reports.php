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
            $this->cli->out("[green_bold]SSO program help :.(".date('Y-m-d H:i:s').").[reset]\n")->new_line();
            $this->cli->out("[white_bold]AD_list_all: List all Active Directory Users. [reset]\n")->new_line();
            $this->cli->out("[white_bold]AD_list_enabled: List all enabled Active Directory Users. [reset]\n")->new_line();
            $this->cli->out("[white_bold]AD_list_disabled: List all disabled Active Directory Users. [reset]\n")->new_line();
            $this->cli->out("[white_bold]AD_list_changed: List all changed Active Directory Users since last sync. [reset]\n")->new_line();
            
        }
    }


}