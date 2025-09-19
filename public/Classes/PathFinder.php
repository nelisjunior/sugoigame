<?php

class PathFinder {
	/* Directions */
	const DIR_NORTH = 0x00;
	const DIR_NORTHEAST = 0x01;
	const DIR_EAST = 0x02;
	const DIR_SOUTHEAST = 0x03;
	const DIR_SOUTH = 0x04;
	const DIR_SOUTHWEST = 0x05;
	const DIR_WEST = 0x06;
	const DIR_NORTHWEST = 0x07;

	/* Arrows */
	const ARROW_NORTH = '⇑';
	const ARROW_NORTHEAST = '⇗';
	const ARROW_EAST = '⇒';
	const ARROW_SOUTHEAST = '⇘';
	const ARROW_SOUTH = '⇓';
	const ARROW_SOUTHWEST = '⇙';
	const ARROW_WEST = '⇐';
	const ARROW_NORTHWEST = '⇖';

	/* Types */
	const TYPE_FREE = 0x00;
	const TYPE_SOURCE = 0x01;
	const TYPE_DESTINATION = 0x02;
	const TYPE_WALL = 0x04;
	const TYPE_AVOID = 0x08;
	const TYPE_FRIEND = 0x10;
	const TYPE_ENEMY = 0x20;

	/* Variables */
	public $map;
	public $mapFlow;
	public $mapPath;
	public $mapPathSteps;
	public $canWalkDiagonally;

	public $steps = 0;

	public $blockX = 0;
	public $blockY = 0;

	public $source;
	public $destination;
	public $reached = false;

	public function __construct($map = [], $canWalkDiagonally = false) {
		$this->canWalkDiagonally = $canWalkDiagonally;
		if (count($map) > 0) {
			$this->setMap($map);
		}
	}

	private function getDirection($from = [], $destination = [], $checkFlow = false, $step = null, $doNotTest = false) {
		$dir = (
		$destination[0] == $from[0] ?
			(
			$destination[1] < $from[1] ?
				PathFinder::DIR_NORTH :
				PathFinder::DIR_SOUTH
			) : (
		$destination[1] == $from[1] ?
			(
			$destination[0] < $from[0] ?
				PathFinder::DIR_WEST :
				PathFinder::DIR_EAST
			) : (
		$destination[0] < $from[0] ?
			(
			$destination[1] < $from[1] ?
				PathFinder::DIR_NORTHWEST :
				PathFinder::DIR_SOUTHWEST
			) : (
		$destination[1] < $from[1] ?
			PathFinder::DIR_NORTHEAST :
			PathFinder::DIR_SOUTHEAST
		)
		)
		)
		);

		/* Test if can go to the desired direction */
		if (!$doNotTest && !$this->canGoTo($from, $dir, $checkFlow, $step)) {
			$dir = $this->getBestDirection($from, $dir, $checkFlow, $step);
		}
		return $dir;
	}

	private function getBestDirection($from = [], $direction = 0, $checkFlow = false, $step = null) {
		$actual = $direction;
		for ($test = 0; $test <= 7; $test++) {
			if ($this->canGoTo($from, $actual, $checkFlow, $step)) {
				return $actual;
			} else {
				$actual++;
				if ($actual > 7) {
					$actual = 0;
				}
			}
		}

		/* Can't go any where */
		return false;
	}

	public function canGoTo($from = [], $direction = 0, $checkFlow = false, $step = null) {
		if (!$checkFlow) {
			if ($this->getNodeFrom($direction, $from) === PathFinder::TYPE_WALL) {
				return false;
			}
			return true;
		}

		$stepT = $this->getFlowNodeFrom($from, $direction);

		if ($stepT != $step) {
			return false;
		}
		return true;
	}

	public function setMap($map = []) {
		$this->map = $map;
		$this->blockX = count($map[0]);
		$this->blockY = count($map);
		$this->source = $this->getSource();
		$this->destination = $this->getDestination();
		$this->mapFlow = [];

		/* Build the empty map flow */
		for ($x = 0; $x < $this->blockX; $x++) {
			for ($y = 0; $y < $this->blockY; $y++) {
				$this->mapFlow[$y][$x] = ($this->map[$y][$x] == PathFinder::TYPE_SOURCE || $this->map[$y][$x] == PathFinder::TYPE_DESTINATION || $this->map[$y][$x] == PathFinder::TYPE_WALL ? -1 : 0);
			}
		}

		$this->calculateMapFlow();
		$this->calculateMapPath();
	}

