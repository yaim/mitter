@extends('layouts.row')
@section('row-content')
	@if(isset($relationEditLink) && !empty(@$relationEditLink))
		<div class='col-sm-{{$width}} col-xs-11'>
			<input class='form-horizontal row-border form-control' value='{{$oldData}}' type='text' placeholder='{{$title}}' locked disabled title='{{$title}}'>
		</div>
		<div class='col-xs-1'>
			<a class='btn btn-sm btn-info link-to-relation' target='_blank' href='{{$relationEditLink}}'><i class='fa fa-external-link'></i></a>
		</div>
	@else
		<div class='col-sm-{{$width}}'>
			<input class='form-horizontal row-border form-control' value='{{$oldData}}' type='text' placeholder='{{$title}}' locked disabled title='{{$title}}'>
		</div>
	@endif
@endsection