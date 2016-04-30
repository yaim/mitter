<div class="panel-body">
    <div class="row">
        <div class="col-md-12">
            <h2>{{$title}}</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="block-web col-md-12">
                <form>
                    <div class="row">
                        <div class="col-sm-10">
                            <input type="text" name="search" class="form-control parsley-validated" placeholder="Search In Items" parsley-min="1" value="{{request('search')}}"/>
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="control-label btn btn-primary">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="row mitter">
            <div class="col-md-12">
                <div class="block-web">
                    <div class="header">
                        <h3 class="content-header">All {{str_plural($title)}}</h3>
                    </div>
                    <div class="porlets-content">
                        <div class="adv-table editable-table ">
                            <div class="clearfix">
                                <div class="btn-group">
                                    <a href="{{$createUrl}}" class="btn btn-primary">
                                        Add New <i class="fa fa-plus"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="margin-top-10"></div>
                            @if(!$items)
                                data not found
                            @else
                                <table class="table table-striped table-hover table-bordered" id="editable-sample">
                                    <thead>
                                    <tr>
                                        @foreach($head as $key=>$value)
                                            <th>{!! $value !!}</th>
                                        @endforeach
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($items as $item)
                                        <tr class="">
                                            @foreach($head as $key => $value)
                                                <td>{!! $item->get($key,'-') !!}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                        <div class="col-xs-12 text-center">
                            {!! $paginate !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
