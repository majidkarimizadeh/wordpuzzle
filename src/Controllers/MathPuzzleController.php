<?php
namespace Puzzle\Wordpuzzle\Controllers;
ini_set('max_execution_time', 600000);

use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Exception;

class MathPuzzleController extends Controller
{
	const TOP = 0;
	const BOTTOM = 1;
	const LEFT = 2;
	const RIGHT = 3;
	const TOP_RIGHT = 4;
	const TOP_LEFT = 5;
	const BOTTOM_RIGHT = 6;
	const BOTTOM_LEFT = 7;

	const RANDOM_COLOR = 1;
	const GREEN_COLOR = 2;
	const YELLOW_COLOR = 3;
	const BLUE_COLOR = 4;
	const RED_COLOR = 5;

	public function __construct()
	{
		$this->computes = [];
		$this->allAnswers = [];
		$this->mainArray = null;
		$this->row = 0;
		$this->col = 0;
		$this->number = 0;

		$this->width = 0;
    	$this->height = 0;
    	$this->blockSize = 0;
    	$this->blockColor = 1;
    	$this->backgroundColor = "#ffffff";
    	$this->offset = 3;

    	$this->lastColIndex = [];
    	$this->lastRowIndex = [];
    	$this->connectingPoint = [];
	}

	public function index()
	{
		return view('majid/wordpuzzle::mathpuzzle_index');
	}

	public function generate(Request $request)
	{
		$this->validate($request, [
			'number' => 'required|integer',
	        'row' => 'required|integer',
	        'col' => 'required|integer',
	        'color'	=>	'required'
	    ]);

		$this->number = $request->number;
		$this->row = $request->row;
		$this->col = $request->col;
		$this->blockColor = $request->color;

		$this->width = $this->col * 100;
		$this->height = $this->row * 100;
		$this->blockSize = 100;

		$this->generateEmptyImageCell();

		for ($i = 0; $i < $this->number; $i++) { 
			$this->generateEmptyArray();
			$this->getRandomComputation();
			$this->fillArrayWithSelectedComputes($i+1);
		}

		$this->exportExcel();

		return redirect()->route('show.generate.mathpuzzle');
	}

	private function generateEmptyArray() {
		for ($i = 0; $i < $this->row; $i++) { 
			for ($j = 0; $j < $this->col; $j++) { 
				$this->mainArray[$i][$j] = -1;
			}
		}
	}

	public function getRandomComputation()
	{
		$computeNumber = mt_rand(4, 6);
		for ($i = 0; $i < $computeNumber; $i++) { 
			$first = -1;
			$second = -1;
			$plus = -1;
			$operators = ['+', '-'];
			$this->computes[] = [
				"$first",
				$operators[rand(0, 1)],
				"$second",
				'=',
				"$plus"
			];
		}
	}

	private function generateEmptyImageCell()
	{
		$red = config('majid/wordpuzzle.colors.red');
	    $yellow = config('majid/wordpuzzle.colors.yellow');
	    $blue = config('majid/wordpuzzle.colors.blue');
	    $green = config('majid/wordpuzzle.colors.green');
	    $black = config('majid/wordpuzzle.colors.black');

	    $offset = $this->offset;

	   	Storage::exists('public/blocks') || Storage::makeDirectory('public/blocks', 0777);

		$img = Image::canvas($this->blockSize, $this->blockSize);

		if(!Storage::exists('public/blocks/border.png')) {
	        $img->rectangle($offset, $offset, ($this->blockSize-$offset), ($this->blockSize-$offset), function ($draw) use ($black) {
	            $draw->border(2, $black);
	        });
	        $img->save(storage_path('app/public/blocks/') . "border.png", 100);
        }

		if(!Storage::exists('public/blocks/yellow.png')) {
	        $img->rectangle($offset, $offset, ($this->blockSize-$offset), ($this->blockSize-$offset), function ($draw) use ($yellow) {
	            $draw->background($yellow);
	            $draw->border(1, $yellow);
	        });
	        $img->save(storage_path('app/public/blocks/') . "yellow.png", 100);
        }

        if(!Storage::exists('public/blocks/red.png')) {
	        $img->rectangle($offset, $offset, ($this->blockSize-$offset), ($this->blockSize-$offset), function ($draw) use ($red) {
	            $draw->background($red);
	            $draw->border(1, $red);
	        });
	        $img->save(storage_path('app/public/blocks/') . "red.png", 100);
    	}

    	if(!Storage::exists('public/blocks/blue.png')) {
	        $img->rectangle($offset, $offset, ($this->blockSize-$offset), ($this->blockSize-$offset), function ($draw) use ($blue) {
	            $draw->background($blue);
	            $draw->border(1, $blue);
	        });
	        $img->save(storage_path('app/public/blocks/') . "blue.png", 100);
    	}

    	if(!Storage::exists('public/blocks/green.png')) {
	        $img->rectangle($offset, $offset, ($this->blockSize-$offset), ($this->blockSize-$offset), function ($draw) use ($green) {
	            $draw->background($green);
	            $draw->border(1, $green);
	        });
	        $img->save(storage_path('app/public/blocks/') . "green.png", 100);
    	}

    	if(!Storage::exists('public/blocks/black.png')) {
	        $img->rectangle($offset, $offset, ($this->blockSize-$offset), ($this->blockSize-$offset), function ($draw) use ($black) {
	            $draw->background($black);
	            $draw->border(1, $black);
	        });
	        $img->save(storage_path('app/public/blocks/') . "black.png", 100);
    	}
	}

