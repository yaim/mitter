<div class="row">
	<div class="col-xs-6">
		<h2>{{$structure['title']}}</h2>
	</div>
	@if($isDeleted)
	<form method="POST" action="{{action($structure['controller'].'@store', $id)}}" accept-charset="UTF-8">
		<input name="_token" type="hidden" value="{{csrf_token()}}">
		<div class="col-xs-6">
			<input type="submit" class="btn btn-info pull-right" value="Restore Item">
		</div>
	</form>
	<?php $formTitle = 'Deleted '.$structure['title']; ?>
	@elseif(isset($id))
	<form method="POST" action="{{action($structure['controller']."@destroy", $id)}}" accept-charset="UTF-8">
		<input name="_method" type="hidden" value="DELETE">
		<input name="_token" type="hidden" value="{{csrf_token()}}">
		<div class="col-xs-6">
			<input type="submit" class="btn btn-danger pull-right" onclick="return confirm(`Deleting this item means other models\'s relations to this item, wouldn\'t work anymore! Are you sure you want to delete this?`); return false;" value="Delete Item">
		</div>
	</form>
	<?php $formTitle = 'Edit '.$structure['title']; ?>
	@else
	<?php $formTitle = 'Add '.$structure['title']; ?>
	@endif
</div>
<form method="POST" action="{{action($structure['controller'].'@store')}}" accept-charset="UTF-8" parsley-validate="parsley-validate" novalidate="novalidate" enctype="multipart/form-data">
	<input name="_token" type="hidden" value="{{csrf_token()}}">
	@if (isset($id))
	<input type='hidden' name='id' value='$id'>
	@endif
	<div class="row mitter">
		<div class="col-sm-12 col-md-12">
			<div class="row">
				<h3 class="col-xs-6">{{$formTitle}}</h3>
			</div>
			<div class="tab-container block-web">
			{!!$generatedFields!!}
			</div>
		</div>
	</div>
	<input class="btn btn-primary" type="submit" value="Submit {{$structure['title']}}">
	@if(isset($structure['links']))
	{{$structure['links']}}
	@endif
</form>
