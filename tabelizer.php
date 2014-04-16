<?php

// PHP Tabelizer

// flaviu@cimpan.ro
// http://cimpan.ro
// 2014

class Tabelizer{
	private $args 			= FALSE;
	private $editable		= FALSE;
	private $con_type		= FALSE;
	private $wp_con			= FALSE;
	private $table 			= FALSE;
	private $where			= '';
	private $data 			= FALSE;
	private $header 		= FALSE;
	private $query 			= FALSE;

	private $has_pagination = FALSE;
	private $pagination		= array('page_no' 	=> 1,
									'per_page'	=> 10,
									'total'		=> 0);

	private $style 			= array('table' => array(), 
										'tr' 	=> array(), 
										'th' 	=> array(), 
										'td' 	=> array(	'all' => array(),
															'ind' => array()));



	public function __construct($con_type = FALSE, $args = FALSE){
		$this->args = $args;
		if($con_type) $this->sql_connect($con_type);
		if(isset($args['table']))			$this->table 			= $args['table'];
		if(isset($args['wp_con'])) 			$this->wp_con 			= $args['wp_con'];
		if(isset($args['where']))			$this->where 			= $args['where'];
		if(isset($args['pagination']))		{
			$this->get_no_of_elements();
			$this->has_pagination 			= $args['pagination'];
			$this->pagination['page_no'] 	= (isset($_GET['page_no']) 	? $_GET['page_no'] 	: $this->pagination['page_no']);
			$this->pagination['per_page'] 	= (isset($_GET['per_page']) ? $_GET['per_page'] : $this->pagination['per_page']);
			$this->pagination['pages'] 		= ceil($this->pagination['total'] / $this->pagination['per_page']);
		}
		if(isset($this->args['editable']))	$this->editable 		= $this->args['editable'];
		$this->get_header();
		$this->get_data();
	}

	function sql_connect($con_type){
		$this->con_type = $con_type;
	}

	public function get_header(){
		if(isset($this->args['columns'])){ // custom column names
			$this->header = $this->args['columns'];
			return;	
		}

		if($this->con_type == 'wp')
			$this->header = $this->wp_con->get_col("DESC " . $this->table, 0);

	}

	public function get_data(){
		$select = isset($this->args['columns']) ? implode(', ', $this->args['columns']) : '*';
		if($this->con_type == 'wp'){
			$limit = '';
			if($this->has_pagination)
				$limit = ' LIMIT '.($this->pagination['page_no'] - 1) * $this->pagination['per_page'].', '.$this->pagination['per_page'];
			$this->data = $this->wp_con->get_results('SELECT '.$select.' FROM '.$this->table.$this->where.$limit);
		}
	}

	public function get_no_of_elements(){
		if($this->con_type == 'wp'){
			$this->pagination['total'] = current(reset($this->wp_con->get_results('SELECT COUNT(*) FROM '.$this->table)));
		}
	}

	public function set_table_styles($args){
		foreach ($this->style as $key1 => $value1)
			foreach ($args as $key2 => $value2)
				if($key1 == $key2) 
					if($key1 != 'td')
						$this->style[$key1][] = $value2;
					else{
						if(isset($value2['all']))
							$this->style[$key1]['all'][] = $value2['all'];
						if(isset($value2['ind']))
							$this->style[$key1]['ind'][] = $value2['ind'];
					}
	}

	private function before_print(){
		if(isset($this->args['editable']))	$this->add_column('Edit', '<button class="tabelizer_edit" id="tabelizer_edit_{0}">Edit</button>');
	}