	public function dumpFlow() {
		echo "\t  X";
		for ($x = 0; $x < $this->blockX; $x++) {
			echo "|" . str_pad($x, 2, "0", STR_PAD_LEFT);
		}
		echo "\n\t Y ";
		for ($x = 0; $x < $this->blockX; $x++) {
			echo "---";
		}
		echo "\n";

		for ($y = 0; $y < $this->blockY; $y++) {
			echo "\t" . str_pad($y, 2, "0", STR_PAD_LEFT) . " |";
			for ($x = 0; $x < $this->blockX; $x++) {
				echo($x > 0 ? " " : "");

				if ($this->map[$y][$x] == 1) {
					echo "S ";
				} else if ($this->map[$y][$x] == 2) {
					echo "D ";
				} else if ($this->mapFlow[$y][$x] == -1) {
					echo "⛆⛆";
				} else {
					echo str_pad($this->mapFlow[$y][$x], 2, "0", STR_PAD_LEFT);
				}
			}
			echo "\n";
		}
	}

	public function dumpPath() {
		echo "\t  X";
		for ($x = 0; $x < $this->blockX; $x++) {
			echo "|" . ($x > 9 ? ($x % 10) : $x);
		}
		echo "\n\tY  ";
		for ($x = 0; $x < $this->blockX; $x++) {
			echo "--";
		}
		echo "\n";

		for ($y = 0; $y < $this->blockY; $y++) {
			echo "\t" . ($y > 9 ? ($y % 10) : $y) . " |";
			for ($x = 0; $x < $this->blockX; $x++) {
				echo($x > 0 ? " " : "");

				if ($this->map[$y][$x] == 1) {
					echo "S";
				} else if ($this->map[$y][$x] == 2) {
					echo "D";
				} else if ($this->mapFlow[$y][$x] == -1) {
					echo "⛆";
				} else {
					$dir = null;
					if ($this->mapPathSteps !== null) {
						foreach ($this->mapPathSteps as $sk => $step) {
							if ($step[1] == $y && $step[0] == $x) {
								if ($sk == 0) {
									$dir = $this->getDirection($this->source, [$x, $y]);
								} else if ($sk == count($this->mapPathSteps) - 1) {
									$dir = $this->getDirection([$x, $y], $this->destination);
								} else {
									$dir = $this->getDirection($this->mapPathSteps[$sk - 1], $step);
								}
							}
						}
					}

					if ($dir === null) {
						echo " ";
					} else {
						echo $this->getDirArrow($dir);
					}
				}
			}
			echo "\n";
		}
	}

	public function calculateMapPath() {
		if ($this->steps == 0) {
			$min = 9999;
			foreach ($this->getAroundFlowNodes($this->source) as $node) {
				if ($node[3] <= 0) {
					continue;
				}

				if ($node[3] < $min) {
					$min = $node[3];
				}
			}
			$this->steps = $min;
		}

		$this->getPathFromFlow();
	}

	public function getPathFromFlow($from = null, $steps = null) {
		if ($from === null) {
			$from = $this->source;
		}

		if ($steps === null) {
			$steps = $this->steps;
		}

		$dir = $this->getDirection($from, $this->destination, true, $steps);
		$next = $this->getFlowNodeFrom($from, $dir);

		if ($next == $steps) {
			$nDir = $this->getNodeDir($from, $dir);
			$this->mapPathSteps[] = $nDir;
			$this->getPathFromFlow($nDir, $steps - 1);
		}
	}

	public function getNodeDir($from, $dir) {
		return $this->getAroundNodes($from)[$dir];
	}

	public function calculateMapFlow($from = null) {
		if ($from === null) {
			$from = $this->destination;
		}

		/* Creates the step one around destination */
		$nodes = $this->getAroundNodes($from);

		foreach ($nodes as $dir => $node) {
			if ($node[3] == PathFinder::TYPE_SOURCE) {
				$this->reached = true;
				continue;
			}

			if ($node[0] < 0 || $node[0] > $this->blockX || $node[1] < 0 || $node[1] > $this->blockY || $node[3] == PathFinder::TYPE_WALL) {
				continue;
			}

			if ($this->mapFlow[$node[1]][$node[0]] > 1 || $this->mapFlow[$node[1]][$node[0]] == PathFinder::TYPE_FREE) {
				$this->mapFlow[$node[1]][$node[0]] = 1;
			}
		}

		$this->doMapFlow(2);
	}