	private function fillArrayWithSelectedComputes($number)
	{
		if(mt_rand(0, 1)) {
			$this->createTemplateTwo();
		} else {
			$this->createTemplateOne();
		}
		$this->createImage($number);
	}

	public function createTemplateTwo()
	{
		$this->connectingPoint = [];
		$col1 = []; $row1 = 0;
		$col2 = 0; $row2 = [];
		$col3 = []; $row3 = 0;
		$col4 = 0; $row4 = [];
		$col5 = []; $row5 = 0;

		$indexes = [0, 2, 4];

		// First Row
		$row1 = $row = $indexes[rand(0, 2)];

		$this->lastRowIndex = [];
		$this->lastColIndex = [];

		if($this->col < 10) $col = 0;
		else $col = rand(0, 1);

		foreach ($this->computes[0] as $computeItem) {
			if($this->mainArray[$row][$col] === -1) {
				$this->mainArray[$row][$col] = $computeItem;
			} else {
				$this->mainArray[$row][$col] = rand(1, 9);
				$this->connectingPoint[] = [$row, $col, false];
			}
			$this->lastColIndex[] = $col;
			$col1[] = $col;
			$col++;
		}
		// END First Row



		// FIRST Col
		$col2 = $col = $col1[2];

		$this->lastRowIndex = [];
		$this->lastColIndex = [$col];

		$row = mt_rand($this->row - 1 - ($this->row - count($this->computes[1])) , $this->row - 1);
		
		foreach ($this->computes[1] as $computeItem) {
			if($this->mainArray[$row][$col] === -1) {
				$this->mainArray[$row][$col] = $computeItem;
			} else {
				$this->mainArray[$row][$col] = rand(1, 9);
				$this->connectingPoint[] = [$row, $col, false];
			}
			$this->lastRowIndex[] = $row;
			$row2[] = $row;
			$row--;
		}
		// END FIRST Col


		// Second Row
		while( in_array( ( $row3 = $row = $this->lastRowIndex[$indexes[mt_rand(0, 2)]] ), [$row1] ) );
		foreach ($this->computes[3] as $computeItem) {
			if($this->mainArray[$row][$col] === -1) {
				$this->mainArray[$row][$col] = $computeItem;
			} else {
				$this->mainArray[$row][$col] = rand(1, 9);
				$this->connectingPoint[] = [$row, $col, false];
			}
			$col3[] = $col;
			$col++;
		}
		// END Second Row

		// Second Col
		$col = $col4 = 6;
		$row = $this->lastRowIndex[4];
		$this->lastRowIndex = [];
		foreach ($this->computes[2] as $computeItem) {
			if($this->mainArray[$row][$col] === -1) {
				$this->mainArray[$row][$col] = $computeItem;
			} else {
				$this->mainArray[$row][$col] = rand(1, 9);
				$this->connectingPoint[] = [$row, $col, true];
			}
			$this->lastRowIndex[] = $row;
			$row4[] = $row;
			$row++;
		}
		// END Second Col

		$col = 4;
		while( in_array( ( $row5 = $row = $this->lastRowIndex[$indexes[mt_rand(0, 2)]] ), [$row1, $row3] ));
		foreach ($this->computes[3] as $computeItem) {
			if($this->mainArray[$row][$col] === -1) {
				$this->mainArray[$row][$col] = $computeItem;
			} else {
				$this->mainArray[$row][$col] = rand(1, 9);
				$this->connectingPoint[] = [$row, $col, false];
			}
			$col5[] = $col;
			$col++;
		}

		$one = 0;
		$two = 0;
		$final = 0;
		$operand = "";

		// First Right
		if($this->mainArray[$row1][$col1[1]] === '-') {
			$two = $this->mainArray[$row1][$col1[2]];
			$final = mt_rand($two, 9);
			$one = $final + $two;
			$this->mainArray[$row1][$col1[0]] = $one;
			$this->mainArray[$row1][$col1[4]] = $final;
		} else if($this->mainArray[$row1][$col1[1]] === '+') {
			$two = $this->mainArray[$row1][$col1[2]];
			$final = mt_rand($two, 9);
			$one = $final - $two;
			$this->mainArray[$row1][$col1[0]] = $one;
			$this->mainArray[$row1][$col1[4]] = $final;
		}

		// TOP
		$one = $this->mainArray[$row2[0]][$col2];
		$two = $this->mainArray[$row2[2]][$col2];
		$final = $this->mainArray[$row2[4]][$col2];
		$operand = $this->mainArray[$row2[1]][$col2];
		if($one < 0  && $two < 0) {
			if($operand === '-') {
				$two = mt_rand(0, $final);
				$one = $final + $two;
			} else if($operand === '+') {
				$one = mt_rand(0, $final);
				$two = $final - $one;
			}
		} else if($one < 0 && $final < 0) {
			if($operand === '-') {
				$final = mt_rand(1, 9);
				$one = $final + $two;
			} else if($operand === '+') {
				$final = mt_rand($two, 9);
				$one = $final - $two;
			}
		} else if($two < 0 && $final < 0) {
			if($operand === '-') {
				$final = mt_rand(1, $one);
				$two = $one - $final;
			} else if($operand === '+') {
				$final = mt_rand($one, 9);
				$two = $final - $one;
			}
		} else if($one < 0) {
			if($operand === '-') {
				if($final < $one) {
					$operand = '+';
					$one = $final - $two;
				} else {
					$one = $final + $two;	
				}
			} else if($operand === '+') {
				if($final < $two) {
					$operand = '-';
					$one = $final + $two;
				} else {
					$one = $final - $two;
				}
			}
		} else if($two < 0) {
			if($operand === '-') {
				if($final > $one) {
					$operand = '+';
					$two = $final - $one;
				} else {
					$two = $one - $final;
				}
			} else if($operand === '+') {
				if($final < $one) {
					$operand = '-';
					$two = $one - $final;
				} else {
					$two = $final - $one;
				}
			}
		} else if($final < 0) {
			if($operand === '-') {
				if($one < $two) {
					$operand = '+';
					$final = $two + $one;
				} else {
					$final = $one - $two;
				}
			} else if($operand === '+') {
				$final = $two + $one;
			}
		}
		
		$this->mainArray[$row2[0]][$col2] = $one;
		$this->mainArray[$row2[2]][$col2] = $two;
		$this->mainArray[$row2[4]][$col2] = $final;
		$this->mainArray[$row2[1]][$col2] = $operand;


		// Bottom
		$one = $this->mainArray[$row4[0]][$col4];
		$two = $this->mainArray[$row4[2]][$col4];
		$final = $this->mainArray[$row4[4]][$col4];
		$operand = $this->mainArray[$row4[1]][$col4];

		if($one >= 0) {
			if($operand === '-') {
				$two = mt_rand(0, $one - 1);
				$final = $one - $two;
			} else if($operand === '+') {
				$two = mt_rand(0, 9 - $one);
				$final = $one + $two;
			}
		} else if($two >= 0) {
			if($operand === '-') {
				$one = mt_rand($two, 9);
				$final = $one - $two;
			} else if($operand === '+') {
				$one = mt_rand(0, 9 - $two);
				$final = $one + $two;
			}
		} else if($final >= 0) {
			if($operand === '-') {
				$two = mt_rand(0, $final - 1);
				$one = $final + $two;
			} else if($operand === '+') {
				$one = mt_rand(0, $final - 1);
				$two = $final - $one;
			}
		}

		$this->mainArray[$row4[0]][$col4] = $one;
		$this->mainArray[$row4[2]][$col4] = $two;
		$this->mainArray[$row4[4]][$col4] = $final;


		// Second Right
		if($this->mainArray[$row3][$col3[1]] === '-') {
			$one = $this->mainArray[$row3][$col3[0]];
			$final = $this->mainArray[$row3][$col3[4]];
			if($final > $one) {
				$operand = '+';
				$two = $final - $one;
				$this->mainArray[$row3][$col3[1]] = $operand;
			} else {
				$two = $one - $final;
			}
			$this->mainArray[$row3][$col3[2]] = $two;
		} else if($this->mainArray[$row3][$col3[1]] === '+') {
			$one = $this->mainArray[$row3][$col3[0]];
			$final = $this->mainArray[$row3][$col3[4]];
			if($final < $one) {
				$operand = '-';
				$two = $one - $final;
				$this->mainArray[$row3][$col3[1]] = $operand;
			} else {
				$two = $final - $one;
			}
			$this->mainArray[$row3][$col3[2]] = $two;
		}



		// Third Right
		if($this->mainArray[$row5][$col5[1]] === '-') {
			$two = $this->mainArray[$row5][$col5[2]];
			$final = mt_rand($two, 9);
			$one = $final + $two;
			$this->mainArray[$row5][$col5[0]] = $one;
			$this->mainArray[$row5][$col5[4]] = $final;

		} else if($this->mainArray[$row5][$col5[1]] === '+') {
			
			$two = $this->mainArray[$row5][$col5[2]];
			$final = mt_rand($two, 9);
			$one = $final - $two;
			$this->mainArray[$row5][$col5[0]] = $one;
			$this->mainArray[$row5][$col5[4]] = $final;
		}


		return;
	}

