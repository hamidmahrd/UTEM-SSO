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

    public function freepbx_add_user($staff_id)
    {
        $this->load->helper('string');
        $this->load->helper('file');
        $this->load->helper('date');

//header for Exten csv file.
        $frpbx_extens_header  = "extension,password,name,voicemail,ringtimer,";
        $frpbx_extens_header .= "mohclass,id,tech,dial,devicetype,";
        $frpbx_extens_header .= "user,description,cid_masquerade,concurrency_limit,account,";
        $frpbx_extens_header .= "accountcode,allow,callerid,context,disallow,";
        $frpbx_extens_header .= "max_contacts,secret,sipdriver,callwaiting_enable,findmefollow_strategy,";
        $frpbx_extens_header .= "findmefollow_grptime,findmefollow_grplist,findmefollow_postdest,findmefollow_ringing,findmefollow_pre_ring,";
        $frpbx_extens_header .= "findmefollow_changecid,findmefollow_fixedcid,findmefollow_enabled,voicemail_enable,voicemail_vmpwd,";
        $frpbx_extens_header .= "voicemail_email,voicemail_options,voicemail_same_exten,disable_star_voicemail\n";

//header for DID csv file.
        $frpbx_dids_header = "cidnum,extension,destination,description,mohclass\n";


        $m_date = mdate('%Y%m%d%h%i%a');
        $frpbx_extens_main_file = './tmp/frpbx_exten_main_' . $m_date . '.csv';
        $frpbx_dids_main_file = './tmp/frpbx_did_main_' . $m_date . '.csv';

        $frpbx_extens_tech_file = './tmp/frpbx_exten_tech_' . $m_date . '.csv';
        $frpbx_dids_tech_file = './tmp/frpbx_did_tech_' . $m_date . '.csv';

        $frpbx_extens_city_file = './tmp/frpbx_exten_city_' . $m_date . '.csv';
        $frpbx_dids_city_file = './tmp/frpbx_did_city_' . $m_date . '.csv';


        $frpbx_extens_main_string = $frpbx_extens_header;
        $frpbx_dids_main_string = $frpbx_dids_header;

        $frpbx_extens_tech_string = $frpbx_extens_header;
        $frpbx_dids_tech_string = $frpbx_dids_header;

        $frpbx_extens_city_string = $frpbx_extens_header;
        $frpbx_dids_city_string = $frpbx_dids_header;



        $this->load->model('ActiveDirectory_model','AD');
        $user = $this->AD->get_user($staff_id);

        //set variables
        $exten = $user['exten'];
        $did = $user['telephonenumber'];
        $camp = $user['camp'];
        $name = $user['displayname'];
        $discription = $user['displayname'];
        $voicemail_email = $user['mail'];
        $account= $user['samaccountname'];
        $mobile = $user['mobile'];
        $department = $user['department'];
        $secret = "ss$exten-$exten";
        $trunk_prefix = substr($exten,0,1);
        $followme_list = $exten . "-" . $trunk_prefix . $mobile . "#";
        $followme_post_dest = "ext-local,$exten,dest";
        $voicemail_option = "attach=yes|saycid=yes|envelope=yes|delete=no";
        //check camp
        switch($camp) {
            case 'main':
                $frpbx_extens_main_string .= "$exten,$exten,$name,default,0,default,$exten,pjsip,PJSIP/$exten,fixed,$exten,$name,";
                $frpbx_extens_main_string .= "$exten,6,$exten,$department,opus&ulaw&alaw&vp8,$name,from-internal,all,6,$secret,";
                $frpbx_extens_main_string .= "chan_pjsip,ENABLED,ringallv2-prim,20,$followme_list,$followme_post_dest,Ring,";
                $frpbx_extens_main_string .= "7,extern,$did,yes,yes,$exten,$voicemail_email,$voicemail_option,yes,yes";

                $frpbx_dids_main_string .= ",$did,\"from-did-direct,$exten,1\",$discription,default\n";

                break;
            case 'tech':
                $frpbx_extens_tech_string .= "$exten,$exten,$name,default,0,default,$exten,pjsip,PJSIP/$exten,fixed,$exten,$name,";
                $frpbx_extens_tech_string .= "$exten,6,$exten,$department,opus&ulaw&alaw&vp8,$name,from-internal,all,6,$secret,";
                $frpbx_extens_tech_string .= "chan_pjsip,ENABLED,ringallv2-prim,20,$followme_list,$followme_post_dest,Ring,";
                $frpbx_extens_tech_string .= "7,extern,$did,yes,yes,$exten,$voicemail_email,$voicemail_option,yes,yes";

                $frpbx_dids_tech_string .= ",$did,\"from-did-direct,$exten,1\",$discription,default\n";

                break;
            case 'city':
                $frpbx_extens_city_string .= "$exten,$exten,$name,default,0,default,$exten,pjsip,PJSIP/$exten,fixed,$exten,$name,";
                $frpbx_extens_city_string .= "$exten,6,$exten,$department,opus&ulaw&alaw&vp8,$name,from-internal,all,6,$secret,";
                $frpbx_extens_city_string .= "chan_pjsip,ENABLED,ringallv2-prim,20,$followme_list,$followme_post_dest,Ring,";
                $frpbx_extens_city_string .= "7,extern,$did,yes,yes,$exten,$voicemail_email,$voicemail_option,yes,yes";

                $frpbx_dids_city_string .= ",$did,\"from-did-direct,$exten,1\",$discription,default\n";

                break;
            default:
                echo "unknown camp";
                return;
                break;

                if ( ! write_file($frpbx_extens_main_file,$frpbx_extens_main_string)){
                    echo "error writing file frpbx_exten_file in $frpbx_extens_main_file";
                    exit();
                }
                if ( ! write_file($frpbx_dids_main_file,$frpbx_dids_main_string)){
                    echo "error writing file frpbx_did_file in $frpbx_dids_main_file";
                    exit();
                }
                if ( ! write_file($frpbx_extens_tech_file,$frpbx_extens_tech_string)){
                    echo "error writing file frpbx_exten_file in $frpbx_extens_tech_file";
                    exit();
                }
                if ( ! write_file($frpbx_dids_tech_file,$frpbx_dids_tech_string)){
                    echo "error writing file frpbx_did_file in $frpbx_dids_tech_file";
                    exit();
                }
                if ( ! write_file($frpbx_extens_city_file,$frpbx_extens_city_string)){
                    echo "error writing file frpbx_exten_file in $frpbx_extens_city_file";
                    exit();
                }
                if ( ! write_file($frpbx_dids_city_file,$frpbx_dids_city_string)){
                    echo "error writing file frpbx_did_file in $frpbx_dids_city_file";
                    exit();
                }

                $res1 = array();
                $res2 = array();

                echo "freepbx main and did files path";
                echo "/var/www/html/utem-sso/$frpbx_extens_main_file\n";
                echo "/var/www/html/utem-sso/$frpbx_dids_main_file\n";

        }

    }

}