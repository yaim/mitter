<?php namespace Yaim\Mitter;

class IndexBuilder {
	protected $structure;
	protected $html;
	protected $rows;

	public function __construct($structure, $rows, $searchTerm)
	{
		// @todo: find a way to get rid of this dummy hack fix
		$laravel = app();
		if (0 === strpos($laravel::VERSION, '5.')) {
			\URL::setRootControllerNamespace('');
		}

		$this->structure = $structure;
		$this->rows = $rows;

		$currentPage = (\Input::get('p')) ? \Input::get('p') : 1;
		$paginationCount = 40;
		$totalPaginations = count($rows) / $paginationCount;
		$totalPaginations = (count($rows) % $paginationCount > 0) ? $totalPaginations + 1 : $totalPaginations;
		$pagination = ['total' => $totalPaginations, 'current' => $currentPage, 'count' => $paginationCount];

		$this->index_prefix($searchTerm);
		$this->index_content($rows, $pagination);
		$this->index_postfix($pagination);

		return $this->html;
	}

	public function index_prefix($searchTerm)
	{
		$structure = $this->structure;
		$create_url = action($structure['controller'].'@create');

		$this->html ='
			<div class="row">
				<div class="col-md-12">
					<h2>'.$structure['title'].'</h2>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<div class="block-web col-md-12">';
						
		$this->html .= \Form::open(array('method' => 'get'));
		$this->html .= '<div class="row">';
		$this->html .= '<div class="col-sm-10">'.\Form::text('search', $searchTerm, array('required', 'class' => 'form-control parsley-validated', 'required', 'parsley-min'=>'1', 'placeholder' => 'Search In Items' )).'</div>';
		$this->html .= '<div class="col-sm-2">'.\Form::submit('Search', array('class' => 'control-label btn btn-primary')).'</div>';
		$this->html .= '</div>';
		$this->html .= \Form::close();

		$this->html .='
					</div>
				</div>
			</div>

			<div class="row mitter">
				<div class="col-md-12">
					<div class="block-web">
					<div class="header">
						<h3 class="content-header">All '.str_plural($structure['title']).'</h3>
					</div>
						<div class="porlets-content">
							<div class="adv-table editable-table ">
								<div class="clearfix">
									<div class="btn-group">
										<a href="'.$create_url.'" class="btn btn-primary">
											Add New <i class="fa fa-plus"></i>
										</a>
									</div>
								</div>
								<div class="margin-top-10"></div>';
	}

	public function index_content($rows, $pagination)
	{
		if(empty($rows) || !is_array($rows)) {
			$this->html .= 'Please first search for a term.';
		} else {
			$this->html .= '
				<table class="table table-striped table-hover table-bordered" id="editable-sample">
					<thead>
						<tr>
							<th><i class="fa fa-edit"></i></th>';

			foreach (head($rows) as $key => $value) {
				$this->html .= '
							<th>'.$key.'</th>';
			}

			$this->html .= '
						</tr>
					</thead>
					<tbody>';

			foreach (array_chunk(array_reverse($rows, true), $pagination['count'])[$pagination['current']-1]  as $id => $fields) {
				$update_url = action($this->structure['controller'].'@edit', $id);
				$this->html .= '
						<tr class="">
							<td style="width: 15px;"><a class="edit" href="'.$update_url.'">Edit</a></td>';

				foreach ($fields as $field_name) {
					$this->html .= '
							<td>'.$field_name.'</td>';
				}

				$this->html .= '
						</tr>';
			}

			$this->html .= '
					</tbody>
				</table>';
		}
	}

	public function index_postfix($pagination)
	{
		if($pagination['total'] > 1) {
			$prev = ($pagination['current'] > 1) ? $pagination['current'] - 1 : $pagination['current'];
			$next = ($pagination['current'] < $pagination['total']) ? $pagination['current'] + 1 : $pagination['current'];
			$this->html .='
							<div class="col-xs-12 text-center">
								<ul class="pagination">
									<li><a href="?p='.$prev.'">«</a></li>';

			for ($page=1; $page <= $pagination['total']; $page++) { 
				$active = ($page == $pagination['current']) ? 'class="active"' : null;
				$this->html .='
									<li '.$active.'><a href="?p='.$page.'">'.$page.'</a></li>';
			}

			$this->html .='
									<li><a href="?p='.$next.'">»</a></li>
								</ul>
							</div>';
		}
		$this->html .='
						</div>
					</div>
				</div>
			</div>
		</div>';
	}

	public function get()
	{
		return $this->html;
	}
}