	public function print_table(){

		$this->before_print();

		$td_styles = $this->style['td'];

		if(count($this->header) <= 0) return;
		echo '<table border="0" cellspacing="0" cellpadding="0" class="'.implode(' ', $this->style['table']).'"><thead><tr>';
		foreach ($this->header as $key => $value) {
			echo '<th class="'.implode(' ', $this->style['th']).'">'.$value.'</th>';
		}
		echo '</tr></thead><tbody>';
		foreach ($this->data as $row) {
			if(count($this->header) == count((array)$row)){ // show only if number of header elements are the same with the row elements
				echo '<tr>';
				$i = 0;
				foreach ($row as $key => $value) {
					$all_styles = $this->style['td']['all'];
					$ind_styles = isset($this->style['td']['ind'][$i]) ? $this->style['td']['ind'][$i] : array();
					echo '<td class="'.implode(' ', array_merge($all_styles, $ind_styles)).' '.'">'.$value.'</td>';
					$i++;
				}
				echo '</tr>';
			}
		}
		echo '</tbody></table>';
		
		$this->pagination();
		$this->editable_js();
	}

	private function pagination(){
		if($this->pagination){
			$url_vars = $_GET;
				echo '<div class="pagination">';
			if($this->pagination['page_no'] > 1){
				$url_vars['page_no'] 	= $this->pagination['page_no'] - 1;
				$url_vars['per_page'] 	= $this->pagination['per_page'];
				echo '<a href="?'.http_build_query($url_vars).'"><span class="prev">Prev</span></a>';
			}
			for($i = 1; $i <= $this->pagination['pages']; $i++){
				$url_vars['page_no'] 	= $i;
				$url_vars['per_page'] 	= $this->pagination['per_page'];

				if($this->pagination['pages'] > 5 && ($i == $this->pagination['pages'] - 2 && $this->pagination['page_no'] + 1 < $this->pagination['pages'] - 2) || ($i == 3 && $this->pagination['page_no'] -1 > 3)) echo '...';
				if($this->pagination['pages'] > 5 && $i > 2 && $i < $this->pagination['pages'] - 1 && $i > $this->pagination['page_no'] + 1);
				elseif($this->pagination['pages'] > 5 && $this->pagination['pages']/2 < $this->pagination['page_no'] && $i < $this->pagination['page_no'] -1 && $i > 2);
				else
					echo '<a href="?'.http_build_query($url_vars).'"><span class="pag'.(($this->pagination['page_no'] == $i) ? ' active' : '').'">'.$i.'</span></a>';
			}
			if($this->pagination['page_no'] < $this->pagination['pages']){
				$url_vars['page_no'] 	= $this->pagination['page_no'] + 1;
				$url_vars['per_page'] 	= $this->pagination['per_page'];
				echo '<a href="?'.http_build_query($url_vars).'"><span class="next">Next</span></a></div><div style="clear:both"></div>';
			}
		}

	}

	private function editable_js(){
		if($this->editable !== FALSE){
			echo '<script>
					jQuery(".tabelizer_edit").click(function(){

						var row 	= jQuery(this).parent().parent();
						var cells 	= jQuery(row).find("td");

						if(jQuery(row).hasClass("tabelizer-edit-mode")){
							jQuery(row).removeClass("tabelizer-edit-mode");
							jQuery(cells).removeAttr("contenteditable");
							jQuery(this).text("Edit");
							tabelizer_save_data(jQuery(this).attr("id"));
						} else {
							jQuery(row).addClass("tabelizer-edit-mode");
							jQuery(cells).attr("contenteditable", "");
							jQuery(this).text("Save");
						}
					});

					function tabelizer_save_data(id){

					}
				</script>

				
				<style>
					.tabelizer-edit-mode td{
						background-color: #FFFCC1!important;
					}
					.tabelizer-edit-mode:hover td{
						background-color: #FFFAA3!important;
					}
				</style>
			';
		}
	}

	public function add_column($header, $value){
		$this->header[] = $header;
		foreach ($this->data as $key1 => $val1){
			$new_value = $value;
			foreach ($this->header as $key2 => $val2)
				$new_value = str_replace('{'.$key2.'}', $this->data[$key1]->$val2, $new_value);
			$this->data[$key1]->$header = $new_value;
		}
	}

}