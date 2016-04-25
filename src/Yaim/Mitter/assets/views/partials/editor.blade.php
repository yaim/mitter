@extends('layouts.row')
@section('row-content')
	<div class='col-sm-{{$width}}'>
		<textarea class='form-horizontal row-border form-control ckeditor' name='{{$name}}' placeholder='{{$title}}' cols='50' rows='5' id='{{$name}}'>{{$oldData}}</textarea>
	</div>
@endsection
