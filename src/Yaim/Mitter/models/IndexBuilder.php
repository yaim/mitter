<?php

namespace Yaim\Mitter;

use Illuminate\Support\Facades\Form;

class IndexBuilder {
	protected $structure;
	protected $html;
	protected $rows;

	public function __construct($structure, $rows, $search_term)
	{
		$this->structure = $structure;
		$this->rows = $rows;

		$this->index_prefix($search_term);
		$this->index_content($rows);
		$this->index_postfix();

		return $this->html;
	}

	public function index_prefix($search_term)
	{
		$structure = $this->structure;
		$create_url = action($structure['controller'].'@create');

		$this->html ='
			<div class="row mitter">
				<div class="col-md-12">
					<h2>'.$structure['title'].'</h2>
				</div><!--/col-md-12-->
			</div><!--/row-->

			<div class="row">
				<div class="col-md-12">
					<div class="block-web col-md-12">';
						
		$this->html .= Form::open(array('method' => 'get'));
		$this->html .= '<div class="col-md-10">'.Form::text('search', $search_term, array('required', 'class' => 'form-control parsley-validated', 'required', 'parsley-min'=>'1', 'placeholder' => 'Search In Items' )).'</div>';
		$this->html .= '<div class="col-md-2">'.Form::submit('Search', array('class' => 'control-label btn btn-primary')).'</div>';
		$this->html .= Form::close();

		$this->html .='
					</div>
				</div><!--/col-md-12-->
			</div><!--/row-->

			<div class="row">
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

	public function index_content($rows)
	{
		if(empty($rows) || !is_array($rows))
		{
			$this->html .= 'Please first search for a term.';
		}
		else
		{
			$this->html .= '
				<table class="table table-striped table-hover table-bordered" id="editable-sample">
					<thead>
						<tr>
							<th><i class="fa fa-edit"></i></th>';

			foreach (head($rows) as $key => $value)
			{
				$this->html .= '
							<th>'.$key.'</th>';
			}

			$this->html .= '
						</tr>
					</thead>
					<tbody>';

			foreach ($rows as $id => $fields)
			{
				$update_url = action($this->structure['controller'].'@edit', $id);
				$this->html .= '
						<tr class="">
							<td style="width: 15px;"><a class="edit" href="'.$update_url.'">Edit</a></td>';

				foreach ($fields as $field_name)
				{
					$this->html .= '
							<td>'.$field_name.'</td>';
				}

				$this->html .= '
						</tr>';
			}
					'</tbody>
				</table>';
		}
	}

	public function index_postfix()
	{
		$this->html .='
						</div>
					</div><!--/porlets-content-->  
				</div><!--/block-web--> 
			</div><!--/col-md-12--> 
		</div><!--/row-->';
	}

	public function get()
	{
		return $this->html;
	}
}