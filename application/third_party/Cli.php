<?php
namespace Stunt\Console;

/**
 * Cli console library for php
 * My handy console library
 *
 * @author 	Ahmad Samiei <ahmad.samiei@gmail.com>
 * @copyright 	http://samiei.org
 */
Class Cli
{
	public $title;
    public $description;
    protected $manual = '';
    protected $routes = array();
    protected $short_route_map = array();
    protected $request;
    protected $params = array();
    protected $hook_call;
    protected $is_hook = false;
    protected $hook_manual = null;
	public $wait_msg = 'Press any key to continue...';
    protected $styles = array(
        //Foreground color options
        'black' => "\033[0;30m",
        'black_bold' => "\033[1;30m",
        'red' => "\033[0;31m",
        'red_bold' => "\033[1;31m",
        'green' => "\033[0;32m",
        'green_bold' => "\033[1;32m",
        'brown' => "\033[0;33m",
        'yellow' => "\033[1;33m",
        'blue' => "\033[0;34m",
        'blue_bold' => "\033[1;34m",
        'purple' => "\033[0;35m",
        'purple_bold' => "\033[1;35m",
        'cyan' => "\033[0;36m",
        'cyan_bold' => "\033[1;36m",
        'white' => "\033[0;37m",
        'white_bold' => "\033[1;37m",
        //Background color options
        'black_bg' => "\033[40m",
        'red_bg' => "\033[41m",
        'green_bg' => "\033[42m",
        'yellow_bg' => "\033[43m",
        'blue_bg' => "\033[44m",
        'magenta_bg' => "\033[45m",
        'cyan_bg' => "\033[46m",
        'light_gray_bg' => "\033[47m",
        //Text reset
        'bold' => "\033[1m",
        'reset' => "\033[0m",
        'newline' => "\n"
    );

    /**
     * Object constructor
     *
     * @param 	string 	$title 	Command title
     * @param 	string  	$desciption 	Command short info
     * @param 	string 	$argv 	Cli passed arguments
     * @return 	void
     */
    public function __construct($title='', $description='', $argv=null)
    {
    	$this->title = $title;
    	$this->description = $description;


    	if (!empty($argv)) {
    		array_shift($argv);
    	}

    	$this->request = $argv;
    }

    /**
     * Print styled output with bbcode like syntax
     *
     * @param 	string 	$text
     * @return 	object 	self
     */
    public function out($text='')
    {
    	$names = implode('|', array_keys($this->styles));
    	preg_match_all("#(\[({$names})\])#i", $text, $matches);

	    $replacements = $matches[1];
	    $selections = $matches[2];

	    if(!empty($replacements) && !empty($selections))
	    {
	        $replacements = array_unique($replacements);
	        $selections = array_unique($selections);

	        foreach($replacements as $index => $replacement)
	        {
	            $color = strtolower($selections[$index]);
	            if(array_key_exists($color, $this->styles))
	                $text = str_ireplace($replacement, $this->styles[$color], $text);
	        }
	    }

    	echo $text;

    	return $this;
    }

    /**
     * Router
     *
     * @param 	string 		$long 	 long keyword
     * @param 	callback 	$func 	 callback function
     * @param 	string 		$comment swtch comment used for manual
     * @param 	string 		$short 	 short keyword
     * @return 	object 		return self object for chainable usage
     */
    public function get($long, $func, $comment=null, $short=null)
    {
    	if ( ! empty($long)) {
    		$long = '--'.$long;
    	}

        $this->routes[$long] = array(
        	'long' => $long,
        	'callback' => $func,
        	'comment' => $comment
        );

        if (!empty($short)) {
        	$this->routes[$long]['short'] = '-'.$short;
        	$this->short_route_map['-'.$short] = $long;
        }

        return $this;
    }

    public function hook($func, $manual=null)
    {
    	$this->is_hook = true;
    	$this->hook_call = $func;
    	$this->hook_manual = $manual;

    	return $this;
    }

    /**
     * Run console script and initialize routes
     *
     * @return 	void
     */
    public function run()
    {
        //$str = str_replace("'", '"', $str);
        //$this->request = str_getcsv($str, ' ');
    	$this->addManual();
        $this->readRequest();
        $this->isError();

        if ($this->is_hook == true) {
        	$call = $this->hook_call;
        	return $call($this->params);
        }

        $this->process();
    }

    /**
     * Process and run callbacks
     *
     * @return 	void
     */
    private function process()
    {
    	if (!empty($this->params)) {
	    	foreach ($this->params as $key => $params) {
	    		if (array_key_exists($key, $this->short_route_map)) {
	    			$key = $this->short_route_map[$key];
	    		}
	    		if (isset($this->routes[$key])) {

	    			if (empty($params)) {
	    				$this->routes[$key]['callback']();
	    			} else {
	    				$this->routes[$key]['callback']($params);
	    			}
	    		}
	    		if ($key == '--man' || $key == 'man') {
	    			$this->printManual();
	    		}
	    	}
    	}
    }

    private function isError()
    {
    	if (!empty($this->params)) {
	    	foreach ($this->params as $key => $params) {
	    		if ($key == '--man') {
	    			continue;
	    		}

	    		if (array_key_exists($key, $this->short_route_map)) {
	    			$key = $this->short_route_map[$key];
	    		}

	    		if (!isset($this->routes[$key]) && $this->is_hook == false) {
	    			$this->out('[red]Fatal Error. near [red_bold]'.$key.'[red]. is not defined or wrong usage![reset]')
	    				->new_line();
	    			exit;
	    		}

	    	}
    	}
    }

    /**
     * Read cli request
     *
     * @return 	void
     */
    private function readRequest()
    {
    	if (empty($this->request) && isset($this->routes[""])) {
        	return $this->routes[""]['callback']();
        }

    	foreach ($this->request as $value) {
    		if (strpos($value, '-') === 0) {
    			$this->params[$value] = array();
    		} else {
    			if (!empty($this->params)) {
    				end($this->params);
    				$this->params[key($this->params)][] = $value;
    			}
    		}
    	}

    	if ($this->request[0] == '--man' || $this->request[0] == 'man') {
    		$this->printManual();
    	}
    	if (!empty($this->request) && empty($this->params)) {
    		if (!empty($this->routes[""])) {
    			$this->routes[""]['callback']($this->request);
    		}
    	}
    }

    /**
     * Add manual
     *
     * @return 	void
     */
    private function addManual()
    {
    	$this->get('man', function() {
    		return true;
    	}, 'Print manual');
    }

    /**
     * Print manual
     *
     * @return 	void
     */
    public function printManual()
    {

    }

    /**
     * Print manual
     *
     * @return 	void
     */
    public function printHookManual()
    {
    	if ($this->request[0] != '--man' && $this->request[0] != 'man') {
    		return false;
    	}

    	$this->out(PHP_EOL."".$this->title."".PHP_EOL);
    	$this->out("------------------".str_repeat(PHP_EOL, 2));
    	$this->out($this->description.str_repeat(PHP_EOL, 2));

    	echo str_repeat(PHP_EOL, 2);
    	exit;
    }

	/**
	* Beeps a certain number of times.
	*
	* @param int $num the number of times to beep
	*/
	public function beep($num = 1)
	{
		echo str_repeat("\x07", $num);

        return $this;
	}

	/**
	* Waits a certain number of seconds, optionally showing a wait message and
	* waiting for a key press.
	*
	* @param int $seconds number of seconds
	* @param bool $countdown show a countdown or not
	*/
	public function wait($seconds = 0, $countdown = true)
	{
		if ($countdown === true)
		{
			$time = $seconds;

			while ($time > 0)
			{
				fwrite(STDOUT, $time.'... ');
				sleep(1);
				$time--;
			}
			$this->out("\n");
		}

		else
		{
			if ($seconds > 0) {
				sleep($seconds);
			} else {
				$this->write($this->wait_msg);
				$this->read();
			}
		}

        return $this;
	}

	/**
	* Get input from the shell, using readline or the standard STDIN
	*
	*
	* @param string|int $name the name of the option (int if unnamed)
	* @return string
	*/
	public function input($prefix = '', $required=false)
	{
		// $readline_support = extension_loaded('readline');

	 //    if ($readline_support) {
		// 	return readline($prefix);
		// }

		echo $prefix;
		$result = trim(fgets(STDIN));

		if (empty($result) && $required === true) {
			$this->out('[red]This is required.[reset]')->new_line();
			return $this->input($prefix, $required);
		}

		return $result;
	}

	/**
	* Clears the screen of output
	*
	* @return void
	*/
    public function clear_screen()
	{
		fwrite(STDOUT, chr(27)."[H".chr(27)."[2J");
		//echo chr(27)."[H".chr(27)."[2J";

        return $this;
	}

	public function new_line($num=1)
	{
		for($i = 0; $i < $num; $i++) {
			fwrite(STDOUT, PHP_EOL);
			//echo PHP_EOL;
		}

		return $this;
	}


	/**
	* Asks the user for input. This can have either 1 or 2 arguments.
	*
	* Usage:
	*
	* // Waits for any key press
	* $this->cli->prompt();
	*
	* // Takes any input
	* $color = $this->cli->prompt('What is your favorite color?');
	*
	* // Takes any input, but offers default
	* $color = $this->cli->prompt('What is your favourite color?', 'white');
	*
	* // Will only accept the options in the array
	* $ready = $this->cli->prompt('Are you ready?', array('y','n'));
	*
	* @return string the user input
	*/
	public function prompt()
	{
		$args = func_get_args();

		$options = array();
		$output = '';
		$default = null;

		// How many we got
		$arg_count = count($args);

		// Is the last argument a boolean? True means required
		$required = end($args) === true;

		// Reduce the argument count if required was passed, we don't care about that anymore
		$required === true and --$arg_count;

		// This method can take a few crazy combinations of arguments, so lets work it out
		switch ($arg_count) {
			case 2:

			// E.g: $ready = $this->cli->prompt('Are you ready?', array('y','n'));
			if (is_array($args[1]))
			{
			list($output, $options)=$args;
			}

			// E.g: $color = $this->cli->prompt('What is your favourite color?', 'white');
			elseif (is_string($args[1]))
			{
			list($output, $default)=$args;
			}

			break;

			case 1:

			// No question (probably been asked already) so just show options
			// E.g: $ready = $this->cli->prompt(array('y','n'));
			if (is_array($args[0]))
			{
			$options = $args[0];
			}

			// Question without options
			// E.g: $ready = $this->cli->prompt('What did you do today?');
			elseif (is_string($args[0]))
			{
			$output = $args[0];
			}

			break;
		}

		// If a question has been asked with the read
		if ($output !== '') {
			$extra_output = '';

			if ($default !== null) {
				$extra_output = ' [ Default: "'.$default.'" ]';
			} elseif ($options !== array()) {
				$extra_output = ' [ '.implode(', ', $options).' ]';
			}

			fwrite(STDOUT, $output.$extra_output.': ');
		}

		// Read the input from keyboard.
		($input = trim($this->input())) or $input = $default;

		// No input provided and we require one (default will stop this being called)
		if (empty($input) and $required === true) {
			$this->out('[red]This is required.[reset]')->new_line();

			$input = call_user_func(array($this, 'prompt'), $args);
		}

		// If options are provided and the choice is not in the array, tell them to try again
		if (!empty($options) && ! in_array($input, $options)) {
			$this->out('[red]This is not a valid option. Please try again.[reset]')->new_line();

			$input = call_user_func_array(array($this, 'prompt'), $args);
		}

		return $input;
	}

    public function flush()
    {
        ob_flush();
        flush();
        return $this;
    }

    public function end()
    {
        ob_end_flush();
        return $this;
    }


    /**
     * @param $command name of command in cli
     * @param $discription  the description of command
     */
    public function command_help($command, $discription)
    {
        $this->out("[green_bold]$command: [white]$discription [reset]\n")->new_line();
    }

}