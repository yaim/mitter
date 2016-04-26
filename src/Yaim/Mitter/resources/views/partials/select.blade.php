@extends('layouts.row')
@section('row-content')
	<div class='col-sm-{{$width}}'>
		<select id='{{$name}}' class='form-control' name='{{$name}}'>
		@foreach ($field['options'] as $key => $value)
			<?php $selected = ''; ?>
			@if(isset($oldData['name']))
				@if($oldData['name'] == $value)
					<?php $selected = ' selected="selected" ' ?>
				@endif
			@elseif($oldData == $key)
				<?php $selected = ' selected="selected" ' ?>
			@endif
			<option value='{{$key}}' {{$selected}}>{{$value}}</option>
		@endforeach
		</select>
	</div>
@endsection