	public function doMapFlow($actualStep = 1) {
		$new = false;
		foreach ($this->getNodeStep($actualStep - 1) as $node) {
			if ($node[0] < 0 || $node[0] > $this->blockX || $node[1] < 0 || $node[1] > $this->blockY) {
				continue;
			}

			$nodes = $this->getAroundNodes($node);

			foreach ($nodes as $dir => $node) {
				if ($node[3] & PathFinder::TYPE_SOURCE) {
					$this->reached = true;
					continue;
				}

				if ($node[0] < 0 || $node[0] > $this->blockX || $node[1] < 0 || $node[1] > $this->blockY || $node[3] == PathFinder::TYPE_WALL) {
					continue;
				}

				if ($this->mapFlow[$node[1]][$node[0]] > $actualStep || $this->mapFlow[$node[1]][$node[0]] == PathFinder::TYPE_FREE) {
					$this->mapFlow[$node[1]][$node[0]] = $actualStep;
					$new = true;
				}
			}
		}

		if ($new && !$this->reached) {
			$this->doMapFlow($actualStep + 1);
		}
	}

	public function getNodeStep($stepNum = false) {
		$ret = [];
		for ($x = 0; $x < $this->blockX; $x++) {
			for ($y = 0; $y < $this->blockY; $y++) {
				if ($this->mapFlow[$y][$x] == $stepNum) {
					$ret[] = [0 => $x, 1 => $y];
				}
			}
		}
		return $ret;
	}

	public function getSource() {
		for ($x = 0; $x < $this->blockX; $x++) {
			for ($y = 0; $y < $this->blockY; $y++) {
				if ($this->map[$y][$x] == 1) {
					return [0 => $x, 1 => $y];
				}
			}
		}
	}

	public function getDestination() {
		for ($x = 0; $x < $this->blockX; $x++) {
			for ($y = 0; $y < $this->blockY; $y++) {
				if ($this->map[$y][$x] == 2) {
					return [0 => $x, 1 => $y];
				}
			}
		}
	}

