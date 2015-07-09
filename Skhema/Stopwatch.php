<?php

namespace Jacere\Skhema;

class Stopwatch {
	
	private static $c_instance;

	private $m_splits;
	private $m_end;
	
	private function __construct() {
		$this->m_splits = [];
		$this->save();
	}

	public static function instance(Stopwatch $sw = NULL) {
		if ($sw) {
			self::$c_instance = $sw;
		}
		return self::$c_instance;
	}

	public static function start() {
		return new self();
	}

	public function register() {
		return self::instance($this);
	}
	
	public function stop() {
		$this->m_end = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
		return $this;
	}
	
	public function save($name = NULL) {
		$time = count($this->m_splits) ? microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'] : 0;

		$this->m_splits[] = [
			'name' => $name,
			'time' => $time,
			'memc' => memory_get_usage(),
			'memp' => memory_get_peak_usage(),
		];
		return $this;
	}

	public function getString() {
		$pad_right = ['name'];
		$prev = NULL;
		$lines = [];
//		$lines[] = [
//			'name' => 'name',
//			'time' => 'time (ms)',
//			'memc' => 'memc (KiB)',
//			'memp' => 'memp (KiB)',
//			'memd' => 'memd (KiB)',
//		];
		foreach ($this->m_splits as $split) {
			$lines[] = [
				'name' => ((strlen($split['name']) && $split['name'][0] !== '~') ? " {$split['name']}" : $split['name']),
				'time' => $prev ? number_format(($split['time'] - $prev['time']) * 1000, 2) : NULL,
				'memc' => number_format($split['memc'] >> 10),
				'memp' => number_format($split['memp'] >> 10),
				'memd' => $prev ? number_format(($split['memp'] - $prev['memp']) >> 10) : NULL,
			];
			$prev = $split;
		}

		$keys = array_keys(reset($lines));
		array_unshift($lines, array_combine($keys, $keys));

		$total = number_format($this->m_end * 1000, 2);
		$lines[] = array_combine($keys, array_map(function($a) use($total) {return ($a === 'time') ? $total : NULL;}, $keys));

		$widths = [];
		foreach ($lines as $line) {
			foreach ($line as $key => $value) {
				$widths[$key] = max(isset($widths[$key]) ? $widths[$key] : 0, strlen((string)$value));
			}
		}

		foreach ($lines as &$line) {
			foreach ($line as $key => &$value) {
				$value = str_pad((string)$value, $widths[$key], ($value === NULL) ? '-' : ' ', in_array($key, $pad_right, true) ? STR_PAD_RIGHT : STR_PAD_LEFT);
			}
			$line = sprintf('[ %s ]', implode('  ', $line));
		}

		$border = sprintf('[ %s ]', str_repeat('-', strlen(reset($lines)) - 4));
		array_unshift($lines, $border);
		//$lines[] = $border;

		return implode("\n", $lines);
	}
	
	public function __toString() {
		return '';
	}
}
