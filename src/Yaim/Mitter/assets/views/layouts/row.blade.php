<div class='form-group row' {{$extraAttributes}}>
	<label for='{{@$name}}' class='form-horizontal row-border col-sm-3 control-label'>{{$title}}</label>
	<div class='col-sm-9'>
		<div class='row'>
			@yield('row-content')
		</div>
	</div>
</div>
@if (!$continious)
<hr/>
@endif
