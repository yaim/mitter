@extends('layouts.row')
@section('row-content')
	@if(isset($relationEditLink) && !empty(@$relationEditLink))
		<div class='col-sm-{{$width}} col-xs-11'>
			<select data-minimum-input-length='{{$minimum}}' {{$attributes}} data-placeholder='{{$title}}' data-allow-clear='true' data-ajax--url='{{$api}}' data-ajax--data-type='json' data-ajax--type='GET' data-ajax--quiet-millis='50' name='{{$name}}' id='{{$name}}'>
				<option value='{{$id}}'>{{$text}}</option>
			</select>
		</div>
		<div class='col-xs-1'>
			<a class='btn btn-sm btn-info link-to-relation' target='_blank' href='{{$relationEditLink}}'><i class='fa fa-external-link'></i></a>
		</div>
	@else
		<div class='col-sm-{{$width}}'>
			<select data-minimum-input-length='{{$minimum}}' {{$attributes}} data-placeholder='{{$title}}' data-allow-clear='true' data-ajax--url='{{$api}}' data-ajax--data-type='json' data-ajax--type='GET' data-ajax--quiet-millis='50' name='{{$name}}' id='{{$name}}'>
				<option value='{{$id}}'>{{$text}}</option>
			</select>
		</div>
	@endif
@endsection
