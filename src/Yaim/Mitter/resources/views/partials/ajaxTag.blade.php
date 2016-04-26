@extends('layouts.row')
@section('row-content')
	<div class='col-sm-{{$width}}'>
		<select {{$attributes}} data-minimum-input-length='{{$minimum}}' data-placeholder='{{$title}}' data-allow-clear='true' data-ajax--url='{{$api}}' data-ajax--data-type='json' data-ajax--type='GET' data-ajax--quiet-millis='50' multiple='multiple' name='{{$name}}' id='{{$name}}' data-api='{{$api}}' placeholder='{{$title}}'>
			@foreach ($oldDataArray as $value)
			<option selected="selected" value="{{$value['id']}}">{{$value['text']}}</option>
			@endforeach
		</select>
	</div>
@endsection