	public function getNodeFrom($direction, $from = []) {
		switch ($direction) {
			case self::DIR_NORTH:
				if (!isset($this->map[($from[1] - 1)]) || !isset($this->map[($from[0])])) {
					return PathFinder::TYPE_WALL;
				}

				return (isset($this->map[($from[1] - 1)][($from[0])]) ? $this->map[($from[1] - 1)][($from[0])] : PathFinder::TYPE_WALL);
			case self::DIR_NORTHEAST:
				if (!isset($this->map[$from[1] - 1]) || !isset($this->map[$from[0] + 1])) {
					return PathFinder::TYPE_WALL;
				}
				$node = (isset($this->map[($from[1] - 1)][($from[0] + 1)]) ? ($this->canWalkDiagonally ? $this->map[($from[1] - 1)][($from[0] + 1)] : PathFinder::TYPE_WALL) : PathFinder::TYPE_WALL);

				$test1 = (isset($this->map[($from[1] - 1)][($from[0])]) ? ($this->canWalkDiagonally ? $this->map[($from[1] - 1)][($from[0])] : PathFinder::TYPE_WALL) : PathFinder::TYPE_WALL);
				$test2 = (isset($this->map[($from[1])][($from[0] + 1)]) ? ($this->canWalkDiagonally ? $this->map[($from[1])][($from[0] + 1)] : PathFinder::TYPE_WALL) : PathFinder::TYPE_WALL);

				if ($test1 == 0 && $test2 == 0) {
					return $node;
				} else {
					return PathFinder::TYPE_WALL;
				}
			case self::DIR_EAST:
				if (!isset($this->map[$from[1]]) || !isset($this->map[$from[0] + 1])) {
					return PathFinder::TYPE_WALL;
				}
				return (isset($this->map[($from[1])][($from[0] + 1)]) ? $this->map[($from[1])][($from[0] + 1)] : PathFinder::TYPE_WALL);
			case self::DIR_SOUTHEAST:
				if (!isset($this->map[$from[1] + 1]) || !isset($this->map[$from[0] + 1])) {
					return PathFinder::TYPE_WALL;
				}
				$node = (isset($this->map[($from[1] + 1)][($from[0] + 1)]) ? ($this->canWalkDiagonally ? $this->map[($from[1] + 1)][($from[0] + 1)] : PathFinder::TYPE_WALL) : PathFinder::TYPE_WALL);

				$test1 = (isset($this->map[($from[1])][($from[0] + 1)]) ? ($this->canWalkDiagonally ? $this->map[($from[1])][($from[0] + 1)] : PathFinder::TYPE_WALL) : PathFinder::TYPE_WALL);
				$test2 = (isset($this->map[($from[1] + 1)][($from[0])]) ? ($this->canWalkDiagonally ? $this->map[($from[1] + 1)][($from[0])] : PathFinder::TYPE_WALL) : PathFinder::TYPE_WALL);

				if ($test1 == 0 && $test2 == 0) {
					return $node;
				} else {
					return PathFinder::TYPE_WALL;
				}

			case self::DIR_SOUTH:
				if (!isset($this->map[$from[1] + 1]) || !isset($this->map[$from[0]])) {
					return PathFinder::TYPE_WALL;
				}
				return (isset($this->map[($from[1] + 1)][($from[0])]) ? $this->map[($from[1] + 1)][($from[0])] : PathFinder::TYPE_WALL);
			case self::DIR_SOUTHWEST:
				if (!isset($this->map[$from[1] + 1]) || !isset($this->map[$from[0] - 1])) {
					return PathFinder::TYPE_WALL;
				}
				$node = (isset($this->map[($from[1] + 1)][($from[0] - 1)]) ? ($this->canWalkDiagonally ? $this->map[($from[1] + 1)][($from[0] - 1)] : PathFinder::TYPE_WALL) : PathFinder::TYPE_WALL);

				$test1 = (isset($this->map[($from[1])][($from[0] - 1)]) ? ($this->canWalkDiagonally ? $this->map[($from[1])][($from[0] - 1)] : PathFinder::TYPE_WALL) : PathFinder::TYPE_WALL);
				$test2 = (isset($this->map[($from[1] + 1)][($from[0])]) ? ($this->canWalkDiagonally ? $this->map[($from[1] + 1)][($from[0])] : PathFinder::TYPE_WALL) : PathFinder::TYPE_WALL);

				if ($test1 == 0 && $test2 == 0) {
					return $node;
				} else {
					return PathFinder::TYPE_WALL;
				}
			case self::DIR_WEST:
				if (!isset($this->map[$from[1]]) || !isset($this->map[$from[0] - 1])) {
					return PathFinder::TYPE_WALL;
				}
				return (isset($this->map[($from[1])][($from[0] - 1)]) ? $this->map[($from[1])][($from[0] - 1)] : PathFinder::TYPE_WALL);
			case self::DIR_NORTHWEST:
				if (!isset($this->map[$from[1] - 1]) || !isset($this->map[$from[0] - 1])) {
					return PathFinder::TYPE_WALL;
				}
				$node = (isset($this->map[($from[1] - 1)][($from[0] - 1)]) ? ($this->canWalkDiagonally ? $this->map[($from[1] - 1)][($from[0] - 1)] : PathFinder::TYPE_WALL) : PathFinder::TYPE_WALL);

				$test1 = (isset($this->map[($from[1] - 1)][($from[0])]) ? ($this->canWalkDiagonally ? $this->map[($from[1] - 1)][($from[0])] : PathFinder::TYPE_WALL) : PathFinder::TYPE_WALL);
				$test2 = (isset($this->map[($from[1])][($from[0] - 1)]) ? ($this->canWalkDiagonally ? $this->map[($from[1])][($from[0] - 1)] : PathFinder::TYPE_WALL) : PathFinder::TYPE_WALL);

				if ($test1 == 0 && $test2 == 0) {
					return $node;
				} else {
					return PathFinder::TYPE_WALL;
				}
			default:
				return false;
		}
	}

