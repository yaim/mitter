@extends('layouts.row')
@section('row-content')
	<input type='hidden' name='{{$name}}'/>
	@foreach ($oldData as $key => $data)
	<div class='col-sm-$width' data-groupkey='{{$key}}'>
		<div class='panel box box-primary' data-group='{{$title}}'>
			<div class='box-header with-border'>
				<h4 class='box-title'>
					<a data-toggle='collapse' href='#{{$key}}'>{{$title}}</a>
				</h4>
			</div>
			<div id='{{$key}}' class='panel-collapse collapse in'>
				<div class='box-body'>
				@if(isset($field['manualKey'])) {
					@if($field['manualKey'] == true)
						<input class='form-horizontal row-border form-control' value='{{$key}}' name='{{$name}}"."[{{$key}}][arraykey]' type='text' id='{{$name}}' placeholder='{{$title}} Key' />
					@endif
				@endif
				@if(isset($field['fields']))
					@foreach ($field['fields'] as $fieldName => $fieldTitle)
						<?php $fieldValue = isset($data[$fieldName]) ? $data[$fieldName] : '' ?>
						<input class='form-horizontal row-border form-control' value='{{$fieldValue}}' name='{{$name}}"."[{{$key}}][{{$fieldName}}]' type='text' id='{{$name}}' placeholder='{{$title}} {{$fieldTitle}}' />
					@endforeach
				@else
					<input class='form-horizontal row-border form-control' value='{{$data}}' name='{{$name}}"."[{{$key}}][arrayvalue]' type='text' id='{{$name}}' placeholder='{{$title}} Value' />
				@endif
				</div>
			</div>
		</div>
	</div>
	@endforeach
@endsection
