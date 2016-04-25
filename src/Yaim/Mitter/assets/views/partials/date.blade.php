@extends('layouts.row')
@section('row-content')
	<div class='col-sm-{{$width}}'>
		<input class='form-horizontal row-border form-control' value='{{$oldData}}' name='{{$name}}' type='text' id='{{$name}}' placeholder='{{$title}}' data-datePicker title='{{$title}}'>
	</div>
@endsection
