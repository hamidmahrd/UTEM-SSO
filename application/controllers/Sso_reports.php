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
            $this->cli->out("\n[green_bold]SSO Reports :[reset]\n")->new_line();
            $this->cli->out("[green_bold]AD_list_all: [white_bold]List all Active Directory Users. [reset]\n")->new_line();
            $this->cli->out("[green_bold]AD_list_enabled:[white_bold] List all enabled Active Directory Users. [reset]\n")->new_line();
            $this->cli->out("[green_bold]AD_list_disabled:[white_bold] List all disabled Active Directory Users. [reset]\n")->new_line();
            $this->cli->out("[green_bold]AD_list_changed:[white_bold] List all changed Active Directory Users since last sync. [reset]\n")->new_line();
        }
    }


}