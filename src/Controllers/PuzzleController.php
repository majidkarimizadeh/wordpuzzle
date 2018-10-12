<?php 

namespace Puzzle\Wordpuzzle\Controllers;

use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Exception;

class PuzzleController extends Controller
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
		$this->words = [
			'مجید', 'بهنام', 'جواد', 'حسن', 'غلامرضا', 'حامد'
		];
		$this->mainArray = null;
		$this->selectedIndexWords = [];
		$this->answerIndex = [];
		$this->row = 0;
		$this->col = 0;
		$this->number = 0;

		$this->width = 0;
    	$this->height = 0;
    	$this->blockSize = 0;
    	$this->blockColor = 1;
    	$this->backgroundColor = "#ffffff";
    	$this->offset = 3;
	}

	public function index()
	{
		return view('majid/wordpuzzle::index');
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
			$this->getFourRandomWords();
			$this->fillArrayWithSelectedWord($i+1);
		}

		$this->exportExcel();

		return redirect()->route('show.generate.puzzle');
	}

	private function generateEmptyArray() {
		for ($i = 0; $i < $this->row; $i++) { 
			for ($j = 0; $j < $this->col; $j++) { 
				$this->mainArray[$i][$j] = 0;
			}
		}
	}

	private function generateEmptyImageCell()
	{
		$red = config('majid/wordpuzzle.colors.red');
	    $yellow = config('majid/wordpuzzle.colors.yellow');
	    $blue = config('majid/wordpuzzle.colors.blue');
	    $green = config('majid/wordpuzzle.colors.green');

	    $offset = $this->offset;

	   	Storage::exists('public/blocks') || Storage::makeDirectory('public/blocks', 0777);

		$img = Image::canvas($this->blockSize, $this->blockSize);

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
	}

	private function getFourRandomWords()
	{
		$this->selectedIndexWords[] = array_rand($this->words, 4);
		return $this->selectedIndexWords;
	}

	private function fillArrayWithSelectedWord($number)
	{
		$this->answerIndex[] = end($this->selectedIndexWords)[rand(0, 3)];
		$this->checkConditionForSelectedWord($number);
	}

	private function checkConditionForSelectedWord($number)
	{
		$word = $this->words[end($this->answerIndex)];
		$charecters = preg_split('//u', $word, null, PREG_SPLIT_NO_EMPTY);
		$this->checkPositionCondition($charecters);
		$this->createImage($number);
	}

	private function checkPositionCondition($charecters)
	{
		$direction = mt_rand(0, 7);

		$topCondition = $this->row - 1 - ($this->row - count($charecters)) <= $this->row - 1;
		$bottomCondition = 0 <= $this->row - count($charecters);
		$leftCondition = $this->col - 1 - ($this->col - count($charecters)) <= $this->col - 1;
		$rightCondition = 0 <= $this->col - count($charecters);

		if(!$topCondition || !$leftCondition || !$rightCondition || !$bottomCondition) {
			throw new Exception("Sorry the word's charecters ( ". $this->words[end($this->answerIndex)] ." ) is more than ". $this->col ." or ". $this->row, 1);
			return;
		}

		if($direction === self::TOP && $topCondition) {
			$col = mt_rand(0, $this->col - 1);
			$row = mt_rand($this->row - 1 - ($this->row - count($charecters)) , $this->row - 1);
			foreach ($charecters as $charecter) {
				$this->mainArray[$row][$col] = $charecter;
				$row--;
			}
			return;
		} 

		if ($direction === self::BOTTOM && $bottomCondition) {
			$col = mt_rand(0, $this->col - 1);
			$row = mt_rand(0, $this->row - count($charecters));
			foreach ($charecters as $charecter) {
				$this->mainArray[$row][$col] = $charecter;
				$row++;
			}
			return;
		} 

		if ($direction === self::LEFT && $leftCondition) {
			$row = mt_rand(0, $this->row - 1);
			$col = mt_rand($this->col - 1 - ($this->col - count($charecters)) , $this->col - 1);
			foreach ($charecters as $charecter) {
				$this->mainArray[$row][$col] = $charecter;
				$col--;
			}
			return;
		}

		if ($direction === self::RIGHT && $rightCondition) {
			$row = mt_rand(0, $this->row - 1);
			$col = mt_rand(0, $this->col - count($charecters));
			foreach ($charecters as $charecter) {
				$this->mainArray[$row][$col] = $charecter;
				$col++;
			}
			return;
		} 

		if ($direction === self::TOP_RIGHT && $topCondition && $rightCondition) {
			$col = mt_rand(0, $this->col - count($charecters));
			$row = mt_rand($this->row - 1 - ($this->row - count($charecters)) , $this->row - 1);
			foreach ($charecters as $charecter) {
				$this->mainArray[$row][$col] = $charecter;
				$row--;
				$col++;
			}
			return;
		} 

		if ($direction === self::TOP_LEFT && $topCondition && $leftCondition) {
			$col = mt_rand($this->col - 1 - ($this->col - count($charecters)) , $this->col - 1);
			$row = mt_rand($this->row - 1 - ($this->row - count($charecters)) , $this->row - 1);
			foreach ($charecters as $charecter) {
				$this->mainArray[$row][$col] = $charecter;
				$row--;
				$col--;
			}
			return;
		} 

		if ($direction === self::BOTTOM_RIGHT && $bottomCondition && $rightCondition) {
			$col = mt_rand(0, $this->col - count($charecters));
			$row = mt_rand(0, $this->row - count($charecters));
			foreach ($charecters as $charecter) {
				$this->mainArray[$row][$col] = $charecter;
				$row++;
				$col++;
			}
			return;
		} 

		if ($direction === self::BOTTOM_LEFT && $bottomCondition && $leftCondition) {
			$col = mt_rand($this->col - 1 - ($this->col - count($charecters)) , $this->col - 1);
			$row = mt_rand(0, $this->row - count($charecters));
			foreach ($charecters as $charecter) {
				$this->mainArray[$row][$col] = $charecter;
				$row++;
				$col--;
			}
			return;
		}

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

		for ($i = 0; $i < $this->width / $this->blockSize; $i++) { 
			$x = $i * $this->blockSize;
			for ($j = 0; $j < $this->height / $this->blockSize; $j++) { 
				$y = $j * $this->blockSize;

				if($color !== null) {
					$img->insert(storage_path('app/public/blocks/') . $color, 'top-left', $x, $y);
				} else {
					$img->insert(storage_path('app/public/blocks/') . $colors[mt_rand(0, count($colors) - 1)], 'top-left', $x, $y);
				}
			}
		}

		for ($i = 0; $i < $this->row; $i++) { 
			for ($j = 0; $j < $this->col; $j++) { 
				$text = $this->mainArray[$i][$j];
				if($text === 0) {
					$alphabet = config('majid/wordpuzzle.alphabet');
					$text = $alphabet[mt_rand(0, count($alphabet) - 1)];
					$color = config('majid/wordpuzzle.colors.fontColor');
				} else {
					$text = mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8');
					// $color = "#000000";
				}
				$img->text($text , ($j * $this->blockSize) + $this->blockSize / 2, ($i * $this->blockSize) + $this->blockSize / 2, function($font) use ($color){
		            $font->file(public_path() . "/IRANSans.woff");
		            $font->size(34);
		            $font->color($color);
		            $font->align('center');
		            $font->valign('center');
		        });
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
                	$wrongAnswers = array_values(
						array_diff($this->selectedIndexWords[$k], [$this->answerIndex[$k]])
					);
                    $sheet->row($k + 1, [
                    	$k + 1,
                        'کدام کلمه را همشاهده میکنید؟',
                        $this->words[$this->answerIndex[$k]],
                        $this->words[$wrongAnswers[0]],
                        $this->words[$wrongAnswers[1]],
                        $this->words[$wrongAnswers[2]],
                    ]);
                }
            });
        })->export('xls');
	}

}