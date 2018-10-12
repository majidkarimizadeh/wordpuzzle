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
			    	<label for="exampleInputEmail1">
			    		تعداد سطر مورد نیاز
			    	</label>
			    	<input type="text" name="row" class="form-control" placeholder="تعداد سطر مورد نیاز">
			  	</div>

			  	<div class="form-group">
			    	<label for="exampleInputEmail1">
			    		تعداد ستون مورد نیاز
			    	</label>
			    	<input type="text" name="col" class="form-control" placeholder="تعداد ستون مورد نیاز">
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