@extends('layouts.row')
@section('row-content')
	<div class='col-sm-{{$width}}'>
		<label class='checkbox-inline'>
			<input type='hidden' name='{{$name}}' value='0'>
			<input type='checkbox' {{$checked}} value='1' name='{{$name}}' id='{{$name}}'>
			<span class='custom-checkbox'></span>{{$title}}
		</label>
	</div>
@endsection