	private function createTemplateOne()
	{
		$this->connectingPoint = [];
		$col1 = 0; $row1 = [];
		$col2 = []; $row2 = 0;
		$col3 = 0; $row3 = [];
		$col4 = []; $row4 = 0;

		$indexes = [0, 2, 4];

		if($this->col < 10) $col = $col1 = 0;
		else $col = $col1 = rand(0, 1);

		$this->lastColIndex = [$col];
		$this->lastRowIndex = [];
		$row = mt_rand($this->row - 1 - ($this->row - count($this->computes[0])) , $this->row - 1);
		foreach ($this->computes[0] as $computeItem) {
			if($this->mainArray[$row][$col] === -1) {
				$this->mainArray[$row][$col] = $computeItem;
			} else {
				$this->mainArray[$row][$col] = rand(1, 9);
				$this->connectingPoint[] = [$row, $col, false];
			}
			$this->lastRowIndex[] = $row;
			$row1[] = $row;
			$row--;
		}

		$this->lastColIndex = [];
		$row2 = $row = $this->lastRowIndex[$indexes[mt_rand(0, 2)]];
		foreach ($this->computes[1] as $computeItem) {
			if($this->mainArray[$row][$col] === -1) {
				$this->mainArray[$row][$col] = $computeItem;
			} else {
				$this->mainArray[$row][$col] = rand(1, 9);
				$this->connectingPoint[] = [$row, $col, false];
			}
			$this->lastColIndex[] = $col;
			$col2[] = $col;
			$col++;
		}

		$col = $col3 = 4 + $col1;
		$row = $this->lastRowIndex[4];
		$this->lastRowIndex = [];
		foreach ($this->computes[2] as $computeItem) {
			if($this->mainArray[$row][$col] === -1) {
				$this->mainArray[$row][$col] = $computeItem;
			} else {
				$this->mainArray[$row][$col] = rand(1, 9);
				$this->connectingPoint[] = [$row, $col, true];
			}
			$this->lastRowIndex[] = $row;
			$row3[] = $row;
			$row++;
		}


		$col = 4 + $col1;
		$row4 = $row = $this->lastRowIndex[$indexes[mt_rand(0, 2)]];
		foreach ($this->computes[3] as $computeItem) {
			if($this->mainArray[$row][$col] === -1) {
				$this->mainArray[$row][$col] = $computeItem;
			} else {
				$this->mainArray[$row][$col] = rand(1, 9);
				$this->connectingPoint[] = [$row, $col, false];
			}
			$col4[] = $col;
			$col++;
		}


		$one = 0;
		$two = 0;
		$final = 0;

		// Right
		if($this->mainArray[$row4][$col4[1]] === '-') {
			$one = $this->mainArray[$row4][$col4[0]];
			$two = mt_rand(0, $one - 1);
			$final = $one - $two;
			$this->mainArray[$row4][$col4[2]] = $two;
			$this->mainArray[$row4][$col4[4]] = $final;
		} else if($this->mainArray[$row4][$col4[1]] === '+') {
			$one = $this->mainArray[$row4][$col4[0]];
			$two = mt_rand(0, 9 - $one);
			$final = $one + $two;
			$this->mainArray[$row4][$col4[2]] = $two;
			$this->mainArray[$row4][$col4[4]] = $final;
		}

		// Bottom
		$one = $this->mainArray[$row3[0]][$col3];
		$two = $this->mainArray[$row3[2]][$col3];
		$final = $this->mainArray[$row3[4]][$col3];
		$operand = $this->mainArray[$row3[1]][$col3];

		if($one < 0  && $two < 0) {
			if($operand === '-') {
				$two = mt_rand(0, $final);
				$one = $final + $two;
			} else if($operand === '+') {
				$one = mt_rand(0, $final);
				$two = $final - $one;
			}
		} else if($one < 0 && $final < 0) {
			if($operand === '-') {
				$final = mt_rand(1, 9);
				$one = $final + $two;
			} else if($operand === '+') {
				$final = mt_rand($two, 9);
				$one = $final - $two;
			}
		} else if($two < 0 && $final < 0) {
			if($operand === '-') {
				$final = mt_rand(1, $one);
				$two = $one - $final;
			} else if($operand === '+') {
				$final = mt_rand($one, 9);
				$two = $final - $one;
			}
		} else if($one < 0) {
			if($operand === '-') {
				if($final < $one) {
					$operand = '+';
					$one = $final - $two;
				} else {
					$one = $final + $two;	
				}
			} else if($operand === '+') {
				if($final < $two) {
					$operand = '-';
					$one = $final + $two;
				} else {
					$one = $final - $two;
				}
			}
		} else if($two < 0) {
			if($operand === '-') {
				if($final > $one) {
					$operand = '+';
					$two = $final - $one;
				} else {
					$two = $one - $final;
				}
			} else if($operand === '+') {
				if($final < $one) {
					$operand = '-';
					$two = $one - $final;
				} else {
					$two = $final - $one;
				}
			}
		} else if($final < 0) {
			if($operand === '-') {
				if($one < $two) {
					$operand = '+';
					$final = $two + $one;
				} else {
					$final = $one - $two;
				}
			} else if($operand === '+') {
				$final = $two + $one;
			}
		}
		
		$this->mainArray[$row3[0]][$col3] = $one;
		$this->mainArray[$row3[2]][$col3] = $two;
		$this->mainArray[$row3[4]][$col3] = $final;
		$this->mainArray[$row3[1]][$col3] = $operand;


		// Left
		if($this->mainArray[$row2][$col2[1]] === '-') {
			$one = $this->mainArray[$row2][$col2[0]];
			$final = $this->mainArray[$row2][$col2[4]];
			if($one < $final) {
				$this->mainArray[$row2][$col2[1]] = '+';
				$two = $final - $one;
			} else {
				$two = $one - $final;
			}
			$this->mainArray[$row2][$col2[2]] = $two;
		} else if($this->mainArray[$row2][$col2[1]] === '+') {
			$one = $this->mainArray[$row2][$col2[0]];
			$final = $this->mainArray[$row2][$col2[4]];
			if($final < $one) {
				$this->mainArray[$row2][$col2[1]] = '-';
				$two = $one - $final;
			} else {
				$two = $final - $one;
			}
			$this->mainArray[$row2][$col2[2]] = $two;
		}

		// Top
		$one = $this->mainArray[$row1[0]][$col1];
		$two = $this->mainArray[$row1[2]][$col1];
		$final = $this->mainArray[$row1[4]][$col1];
		$operand = $this->mainArray[$row1[1]][$col1];

		if($one >= 0) {
			if($operand === '-') {
				$two = mt_rand(0, $one - 1);
				$final = $one - $two;
			} else if($operand === '+') {
				$two = mt_rand(0, 9 - $one);
				$final = $one + $two;
			}
		} else if($two >= 0) {
			if($operand === '-') {
				$one = mt_rand($two, 9);
				$final = $one - $two;
			} else if($operand === '+') {
				$one = mt_rand(0, 9 - $two);
				$final = $one + $two;
			}
		} else if($final >= 0) {
			if($operand === '-') {
				$two = mt_rand(0, $final - 1);
				$one = $final + $two;
			} else if($operand === '+') {
				$one = mt_rand(0, $final - 1);
				$two = $final - $one;
			}
		}
		$this->mainArray[$row1[0]][$col1] = $one;
		$this->mainArray[$row1[2]][$col1] = $two;
		$this->mainArray[$row1[4]][$col1] = $final;

		return;
	}

