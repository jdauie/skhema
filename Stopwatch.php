<?php

namespace Jacere\Skhema;

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
		
		$this->Register();
	}
	
	public static function GetInstances() {
		return self::$c_instances;
	}
	
	public static function GetInstance($name = NULL) {
		if (isset(self::$c_instances[$name])) {
			return self::$c_instances[$name];
		}
		return NULL;
	}
	
	public function Register() {
		self::$c_instances[$this->m_name] = $this;
	}
	
	public function Unregister() {
		if (isset(self::$c_instances[$this->m_name]))
			unset(self::$c_instances[$this->m_name]);
	}
	
	public function IsEmpty() {
		return ($this->m_startTime === NULL);
	}
	
	public function Start() {
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
		if (isset($this->m_splits[$name])) {
			$previousTime = $this->m_splits[$name]['time'];
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
	
	public static function Create($name = NULL) {
		$sw = new Stopwatch($name);
		return $sw;
	}
	
	public static function StartNew($name = NULL) {
		$sw = new Stopwatch($name);
		$sw->Start();
		return $sw;
	}

	function ConvertToSize($size) {
		$unit = ['B','KB','MB','GB','TB','PB'];
		// compare with @number_format
		$i = ($size === 0) ? 0 : floor(log($size, 1024));
		return @round($size / pow(1024, $i), 2).' '.$unit[$i];
	}
	
	public function __toString() {
		$total = NULL;
		if ($this->m_endTime != NULL) {
			$total = $this->ElapsedMilliseconds();
		}
		
		$splits = '';
		$memoryPrev = $this->m_peakMemory;
		if (true) {
			$memory = self::ConvertToSize($memoryPrev);
			$splits .= "<tr><td></td><td></td><td></td><td></td><td>{$memory}</td><td></td></tr>";
		}
		foreach ($this->m_splits as $name => $split) {
			$time = $split['time'];
			$percent = ($total != NULL) ? round($time / $total * 100, 0).'%' : '';
			$time = number_format($time, 2);
			$memory2 = self::ConvertToSize($split['memory2']);
			$memory = self::ConvertToSize($split['memory']);
			$increase = self::ConvertToSize($split['memory'] - $memoryPrev);
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
			<thead><tr><th colspan="3">{$name}</th><th>mem</th><th>peak</th><th>change</th></tr></thead>
			{$splits}
		</table>
		<br>
EOT;
	}
}
