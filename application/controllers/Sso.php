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

class Sso extends CI_Controller {
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

    public function AD_user($staff_id=null)
    {
        echo "get user(s) $staff_id from AD";

        $this->load->model('activeDirectory_model','ad_model');
        $items = $this->ad_model->get_item($staff_id);

        printr_pre($items);
    }
    //leech data from ldap and put in database.
    public function leech()
    {
        echo "leech from ldap";

        $this->load->model('ldap_model');
        $ldap_all = $this->ldap_model->getLdapUsers();

        printr_pre($ldap_all['new']);


    }
    public function leech2()
    {
        echo "leech from ldap";

        $this->load->model('ldap_model');
        $ldap_all = $this->ldap_model->leech();

        printr_pre($ldap_all);


    }

    public function insert_new_all()
    {
        $this->load->model('ldap_model');
        $ldap_all = $this->ldap_model->getLdapUsers();
        $new_items = $ldap_all['new'];
        $items = array_slice($new_items,0,5);
        $this->ldap_model->freepbx_new_item($new_items);

    }

    public function insert_newfrpbx_item($ad_user)
    {
        $this->load->model('ldap_model');
        $ldap_all = $this->ldap_model->getLdapUsers();
        $new_items = $ldap_all['new'];
        foreach($new_items as $new_item) {
            if ($new_item['samaccountname'] == $ad_user)
                $items[0] = $new_item;
        }
        $this->ldap_model->freepbx_new_item($items);

    }

    public function insert_new_stretto()
    {
        $this->load->model('ldap_model');
        $ldap_all = $this->ldap_model->getLdapUsers();
        $new_items = $ldap_all['new'];
        $items = array_slice($new_items,0,5);
        $this->ldap_model->stretto_new_item($new_items);

    }
    public function insert_new_stretto_item($ad_user)
    {
        $this->load->model('ldap_model');
        $ldap_all = $this->ldap_model->getLdapUsers();
        $new_items = $ldap_all['new'];
        foreach($new_items as $new_item) {
            if ($new_item['samaccountname'] == $ad_user)
                $items[0] = $new_item;
        }
        printr_pre($items);
        $this->ldap_model->stretto_new_item($items);

    }
    public function insert_newusers_chat()
    {
        $this->load->model('ldap_model');
        $ldap_all = $this->ldap_model->getLdapUsers();
        $new_items = $ldap_all['new'];
        $items = array_slice($new_items,0,5);
        foreach($new_items as $item)
        {
                $this->ldap_model->insertOpenfire($item);
        }
    }
    public function insert_newusers_chat_item($ad_user)
    {
        $this->load->model('ldap_model');
        $ldap_all = $this->ldap_model->getLdapUsers();
        $new_items = $ldap_all['new'];
        $items = array_slice($new_items,0,5);
        foreach($new_items as $item)
        {
            if ($item['samaccountname'] == $ad_user) {
                $this->ldap_model->insertOpenfire($item);
            }
        }
    }

    public function get_chat_groups()
    {
        echo "openfire chat groups";

        $api = new Gidkom\OpenFireRestApi\OpenFireRestApi;
        $api->secret = "6pd3ThrTsJQManie";

        $api->host = "chat.connect.utem.edu.my";
        $api->port = "9090";  // default 9090



        $group = $api->getGroup('utem');
        $grp [] = 'utem';
        printr_pre($grp);
        printr_pre($group);

    }

    public function UpdateChat()
    {
        echo "get update";
        $user = 'uc1';
        $name = 'uc1';
        $email = 'uc1@mid.com';
        $password = "ss1001-1001";

        $api = new Gidkom\OpenFireRestApi\OpenFireRestApi;
        $api->secret = "6pd3ThrTsJQManie";

        $api->host = "chat.connect.utem.edu.my";
        $api->port = "9090";  // default 9090
        $result = $api->updateUser($user, $password, $name, $email,'utem');
        printr_pre($result);
    }

    public function get_users()
    {
        echo "openfire insert";

        $api = new Gidkom\OpenFireRestApi\OpenFireRestApi;
        $api->secret = "6pd3ThrTsJQManie";

        $api->host = "chat.connect.utem.edu.my";
        $api->port = "9090";  // default 9090

        $users = $api->getUsers();

        printr_pre($users);

    }

    public function get_user($user)
    {
        echo "openfire user : $user";

        $api = new Gidkom\OpenFireRestApi\OpenFireRestApi;
        $api->secret = "6pd3ThrTsJQManie";

        $api->host = "chat.connect.utem.edu.my";
        $api->port = "9090";  // default 9090

        $res = $api->getUser($user);

        printr_pre($res);

    }

    public function make_directory()
    {
        $this->load->helper('string');
        $this->load->helper('file');
        $this->load->helper('date');

        $m_date = mdate('%Y%m%d%h%i%a');
        $directory_file = './tmp/directory_' . $m_date . '.csv';
        //$directory_header = "business_number,categories,default_address,default_address_comm,,default_address_type,display-name,email_address,is_favorite,pres_subscription,sip_address,xmpp_address,\n";

        $directory_header = "business_number,categories,display-name,is_favorite,pres_subscription,sip_address\n";

        $directory_string = $directory_header;
        $this->load->model('ldap_model');
        $ldap_all = $this->ldap_model->getLdapUsers();

        $new_users = $ldap_all['new'];

        foreach($new_users as $new_item)
        {
            $exten = $new_item['exten'];
            $did = $new_item['telephonenumber'];
            $camp = $new_item['camp'];
            $name = $new_item['displayname'];

            $directory_string .= "\"$did\",\"$camp\",\"$name\",\"FALSE\",\"FALSE\",\"sip:$exten@$camp.connect.utem.edu.my:5061\"\n";
        }

        printr_pre($directory_string);

        if ( ! write_file($directory_file,$directory_string)){
            echo "error writing file frpbx_exten_file in $directory_file";
            exit();
        }
        redirect("main.connect.utem.edu.my/sso/$directory_file");

       //$this->download_csv($directory_string,"Directory.csv");


    }

    public function download_csv($data,$filename)
    {
        $this->load->helper('download');
        //$data = 'Here is some text!';
        //$name = 'mytext.txt';
        echo $data;
        echo $filename;
        force_download($filename, $data);
    }
}