	public function getAroundNodes($from = []) {
		return [
			self::DIR_NORTH => [0 => ($from[0]), 1 => ($from[1] - 1), 3 => $this->getNodeFrom(self::DIR_NORTH, $from)],
			self::DIR_NORTHEAST => [0 => ($from[0] + 1), 1 => ($from[1] - 1), 3 => $this->getNodeFrom(self::DIR_NORTHEAST, $from)],
			self::DIR_EAST => [0 => ($from[0] + 1), 1 => ($from[1]), 3 => $this->getNodeFrom(self::DIR_EAST, $from)],
			self::DIR_SOUTHEAST => [0 => ($from[0] + 1), 1 => ($from[1] + 1), 3 => $this->getNodeFrom(self::DIR_SOUTHEAST, $from)],
			self::DIR_SOUTH => [0 => ($from[0]), 1 => ($from[1] + 1), 3 => $this->getNodeFrom(self::DIR_SOUTH, $from)],
			self::DIR_SOUTHWEST => [0 => ($from[0] - 1), 1 => ($from[1] + 1), 3 => $this->getNodeFrom(self::DIR_SOUTHWEST, $from)],
			self::DIR_WEST => [0 => ($from[0] - 1), 1 => ($from[1]), 3 => $this->getNodeFrom(self::DIR_WEST, $from)],
			self::DIR_NORTHWEST => [0 => ($from[0] - 1), 1 => ($from[1] - 1), 3 => $this->getNodeFrom(self::DIR_NORTHWEST, $from)],
		];
	}

	public function getFlowNodeFrom($direction, $from = []) {
		switch ($direction) {
			case self::DIR_NORTH:
				return (isset($this->mapFlow[($from[1] - 1)][($from[0])]) ? $this->mapFlow[($from[1] - 1)][($from[0])] : -1);
			case self::DIR_NORTHEAST:
				$node = (isset($this->mapFlow[($from[1] - 1)][($from[0] + 1)]) ? ($this->canWalkDiagonally ? $this->mapFlow[($from[1] - 1)][($from[0] + 1)] : -1) : -1);

				if (!isset($this->mapFlow[($from[1] - 1)]) || !isset($this->mapFlow[$from[0]])) {
					return -1;
				}
				if (!isset($this->mapFlow[($from[1])]) || !isset($this->mapFlow[$from[0] + 1])) {
					return -1;
				}

				$test1 = (isset($this->mapFlow[($from[1] - 1)][($from[0])]) ? ($this->canWalkDiagonally ? $this->mapFlow[($from[1] - 1)][($from[0])] : -1) : -1);
				$test2 = (isset($this->mapFlow[($from[1])][($from[0] + 1)]) ? ($this->canWalkDiagonally ? $this->mapFlow[($from[1])][($from[0] + 1)] : -1) : -1);

				if ($node != -1 && $test1 != -1 && $test2 != -1) {
					return $node;
				} else {
					return -1;
				}
			case self::DIR_EAST:
				return (isset($this->mapFlow[($from[1])][($from[0] + 1)]) ? $this->mapFlow[($from[1])][($from[0] + 1)] : -1);
			case self::DIR_SOUTHEAST:
				$node = (isset($this->mapFlow[($from[1] + 1)][($from[0] + 1)]) ? ($this->canWalkDiagonally ? $this->mapFlow[($from[1] + 1)][($from[0] + 1)] : -1) : -1);

				if (!isset($this->mapFlow[($from[1])]) || !isset($this->mapFlow[$from[0] + 1])) {
					return -1;
				}
				if (!isset($this->mapFlow[($from[1] + 1)]) || !isset($this->mapFlow[$from[0]])) {
					return -1;
				}

				$test1 = (isset($this->mapFlow[($from[1])][($from[0] + 1)]) ? ($this->canWalkDiagonally ? $this->mapFlow[($from[1])][($from[0] + 1)] : -1) : -1);
				$test2 = (isset($this->mapFlow[($from[1] + 1)][($from[0])]) ? ($this->canWalkDiagonally ? $this->mapFlow[($from[1] + 1)][($from[0])] : -1) : -1);

				if ($node != -1 && $test1 != -1 && $test2 != -1) {
					return $node;
				} else {
					return -1;
				}
			case self::DIR_SOUTH:
				return (isset($this->mapFlow[($from[1] + 1)][($from[0])]) ? $this->mapFlow[($from[1] + 1)][($from[0])] : -1);
			case self::DIR_SOUTHWEST:
				$node = (isset($this->mapFlow[($from[1] + 1)][($from[0] - 1)]) ? ($this->canWalkDiagonally ? $this->mapFlow[($from[1] + 1)][($from[0] - 1)] : -1) : -1);

				if (!isset($this->mapFlow[($from[1])]) || !isset($this->mapFlow[$from[0] - 1])) {
					return -1;
				}
				if (!isset($this->mapFlow[($from[1] + 1)]) || !isset($this->mapFlow[$from[0]])) {
					return -1;
				}

				$test1 = (isset($this->mapFlow[($from[1])][($from[0] - 1)]) ? ($this->canWalkDiagonally ? $this->mapFlow[($from[1])][($from[0] - 1)] : -1) : -1);
				$test2 = (isset($this->mapFlow[($from[1] + 1)][($from[0])]) ? ($this->canWalkDiagonally ? $this->mapFlow[($from[1] + 1)][($from[0])] : -1) : -1);

				if ($node != -1 && $test1 != -1 && $test2 != -1) {
					return $node;
				} else {
					return -1;
				}
			case self::DIR_WEST:
				return (isset($this->mapFlow[($from[1])][($from[0] - 1)]) ? $this->mapFlow[($from[1])][($from[0] - 1)] : -1);
			case self::DIR_NORTHWEST:
				$node = (isset($this->mapFlow[($from[1] - 1)][($from[0] - 1)]) ? ($this->canWalkDiagonally ? $this->mapFlow[($from[1] - 1)][($from[0] - 1)] : -1) : -1);

				if (!isset($this->mapFlow[($from[1] - 1)]) || !isset($this->mapFlow[$from[0]])) {
					return -1;
				}
				if (!isset($this->mapFlow[($from[1])]) || !isset($this->mapFlow[$from[0] - 1])) {
					return -1;
				}
				$test1 = (isset($this->mapFlow[($from[1] - 1)][($from[0])]) ? ($this->canWalkDiagonally ? $this->mapFlow[($from[1] - 1)][($from[0])] : -1) : -1);
				$test2 = (isset($this->mapFlow[($from[1])][($from[0] - 1)]) ? ($this->canWalkDiagonally ? $this->mapFlow[($from[1])][($from[0] - 1)] : -1) : -1);

				if ($node != -1 && $test1 != -1 && $test2 != -1) {
					return $node;
				} else {
					return -1;
				}
			default:
				return false;
		}
	}

