@extends('majid/wordpuzzle::master')

@section('title', 'Welcome to Wordpuzzle')

@section('content')
	
	<div class="panel panel-default">
		<div class="panel-heading">
			به پازل کلمات فارسی خوش آمدید
		</div>
			<div class="panel-body">
			<form method="post" action="{{ route('generate.puzzle') }}">
				<div class="form-group">
			    	<label for="exampleInputEmail1">
			    		تعداد سوال مورد نیاز
			    	</label>
			    	<input type="text" name="number" class="form-control" value="1" placeholder="تعداد سوال مورد نیاز">
			  	</div>

				<div class="form-group">
				  	<div class="radio-inline">
					  	<label>
					    	<input type="radio" name="level" value="10">
					    	سخت (10*10)
					  	</label>
					</div>
					<div class="radio-inline">
					  	<label>
					    	<input type="radio" name="level" value="8">
					    	متوسط (8*8)
					  	</label>
					</div>
					<div class="radio-inline">
					  	<label>
					    	<input type="radio" checked name="level" value="6">
					    	آسان (6*6)
					  	</label>
					</div>
				</div>

				<div class="form-group">
				  	<div class="radio-inline">
					  	<label>
					    	<input type="radio" name="color" value="4">
					    	آبی
					  	</label>
					</div>
					<div class="radio-inline">
					  	<label>
					    	<input type="radio" name="color" value="5">
					    	قرمز
					  	</label>
					</div>
					<div class="radio-inline">
					  	<label>
					    	<input type="radio" name="color" value="3">
					    	زرد
					  	</label>
					</div>
					<div class="radio-inline">
					  	<label>
					    	<input type="radio" name="color" value="2">
					    	سبز
					  	</label>
					</div>
					<div class="radio-inline">
					  	<label>
					    	<input type="radio" checked name="color" value="1">
					    	تصادفی
					  	</label>
					</div>
				</div>

			  	<button type="submit" class="btn btn-default">
			  		ایجاد سوال
			  	</button>
			</form>
		</div>
	</div>
@endsection