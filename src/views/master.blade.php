<!DOCTYPE html>
<html>
<head>
	<title>@yield('title')</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-rtl/3.4.0/css/bootstrap-rtl.min.css">
	<style type="text/css">
		.vertical-center {
		  	min-height: 100%;
		  	min-height: 100vh;
		  	display: flex;
		  	align-items: center;
		}
	</style>
</head>
<body>
	<div class="vertical-center">

		@if (isset($errors) && $errors->any())
		    <div class="alert alert-danger">
		        <ul>
		            @foreach ($errors->all() as $error)
		                <li>{{ $error }}</li>
		            @endforeach
		        </ul>
		    </div>
		@endif

		<div class="container">
			@yield('content')
		</div>
	</div>
</body>
</html>