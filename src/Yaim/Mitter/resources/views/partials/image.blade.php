@extends('layouts.row')
@section('row-content')
	<div class='col-sm-{{$width}}'>
		<input class='form-horizontal row-border form-control' name='{{$name}}' type='file' id='{{$name}}' placeholder='{{$title}}'>
	</div>
	@if($oldData)
	<div class='col-sm-1 btn' data-toggle='modal' data-target='#{{$name}}-modal'>
		<img class='form-horizontal row-border' width='100%' src='{{$oldData}}' alt='{{$name}}'/>
	</div>
	<div class='col-sm-1 btn-group' data-toggle='buttons'>
		<label class='btn btn-danger fa fa-remove' title='remove'>
			<input type='checkbox' name='{{$removeName}}' id='{{$removeName}}' autocomplete='off'>
		</label>
	</div>
	<div class='modal fade' id='{{$name}}-modal' tabindex='-1' role='dialog' aria-labelledby='{{$name}}-modal-lable' aria-hidden='true'>
		<div class='modal-dialog'>
			<div class='modal-content'>
				<div class='modal-header'>
					<button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
					<h4 class='modal-title' id='{{$name}}-modal-lable'>{{$title}}</h4>
				</div>
				<div class='modal-body'>
					<img class='form-horizontal row-border' width='100%' src='{{$oldData}}' alt='{{$name}}' />
				</div>
				<div class='modal-footer'>
					<button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
				</div>
			</div>
		</div>
	</div>
	@endif
@endsection