	public function getAroundFlowNodes($from = []) {
		$ret = [
			self::DIR_NORTH => [0 => ($from[0]), 1 => ($from[1] - 1), 3 => $this->getFlowNodeFrom($from, self::DIR_NORTH)],
			self::DIR_NORTHEAST => [0 => ($from[0] + 1), 1 => ($from[1] - 1), 3 => $this->getFlowNodeFrom($from, self::DIR_NORTHEAST)],
			self::DIR_EAST => [0 => ($from[0] + 1), 1 => ($from[1]), 3 => $this->getFlowNodeFrom($from, self::DIR_EAST)],
			self::DIR_SOUTHEAST => [0 => ($from[0] + 1), 1 => ($from[1] + 1), 3 => $this->getFlowNodeFrom($from, self::DIR_SOUTHEAST)],
			self::DIR_SOUTH => [0 => ($from[0]), 1 => ($from[1] + 1), 3 => $this->getFlowNodeFrom($from, self::DIR_SOUTH)],
			self::DIR_SOUTHWEST => [0 => ($from[0] - 1), 1 => ($from[1] + 1), 3 => $this->getFlowNodeFrom($from, self::DIR_SOUTHWEST)],
			self::DIR_WEST => [0 => ($from[0] - 1), 1 => ($from[1]), 3 => $this->getFlowNodeFrom($from, self::DIR_WEST)],
			self::DIR_NORTHWEST => [0 => ($from[0] - 1), 1 => ($from[1] - 1), 3 => $this->getFlowNodeFrom($from, self::DIR_NORTHWEST)],
		];

		foreach ($ret as $nk => $node) {
			if ($node[3] <= 0) {
				unset($ret[$nk]);
				continue;
			}
		}

		return $ret;
	}

	public function getDirArrow($direction) {
		switch ($direction) {
			case self::DIR_NORTH:
				return self::ARROW_NORTH;
			case self::DIR_NORTHEAST:
				return self::ARROW_NORTHEAST;
			case self::DIR_EAST:
				return self::ARROW_EAST;
			case self::DIR_SOUTHEAST:
				return self::ARROW_SOUTHEAST;
			case self::DIR_SOUTH:
				return self::ARROW_SOUTH;
			case self::DIR_SOUTHWEST:
				return self::ARROW_SOUTHWEST;
			case self::DIR_WEST:
				return self::ARROW_WEST;
			case self::DIR_NORTHWEST:
				return self::ARROW_NORTHWEST;
			default:
				return;
		}
	}
}