	private function createImage($number)
	{
		$color = null;
		if($this->blockColor == self::GREEN_COLOR) {
			$color = "green.png";
		} elseif ($this->blockColor == self::RED_COLOR) {
			$color = "red.png";
		} elseif ($this->blockColor == self::YELLOW_COLOR) {
			$color = "yellow.png";
		} elseif ($this->blockColor == self::BLUE_COLOR) {
			$color = "blue.png";
		} else {
			$colors = [
				"green.png",
				"yellow.png",
				"red.png",
				"blue.png",
			];
		}

		$img = Image::canvas( $this->width, $this->height, $this->backgroundColor);
		$img->rectangle(0, 0, $this->width, $this->height, function ($draw) {
            $draw->border($this->offset, $this->backgroundColor);
        });

		$color = $colors[mt_rand(0, count($colors) - 1)];
		for ($i = 0; $i < $this->row; $i++) { 
			for ($j = 0; $j < $this->col; $j++) { 
				$text = $this->mainArray[$i][$j];

				foreach ($this->connectingPoint as $point) {
					if($point[0] == $i && $point[1] == $j) {
						$img->insert(storage_path('app/public/blocks/') . "black.png", 'top-left', ($j * $this->blockSize), ($i * $this->blockSize));
						if($point[2]) {

							while( in_array( ($n1 = rand(1, 9)), array($text) ));
							while( in_array( ($n2 = rand(1, 9)), array($text, $n1) ));
							while( in_array( ($n3 = rand(1, 9)), array($text, $n2) ));
							$this->allAnswers[] = [
								$text,
								$n1,
								$n2,
								$n3,
							];
							$img->text("?" , ($j * $this->blockSize) + $this->blockSize / 2, ($i * $this->blockSize) + $this->blockSize / 2, function($font) {
					            $font->file(public_path() . "/IRANSans.woff");
					            $font->size(48);
					            $font->color(config('majid/wordpuzzle.colors.fontColor'));
					            $font->align('center');
					            $font->valign('center');
					        });
						}
						$text = -1;
						break;
					}
				}
				if($text !== -1) {
					$img->insert(storage_path('app/public/blocks/') . $color, 'top-left', ($j * $this->blockSize), ($i * $this->blockSize));
					$text = mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8');
					$img->text($text , ($j * $this->blockSize) + $this->blockSize / 2, ($i * $this->blockSize) + $this->blockSize / 2, function($font) {
				            $font->file(public_path() . "/IRANSans.woff");
				            $font->size(48);
				            $font->color(config('majid/wordpuzzle.colors.fontColor'));
				            $font->align('center');
				            $font->valign('center');
				        });
				}

			}
		}
		Storage::exists('public/question') || Storage::makeDirectory('public/question', 0777);
		$img->save(storage_path('app/public/question/') . $number . ".png", 100);
	}

	private function exportExcel()
	{
		$excelName = "answers";
		Excel::create($excelName, function($excel) {
            $excel->sheet('answers', function($sheet) {
                for ($k = 0; $k < $this->number; $k++) {
                    $sheet->row($k + 1, [
                    	$k + 1,
                        'به جای ؟ چه عددی میتواند قرار بگیرد؟',
                        $this->allAnswers[$k][0],
                        $this->allAnswers[$k][1],
                        $this->allAnswers[$k][2],
                        $this->allAnswers[$k][3],
                    ]);
                }
            });
        })->export('xls');
	}
}