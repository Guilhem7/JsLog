<?php
/*
 * This file is part of the JsDebug package.
 *
 * (c) 2023 RIOUX Guilhem
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsLog;

/**
 * Class helping debug from javascript console on client side
 */
class JsLog
{
	private static $instance = null;

	private const FORMAT_LOG = "File: %s (Line %s)\nFunction: %s\n";
	private const DEFAULT_VAL = "???";

	private array $queue; // in case headers hasn't been sent
	private int $stTime;

	// Type of debug
	public const DEBUG = '[DEBUG]';
	public const ERR = '[DANGER]';
	public const MSG = '[SUCCESS]';

	// CSS associated
	public const CSS_RED = 'color: 	#DC143C';
	public const CSS_GREEN = 'color: #77C66E';
	public const CSS_WARN = 'color: #bada55';
	public const CSS_NONE = '';

	private function __construct(){
		$this->queue = array();
		$this->stTime = 0;
	}

	private static function format_log(){
		$log = "";
		$t = debug_backtrace();
		$arr = end($t);
		
		if(!array_key_exists('function', $arr)) $arr["function"] = self::DEFAULT_VAL;
		if(!array_key_exists('line', $arr)) $arr["line"] = self::DEFAULT_VAL;
		if(!array_key_exists('file', $arr)) $arr["file"] = self::DEFAULT_VAL;

		return sprintf(self::FORMAT_LOG, $arr["file"], $arr["line"], $arr["function"]);
	}

	private static function setArgs(...$arr){
		$js_debug = '<script>' . 'console.log(';
		if(count($arr)%2 == 1) array_push($arr, self::CSS_NONE); // In case it misses one param

		$args = array("");
		for ($i=0; $i < count($arr) - 1; $i += 2) {
			$args[0] .= '"%c" + atob("' . base64_encode($arr[$i]) . '") + '; // avoid problem of badchars
			$args[] = '"' . htmlspecialchars($arr[$i + 1]) . '"';
		}

		$args[0] = rtrim($args[0], ' + ');
		$js_debug .= implode(', ', $args);
		$js_debug .= ');';
		$js_debug .= "</script>\n";
		return $js_debug;
	}

	private function displayDebug($res){
		if(!headers_sent()){
			array_push($this->queue, $res);
		} else {
			$this->purgeQueue();
			echo $res;
		}
	}

	public function custom(...$cust){
		$js_debug = self::setArgs(...$cust);

		$this->displayDebug($js_debug);
	}

	private function purgeQueue(){
		while(!empty($this->queue)){
			echo array_shift($this->queue);
		}
	}

	public function log($msg, $type = self::DEBUG){

		switch ($type) {
			case self::DEBUG:
				$css = self::CSS_WARN;
				break;
			case self::MSG:
				$css = self::CSS_GREEN;
				break;
			default:
				$css = self::CSS_RED;
				break;
		}
		
		$sanit = str_replace('\n', "\n", $msg); // Needed to be sure "\n" are interpreted

		$js_debug = self::setArgs(
			$type, $css,
			"\n", self::CSS_NONE,
			self::format_log(), self::CSS_NONE,
			"\n", self::CSS_NONE,
			$sanit, $css
		);

		$this->displayDebug($js_debug);
	}

	public function err($msg){
		$this->log($msg, self::ERR);
	}

	public function msg($msg){
		$this->log($msg, self::MSG);
	}

	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new JsLog();
		}
		return self::$instance;
	}

	public function full_dbg(){
		$a = debug_backtrace();
		$res = print_r($a, true);
		$this->custom($res);
	}

	public function __destruct(){
		/* Empty the queue if it not */
		$this->purgeQueue();
	}
}
