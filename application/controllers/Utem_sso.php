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

    public function AD_user($staff_id)
    {
        $this->load->model('ActiveDirectory_model','AD');
        $user = $this->AD->get_user($staff_id);


            $name=$user['displayname'];
            $staff_id=$user['samaccountname'];
            $this->cli->out("[green_bold]- $staff_id---[reset]\n");
            $this->cli->out("[white_bold]- $name [reset]")->new_line();

            printr_pre($user);
    }

    public function AD_camp_users($camp='main')
    {
        $this->load->model('ActiveDirectory_model','AD');
        $users = $this->AD->get_camp_users($camp);
        $this->cli->out("[green_bold]-users in $camp : [reset]\n");
        printr_pre($users);
    }

    public function freepbx_add_user($staff_id)
    {
        $this->load->helper('string');
        $this->load->helper('file');
        $this->load->helper('date');



        $this->load->model('ActiveDirectory_model', 'AD');
        $user = $this->AD->get_user($staff_id);

        //set variables
        $exten = $user['exten'];
        $did = $user['telephonenumber'];
        $camp = $user['camp'];
        $name = $user['displayname'];
        $discription = $user['displayname'];
        $voicemail_email = $user['mail'];
        $account = $user['samaccountname'];
        $mobile = $user['mobile'];
        $department = $user['department'];
        $secret = "ss$exten-$exten";
        $trunk_prefix = substr($exten, 0, 1);
        if($mobile == "" ) {
            $followme_list = $exten;
        }
        else
        {
            $followme_list = $exten . "-" . $trunk_prefix . $mobile . "#";
        }
        $followme_post_dest = "\"ext-local,$exten,dest\"";
        $voicemail_option = "attach=yes|saycid=yes|envelope=yes|delete=no";
        printr_pre($user);

//header for Exten csv file.
        $frpbx_exten_header =  "extension,password,name,voicemail,ringtimer,";
        $frpbx_exten_header .= "mohclass,id,tech,dial,devicetype,";
        $frpbx_exten_header .= "user,description,cid_masquerade,concurrency_limit,account,";
        $frpbx_exten_header .= "accountcode,allow,callerid,context,disallow,";
        $frpbx_exten_header .= "max_contacts,secret,sipdriver,callwaiting_enable,findmefollow_strategy,";
        $frpbx_exten_header .= "findmefollow_grptime,findmefollow_grppre,findmefollow_grplist,findmefollow_postdest,findmefollow_dring,findmefollow_needsconf,findmefollow_ringing,findmefollow_pre_ring,";
        $frpbx_exten_header .= "findmefollow_changecid,findmefollow_fixedcid,findmefollow_enabled,voicemail_enable,voicemail_vmpwd,";
        $frpbx_exten_header .= "voicemail_email,voicemail_options,voicemail_same_exten,disable_star_voicemail\n";
//header for DID csv file.
        $frpbx_did_header = "cidnum,extension,destination,description,mohclass\n";

        $frpbx_exten_string = $frpbx_exten_header ;
        $frpbx_did_string = $frpbx_did_header;


        $frpbx_exten_string .= "$exten,$exten,$name,default,0,default,$exten,pjsip,PJSIP/$exten,fixed,$exten,$name,";
        $frpbx_exten_string .= "$exten,6,$exten,$department,opus&ulaw&alaw&vp8,$name <$exten>,from-internal,all,6,$secret,";
        $frpbx_exten_string .= "chan_pjsip,ENABLED,ringallv2-prim,20,,$followme_list,$followme_post_dest,,,Ring,";
        $frpbx_exten_string .= "7,extern,$did,yes,yes,$exten,$voicemail_email,$voicemail_option,yes,yes";

        $frpbx_did_string .= ",$did,\"from-did-direct,$exten,1\",$discription,default\n";

        //check camp
        switch ($camp) {
            case 'main':
                break;
            case 'tech':
                break;
            case 'city':
                break;
            default:
                echo "unknown camp";
                return;
                break;
        }

        $m_date = mdate('%Y%m%d%h%i%a');
        $frpbx_extens_file = "/var/www/html/utem-sso/tmp/frpbx-exten-$account-$m_date.csv";
        $frpbx_dids_file = "/var/www/html/utem-sso/tmp/frpbx-did-$account-$m_date.csv";


        if (!write_file($frpbx_extens_file, $frpbx_exten_string)) {
            echo "error writing file frpbx_exten_file in $frpbx_extens_file";
            exit();
        }
        if (!write_file($frpbx_dids_file, $frpbx_did_string)) {
            echo "error writing file frpbx_did_file in $frpbx_dids_file";
            exit();
        }

        echo "freepbx extensions and dids files path:\n";
        echo "$frpbx_extens_file\n";
        echo "$frpbx_dids_file\n";

    }

    public function freepbx_add_camp($camp='main')
    {
        $this->load->helper('string');
        $this->load->helper('file');
        $this->load->helper('date');

//header for Exten csv file.
        $frpbx_exten_header  = "extension,password,name,voicemail,ringtimer,";
        $frpbx_exten_header .= "mohclass,id,tech,dial,devicetype,";
        $frpbx_exten_header .= "user,description,cid_masquerade,concurrency_limit,account,";
        $frpbx_exten_header .= "accountcode,allow,callerid,context,disallow,";
        $frpbx_exten_header .= "max_contacts,secret,sipdriver,callwaiting_enable,findmefollow_strategy,";
        $frpbx_exten_header .= "findmefollow_grptime,findmefollow_grppre,findmefollow_grplist,findmefollow_postdest,findmefollow_dring,findmefollow_needsconf,findmefollow_ringing,findmefollow_pre_ring,";
        $frpbx_exten_header .= "findmefollow_changecid,findmefollow_fixedcid,findmefollow_enabled,voicemail_enable,voicemail_vmpwd,";
        $frpbx_exten_header .= "voicemail_email,voicemail_options,voicemail_same_exten,disable_star_voicemail,vmx_unavail_enabled,vmx_busy_enabled,vmx_temp_enabled\n";
//header for DID csv file.
        $frpbx_did_header = "cidnum,extension,destination,description,mohclass\n";

        $frpbx_exten_string = $frpbx_exten_header;
        $frpbx_did_string = $frpbx_did_header;


        $this->load->model('ActiveDirectory_model','AD');
        $users = $this->AD->get_camp_users($camp);
        foreach ($users as $user) {
            //set variables
            $exten = $user['exten'];
            $did = $user['telephonenumber'];
            $camp = $user['camp'];
            $name = $user['displayname'];
            $discription = $user['displayname'];
            $voicemail_email = $user['mail'];
            $account = $user['samaccountname'];
            $mobile = $user['mobile'];
            $department = $user['department'];
            $secret = "ss$exten-$exten";
            $trunk_prefix = substr($exten, 0, 1);
            if ($mobile == "") {
                $followme_list = $exten;
            } else {
                $followme_list = $exten . "-" . $trunk_prefix . $mobile . "#";
            }
            $followme_post_dest = "\"ext-local,$exten,dest\"";
            $voicemail_option = "attach=yes|saycid=yes|envelope=yes|delete=no";
            printr_pre($user);

            $frpbx_exten_string .= "$exten,$exten,$name,default,0,default,$exten,pjsip,PJSIP/$exten,fixed,$exten,$name,";
            $frpbx_exten_string .= "$exten,6,$exten,$department,opus&ulaw&alaw&vp8,$name <$exten>,from-internal,all,6,$secret,";
            $frpbx_exten_string .= "chan_pjsip,ENABLED,ringallv2-prim,20,,$followme_list,$followme_post_dest,,,Ring,";
            $frpbx_exten_string .= "7,extern,$did,yes,yes,$exten,$voicemail_email,$voicemail_option,yes,yes,blocked,blocked,blocked\n";

            $frpbx_did_string .= ",$did,\"from-did-direct,$exten,1\",$discription,default\n";
        }
        //check camp


        $m_date = mdate('%Y%m%d%h%i%a');
        $frpbx_extens_file = "/var/www/html/utem-sso/tmp/frpbx-exten-$camp-$m_date.csv";
        $frpbx_dids_file = "/var/www/html/utem-sso/tmp/frpbx-did-$camp-$m_date.csv";


        if (!write_file($frpbx_extens_file, $frpbx_exten_string)) {
            echo "error writing file frpbx_exten_file in $frpbx_extens_file";
            exit();
        }
        if (!write_file($frpbx_dids_file, $frpbx_did_string)) {
            echo "error writing file frpbx_did_file in $frpbx_dids_file";
            exit();
        }

        echo "freepbx extensions and dids files path:\n";
        echo "$frpbx_extens_file\n";
        echo "$frpbx_dids_file\n";

    }

}