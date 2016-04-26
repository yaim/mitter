@extends('layouts.row')
@section('row-content')
	<div class='col-sm-{{$width}}'>
		<input class='form-horizontal row-border form-control' type='password' value='{{$oldData}}' name='{{$name}}' id='{{$name}}' placeholder='{{$title}}' title='{{$title}}'>
	</div>
@endsection
