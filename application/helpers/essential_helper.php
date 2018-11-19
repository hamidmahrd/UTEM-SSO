<?php   if( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * General helper
 *
 * @category	Helpers
 * @author      Ahmad Samiei  ahmad.samiei@gmail.com
 * @copyright   mobinone.com
 */


// ------------------------------------------------------------------------


/**
 * Get selected language
 *
 * @return	string
 */
function lang_name()
{
    return 'persian';
}

// ------------------------------------------------------------------------
/**
 * Print_r in readble form
 * @return viod
 */
function printr_exit($data) {
	error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
	ini_set("display_errors", 1);
	    echo "<pre>";
	print_r($data);
	    echo "</pre>";
	exit();
}
//-------------------------------------------------------------------------
/**
 * Print_r in readble form
 * @return viod
 */
function printr_pre($data) {
	error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
	ini_set("display_errors", 1);
	 if(!is_cli())
	     echo "<pre>";
	print_r($data);
	if (!is_cli())
	    echo "</pre>";

}

if ( ! function_exists('is_cli')) {
    function is_cli()
    {
        echo php_sapi_name();
        return php_sapi_name() === 'cli';
    }

}



//-----------------------------------------
/**
 * Get salt key
 *
 * @return	string
 */
function salt()
{
	return config_item('salt');

    /*$ci =& get_instance(); return $ci->config->item('salt');*/
}

// ------------------------------------------------------------------------

/**
 * Create hash with salt key
 *
 * @param	string 	$string
 * @return	string
 */
function generate_hash($string=NULL)
{
	if( ! empty($string))
	{
		if(strlen($string > 20))
		{
			$string = substr($string, 0,20);
		}

		return sha1($string . salt());
	}

	return FALSE;
}

// ------------------------------------------------------------------------

/**
 * Get selected theme name
 *
 * @return	string
 */
function theme()
{
    return strtolower(config_item('theme'));
}

// ------------------------------------------------------------------------

/**
 * Get selected theme url
 *
 * @return	string
 */
function theme_path()
{
    return base_url() . 'assets/' . strtolower(config_item('theme')) . '/';
}

// ------------------------------------------------------------------------

/**
 * Pad string
 *
 * ability to add left 0 to numbers
 *
 * @param	string	$str 	input
 * @param	integer	$length lenght
 * @return	string
 */
function pad($str, $length=2)
{
	return str_pad(substr($str, 0, $length), $length, 0, STR_PAD_LEFT);
}

// ------------------------------------------------------------------------

/**
 * Detect ajax calls
 *
 * @return	boolean
 */
function is_ajax()
{
	return ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
}

// ------------------------------------------------------------------------

/**
 * Validate string with persian-apa character
 *
 * @param	string 	$str
 * @return	boolean
 */
function persian_string($str)
{
	$check = preg_replace("/[^0-9a-zA-Z ائبپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیآءأ]/", "", $str);

	if($str != $check)
	{
		return FALSE;
	}

	return TRUE;
}

// ------------------------------------------------------------------------

/**
 * Validate referer on post request
 *
 * redirect on remote request for ignore non-planed apps
 *
 * @return	redirect
 */
function check_referer()
{
	$ci =& get_instance();
	if(strtolower($ci->input->server('REQUEST_METHOD')) == 'post' && ! strpos(strtolower($ci->input->server('HTTP_REFERER')), strtolower($ci->input->server('SERVER_NAME')))) {
		redirect('message/error/2');
	}
}

// ------------------------------------------------------------------------

/**
 * Calculate diff days between two time
 *
 * @param	timestamp 	$date
 * @return	integer
 */
function day_diff($date)
{
	return (int) floor((time() - strtotime($date)) / 60 / 60);
}

// ------------------------------------------------------------------------

/**
 * Detect mobile device
 *
 * @return	boolean
 */
function is_mobile_device()
{
	$hystack = array('ipod', 'iphone', 'ipad', 'mobile', 'android', 'webos', 'blackberry', 'rim tablet');
	foreach($hystack as $check)
	{
		if(stripos(strtolower($_SERVER['HTTP_USER_AGENT']),$check))
		{
			return TRUE;
		}
	}

	return FALSE;
}

// ------------------------------------------------------------------------

/**
 * Phone number purifier
 *
 * @param	int	config key
 * @return	string
 */
function mobile_purifier($number)
{
	$country = '98';

	$number = (string) $number;
	$number = preg_replace('[\D]', '', $number);

	if (strpos($number, '+') !== FALSE && strpos($number, '+') >= 0)
	{
		$number = str_replace('+', '00', $number);
	}

	if (strpos($number, $country) !== FALSE && strpos($number, $country) >= 0)
	{
		$number = str_replace($country, '0', $number);
	}

	$number = preg_replace('/09/', '989', $number, 1);
	$number = preg_replace('/00989/', '989', $number, 1);


	return $number;
}

function mobile_print($number)
{
	$country = '98';

	$number = preg_replace('/989/', '09', $number, 1);
	return $number;
}

function is_mci($number)
{
	$number = mobile_purifier($number);

	if (strpos($number, '9891') !== FALSE && strpos($number, '9891') == 0)
	{
		return $number;
	}

	return false;
}

/**
 * Limit array keys
 *
 * @param 	array 	$post
 * @param 	array 	$hystack
 * @return 	array
 */
function array_limit_keys($data, $hystack)
{
	foreach ($data as $key => $value)
	{
		if ( ! in_array($key, $hystack))
		{
			unset($data[$key]);
		}
	}

	return $data;
}

// ------------------------------------------------------------------------

/**
 * Array extend
 *
 * @param 	array 	$array1 	priority
 * @param 	array 	$array2
 * @return 	array
 */
function array_extend(array $array1 = array(), array $array2 = array())
{
    foreach ($array1 as $key => $value) {
        if (isset($array2[$key])) {
            $array1[$key] = $array2[$key];
        }
    }

    return $array1;
}

// ------------------------------------------------------------------------

if ( ! function_exists('word_filter'))
{
	function word_filter($str)
	{
	    $lang['filtered_word'] = array();

		//Female
		$lang['filtered_word'][] = "کس کش";
		$lang['filtered_word'][] = "کوستلا";
		$lang['filtered_word'][] = "کوسخلا";
		$lang['filtered_word'][] = " کوس ";
		$lang['filtered_word'][] = "جنده";
		$lang['filtered_word'][] = "فاحشه";
		$lang['filtered_word'][] = "روسپی";
		$lang['filtered_word'][] = "پستون";
		$lang['filtered_word'][] = "پستان";
		$lang['filtered_word'][] = "ممه";
		$lang['filtered_word'][] = "لاشی";
		$lang['filtered_word'][] = "کسده";
		$lang['filtered_word'][] = "کس ده";
		$lang['filtered_word'][] = "کوس ده";
		$lang['filtered_word'][] = "کوسده";
		$lang['filtered_word'][] = "کرست";
		$lang['filtered_word'][] = "سوتین";
		$lang['filtered_word'][] = "بیکینی";
		$lang['filtered_word'][] = "پریود";
		$lang['filtered_word'][] = "هرزه";
		$lang['filtered_word'][] = "چوچول";

		//Male
		$lang['filtered_word'][] = "دودول";
		$lang['filtered_word'][] = "شمبول";
		$lang['filtered_word'][] = "شونبول";
		$lang['filtered_word'][] = "شنبول";
		$lang['filtered_word'][] = "شومبول";
		$lang['filtered_word'][] = "کیر";
		$lang['filtered_word'][] = "جاکش";
		$lang['filtered_word'][] = "کسلیس";
		$lang['filtered_word'][] = "کس لیس";
		$lang['filtered_word'][] = "کوس لیس";
		$lang['filtered_word'][] = "خایه";
		$lang['filtered_word'][] = "بیضه";
		$lang['filtered_word'][] = "تخم";
		$lang['filtered_word'][] = "بی ناموس";
		$lang['filtered_word'][] = "جقی";
		$lang['filtered_word'][] = "اسپرم";
		$lang['filtered_word'][] = "کاندوم";
		$lang['filtered_word'][] = "کاندم";
		$lang['filtered_word'][] = "کس خل";
		$lang['filtered_word'][] = "کسخل";
		$lang['filtered_word'][] = "کوس خل";
		$lang['filtered_word'][] = "کوسخل";
		$lang['filtered_word'][] = "کون کش";
		$lang['filtered_word'][] = "کونکش";

		//Bisexual
		$lang['filtered_word'][] = "کون";

		//Swear
		$lang['filtered_word'][] = "گایید";
		$lang['filtered_word'][] = "کونی";
		$lang['filtered_word'][] = "سکس";
		$lang['filtered_word'][] = "نمود";
		$lang['filtered_word'][] = "سپوخت";
		$lang['filtered_word'][] = "پدر سگ";
		$lang['filtered_word'][] = "قحبه";
		$lang['filtered_word'][] = "سگ پدر";
		$lang['filtered_word'][] = "مادر به خطا";
		$lang['filtered_word'][] = "عنتر";
		$lang['filtered_word'][] = "انتر";
		$lang['filtered_word'][] = "بی شرف";
		$lang['filtered_word'][] = "بیشرف";
		$lang['filtered_word'][] = "بی پدر";
		$lang['filtered_word'][] = "شاشو";
		$lang['filtered_word'][] = "گوز";
		$lang['filtered_word'][] = "حرومزاده";
		$lang['filtered_word'][] = "حرامزاده";
		$lang['filtered_word'][] = "کره خر";
		$lang['filtered_word'][] = "الاغ";

		//Political
		$lang['filtered_word'][] = "احمدی نژاد";
		$lang['filtered_word'][] = "احمدینژاد";
		$lang['filtered_word'][] = "موسوی";
		$lang['filtered_word'][] = "کروبی";
		$lang['filtered_word'][] = "خاتمی";
		$lang['filtered_word'][] = "خمینی";
		$lang['filtered_word'][] = "رهبر";
		$lang['filtered_word'][] = "مرگ بر";
		$lang['filtered_word'][] = "منافق";
		$lang['filtered_word'][] = "اسلام";
		$lang['filtered_word'][] = "خامنه";
		$lang['filtered_word'][] = "رفسنجانی";
		$lang['filtered_word'][] = "ولی فقیه";

		/*$lang['filtered_word'][] = "www.";
		$lang['filtered_word'][] = "http://";
		$lang['filtered_word'][] = ".com";
		$lang['filtered_word'][] = ".net";
		$lang['filtered_word'][] = ".ir";
		$lang['filtered_word'][] = ".org";
		$lang['filtered_word'][] = ".ws";
		$lang['filtered_word'][] = ".name";*/

		foreach($lang['filtered_word'] as $x => $k) {
		    if(mb_strpos($str, $k) !== false) {
		        return false;
		    }
		}
		return $str;
	}
}

// ------------------------------------------------------------------------

function fancy_date($date)
{
    if (empty($date)) {
        return null;
    }

    $start = new DateTime($date);
    $end  = new DateTime(date('Y-m-d H:i:s'));
    $diff = time() - strtotime($date);
    $since = $start->diff($end);

    if ($since->d > 5) {
        if ($since->days > 300) {
            return jdate('d B y H:i',0 ,strtotime($date));
        }
        return jdate('d B H:i',0 ,strtotime($date));
    }

    $hl = $since->h + ($since->d*24);

    if ($hl == 0) {
        if ($diff < 60) {
            return $diff.' ثانیه پیش';
        }

        $diff = round($diff / 60);

        return $diff.' دقیقه پیش';
    }

    if ($hl < 3) {
        return $hl.' ساعت پیش';
    }

    if ($hl > date('H') && $hl < 24+date('H')) {
        return jdate('دیروز H:i',0 ,strtotime($date));
    }

    if ($hl <= date('H')) {
        return jdate('امروز H:i',0 ,strtotime($date));
    }

    return jdate('l H:i',0 ,strtotime($date));
}

function fancy_duration($sec)
{
	$min = $sec / 60;
	$hour = $min / 60;

	if ($min >= 60) {
		//$hour = $min / 60;
	}

	if ($sec < 60) {
		return $sec.'s';
	}

    if ($hour > 24) {

        return (integer) ($hour/24).'d ';//fancy_duration($hour % 60)
    }

	if ($hour > 1) {
		
		return '+'.(integer) $hour.'h ';//fancy_duration($hour % 60)
	}

	$int_min = (integer) $min;

	$sec = $sec - ($int_min * 60);

	return (integer) $min.'m '.$sec.'s';
}

function fancy_dduration($from, $to)
{
	return fancy_duration($to - $from);
}

// ------------------------------------------------------------------------


if ( ! function_exists('force_character_limiter'))
{
    function force_character_limiter($str, $n = 500, $end_char = '&#8230;')
    {
        if (strlen($str) < $n)
        {
            return $str;
        }

        return substr($str, 0, $n).$end_char;
    }
}


function yield_nav_count($count, $icon='bell')
{
	$extend = '<li class="nav-notify"><a href="events" data-original-title="تقویم کاری" data-toggle="tooltip" data-placement="top" title=""><span class="glyphicon glyphicon-calendar"></span></a></li>';
	if (empty($count)) {
		return '<li class="nav-notify"><a href="notifications" data-original-title="اعلان ها" data-toggle="tooltip" data-placement="top" title=""><span class="glyphicon glyphicon-bell"></span></a></li>'.$extend;
	}
	return '<li class="nav-notify"><a href="notifications" data-container="body" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="شما '.$count.' اعلان جدید دارید"><span class="glyphicon glyphicon-bell"></span> <span class="label label-danger">'.$count.'</span></a></li>'.$extend;
}


function array_append($array1, $array2) 
{
	foreach ($array2 as $value) {
		array_push($array1, $value);
	}
	
	return $array1;
}

function object_column($array_of_object, $parameter)
{
    $result = [];
    foreach ($array_of_object as $value) {
        if (isset($value->$parameter)) {
            $result[] = $value->$parameter;
        }
    }
    return $result;
}


function reindex_by($array, $key)
{
    $result = [];

    if (empty($array)) {
        return $result;
    }

    foreach ($array as $value) {
        if (is_array($value)) {
            $result[$value[$key]] = $value;
        } else {
            $result[$value->$key] = $value;
        }
    }
    return $result;
}


if ( ! function_exists('array_reindex'))
{
    /**
     * Reindex array by specific parameter of values
     *
     * @param array $array
     * @param string $key
     * @return array
     */
    function array_reindex($array, $key)
    {
        $result = array();

        foreach ($array as $value)
        {
            if (is_array($value))
            {
                $result[$value[$key]] = $value;
            }
            else if (is_object($value))
            {
                $result[$value->$key] = $value;
            }
            else
            {
                throw new InvalidArgumentException("Multi-dimensional array or collection of objects  allowed.");
            }
        }

        return $result;
    }
}


function or_dash($value, $dash='-')
{
    if (empty($value)) return $dash;

    return $value;
}

function array_flip_to($input){
    $output = array();
    foreach ($input as $key => $value) {
        $output[$value] = $key;
    }
    return $output;
}

function hasRole($resource='')
{
    static $user = '';
    if (empty($user)) {
        $ci =& get_instance();
        $user = $ci->auth->isUser(true);
    }

    if ($user->role_id == 1) {
        return true;
    }

    if (in_array($resource, $user->role_resource)) {
        return true;
    }

    return false;
}

function hasAllRoles($resources=[])
{
    foreach ($resources as $resource) {
        if (!hasRole($resource)) {
            return false;
        }
    }
    return true;
}

function touchRoles($resources=[])
{
    foreach ($resources as $resource) {
        if (hasRole($resource)) {
            return true;
        }
    }
    return false;
}

function shouldPermit($resource, $redirect_to='account')
{
    if (!is_array($resource) && hasRole($resource) === false) {
        redirect($redirect_to);
    }
    if (is_array($resource) && hasAllRoles($resource) === false) {
        redirect($redirect_to);
    }
    return;
}

function shallPermit($resource=array(), $redirect_to='account')
{
    if (is_array($resource) && !touchRoles($resource)) {
        redirect($redirect_to);
    }
    return;
}


function average($arr)
{
    if (!count($arr)) return 0;

    $sum = 0;
    for ($i = 0; $i < count($arr); $i++)
    {
        $sum += $arr[$i];
    }

    return $sum / count($arr);
}

function variance($arr)
{
    if (!count($arr)) return 0;

    $mean = average($arr);

    $sos = 0;    // Sum of squares
    for ($i = 0; $i < count($arr); $i++)
    {
        $sos += ($arr[$i] - $mean) * ($arr[$i] - $mean);
    }

    return $sos / (count($arr)-1);  // denominator = n-1; i.e. estimating based on sample
    // n-1 is also what MS Excel takes by default in the
    // VAR function
}

function generate_invite($data)
{
	date_default_timezone_set('Asia/Tehran');
return "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
METHOD:PUBLISH
ORGANIZER;RSVP=TRUE;CN=".$data['from'].";PARTSTAT=ACCEPTED;ROLE=CHAIR:mailto:".$data['from_email']."
DTEND:".gmdate('Ymd\THis\Z', ($data['end']))."
UID:".uniqid()."
DTSTAMP:".gmdate('Ymd\THis\Z', time())."
LOCATION:Rightel HQ QC
SUMMARY:".preg_replace('/([\,;])/','\\\$1', $data['title'])."
DTSTART:".gmdate('Ymd\THis\Z', ($data['start']))."
END:VEVENT
END:VCALENDAR";
}

function convert_to_csv($input_array, $output_file_name, $delimiter)
{
	/** open raw memory as file, no need for temp files */
	$temp_memory = fopen('php://memory', 'w');
	/** loop through array */
	foreach ($input_array as $line) {
		/** default php csv handler **/
		fputcsv($temp_memory, $line, $delimiter);
	}
	/** rewrind the "file" with the csv lines **/
	fseek($temp_memory, 0);
	/** modify header to be downloadable csv file **/
	header('Content-Type: application/csv');
	header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
	/** Send file to browser for download */
	fpassthru($temp_memory);
}