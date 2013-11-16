<?php

namespace Jacere;

require_once(__dir__.'/../Util.php');

class Stopwatch {
	
	private static $c_instances;
	
	private $m_name;
	private $m_startTime;
	private $m_endTime;
	private $m_markTime;
	private $m_splits;
	
	private function __construct($name) {
		$this->m_name = $name;
		$this->m_startTime = NULL;
		$this->m_endTime = NULL;
		$this->m_memory = NULL;
		$this->m_peakMemory = NULL;
		$this->m_splits = [];
	}
	
	public static function GetInstance($name) {
		if (array_key_exists($name, self::$c_instances)) {
			return self::$c_instances[$name];
		}
		return NULL;
	}
	
	public function Register() {
		if (array_key_exists($name, self::$c_instances))
			die('Duplicate name');
		self::$c_instances[$this->m_name] = $this;
	}
	
	public function Unregister() {
		if (array_key_exists($name, self::$c_instances))
			unset(self::$c_instances[$this->m_name]);
	}
	
	private function Start() {
		$this->m_startTime = microtime(true);
		$this->m_markTime = $this->m_startTime;
		$this->m_endTime = NULL;
		$this->m_memory = memory_get_usage();
		$this->m_peakMemory = memory_get_peak_usage();
	}
	
	public function Stop() {
		$this->m_endTime = microtime(true);
	}
	
	public function Save($name) {
		$previousTime = 0.0;
		if (array_key_exists($name, $this->m_splits)) {
			$previousTime = $this->m_splits[$name];
		}
		$this->m_splits[$name] = [
			'time' => ($previousTime + $this->ElapsedMillisecondsSince($this->m_markTime)),
			'memory' => memory_get_peak_usage(),
			'memory2' => memory_get_usage()
		];
		$this->m_markTime = microtime(true);
	}
	
	public function ElapsedMilliseconds() {
		return $this->ElapsedMillisecondsSince($this->m_startTime);
	}
	
	private function ElapsedMillisecondsSince($start) {
		$end = $this->m_endTime;
		if ($end === NULL)
			$end = microtime(true);
		return ($end - $start) * 1000;
	}
	
	public static function StartNew($name = NULL) {
		$sw = new Stopwatch($name);
		$sw->Start();
		return $sw;
	}
	
	public function __toString() {
		$total = NULL;
		if ($this->m_endTime != NULL) {
			$total = $this->ElapsedMilliseconds();
		}
		
		$splits = '';
		$memoryPrev = $this->m_peakMemory;
		if (true) {
			$memory = ConvertToSize($memoryPrev);
			$splits .= "<tr><td></td><td></td><td></td><td></td><td>{$memory}</td><td></td></tr>";
		}
		foreach ($this->m_splits as $name => $split) {
			$time = $split['time'];
			$percent = ($total != NULL) ? round($time / $total * 100, 0).'%' : '';
			$time = number_format($time, 2);
			$memory2 = ConvertToSize($split['memory2']);
			$memory = ConvertToSize($split['memory']);
			$increase = ConvertToSize($split['memory'] - $memoryPrev);
			$memoryPrev = $split['memory'];
			$splits .= "<tr><td>{$name}</td><td>{$time} ms</td><td>{$percent}</td><td>{$memory2}</td><td>{$memory}</td><td>{$increase}</td></tr>";
		}
		$splits = '<tbody>'.$splits.'</tbody>';
		if ($this->m_endTime != NULL) {
			$time = round($total, 2);
			$splits .= "<tfoot><tr><th>TOTAL</th><th>{$time} ms</th><th>*</th><th></th><th></th><th></th></tr></tfoot>";
		}
		
		$name = $this->m_name;
		if ($name == NULL)
			$name = 'Stopwatch';
		
		return <<<EOT
		<style type="text/css">
			.stopwatch {font-size:12px;font-family:Monospace;border:1px solid #bbb;border-spacing:0;}
			.stopwatch thead,
			.stopwatch tfoot {background-color:#bbb;}
			.stopwatch tbody tr:first-child {background-color:#ddd;}
			.stopwatch td,
			.stopwatch th {padding:0px 4px;}
			.stopwatch th {text-align:left;}
			.stopwatch tfoot th:nth-child(2),
			.stopwatch tfoot th:nth-child(3),
			.stopwatch td:nth-child(2) {text-align:right;}
			.stopwatch td:nth-child(2) {color:blue;}
			.stopwatch td:nth-child(3) {color:red;text-align:right;}
			.stopwatch td:nth-child(4) {text-align:right;}
			.stopwatch td:nth-child(5) {color:#197C9E;text-align:right;}
			.stopwatch td:nth-child(6):not(:empty):before {content:"+";padding-left:4px;}
			.stopwatch td:nth-child(6) {color:green;text-align:right;}
		</style>
		<table class="stopwatch">
			<thead><tr><th colspan="3">{$name}</th><th>mem</th><th>peak</th><th>increase</th></tr></thead>
			{$splits}
		</table>
		<br>
EOT;
	}
}

?>
