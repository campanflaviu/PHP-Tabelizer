<?php

// PHP Tabelizer

// flaviu@cimpan.ro
// http://cimpan.ro
// 2014

class Tabelizer{
	private $args 		= FALSE;
	private $editable	= FALSE;
	private $con_type	= FALSE;
	private $wp_con		= FALSE;
	private $t_table 	= FALSE;
	private $t_where	= '';
	private $t_data 	= FALSE;
	private $t_header 	= FALSE;
	private $t_query 	= FALSE;
	private $t_style 	= array('table' => array(), 
								'tr' 	=> array(), 
								'th' 	=> array(), 
								'td' 	=> array(	'all' => array(),
													'ind' => array()));



	public function __construct($con_type = FALSE, $args = FALSE){
		$this->args = $args;
		if($con_type) $this->sql_connect($con_type);
		if(isset($args['table']))			$this->t_table 	= $args['table'];
		if(isset($args['wp_con'])) 			$this->wp_con 	= $args['wp_con'];
		if(isset($args['where']))			$this->t_where 	= $args['where'];
		if(isset($this->args['editable']))	$this->editable = $this->args['editable'];

		$this->get_header();
		$this->get_data();
	}

	function sql_connect($con_type){
		$this->con_type = $con_type;
	}

	public function get_header(){
		if(isset($this->args['columns'])){ // custom column names
			$this->t_header = $this->args['columns'];
			return;	
		}

		if($this->con_type == 'wp')
			$this->t_header = $this->wp_con->get_col("DESC " . $this->t_table, 0);

	}

	public function get_data(){
		$select = isset($this->args['columns']) ? implode(', ', $this->args['columns']) : '*';
		if($this->con_type == 'wp')
			$this->t_data = $this->wp_con->get_results('SELECT '.$select.' FROM '.$this->t_table.$this->t_where);
	}

	public function set_table_styles($args){
		foreach ($this->t_style as $key1 => $value1)
			foreach ($args as $key2 => $value2)
				if($key1 == $key2) 
					if($key1 != 'td')
						$this->t_style[$key1][] = $value2;
					else{
						if(isset($value2['all']))
							$this->t_style[$key1]['all'][] = $value2['all'];
						if(isset($value2['ind']))
							$this->t_style[$key1]['ind'][] = $value2['ind'];
					}
	}

	private function before_print(){
		if(isset($this->args['editable']))	$this->add_column('Edit', '<button class="tabelizer_edit" id="tabelizer_edit_{0}">Edit</button>');
	}

	public function print_table(){

		$this->before_print();

		$td_styles = $this->t_style['td'];

		if(count($this->t_header) <= 0) return;
		echo '<table border="0" cellspacing="0" cellpadding="0" class="'.implode(' ', $this->t_style['table']).'"><thead><tr>';
		foreach ($this->t_header as $key => $value) {
			echo '<th class="'.implode(' ', $this->t_style['th']).'">'.$value.'</th>';
		}
		echo '</tr></thead><tbody>';
		foreach ($this->t_data as $row) {
			if(count($this->t_header) == count((array)$row)){ // show only if number of header elements are the same with the row elements
				echo '<tr>';
				$i = 0;
				foreach ($row as $key => $value) {
					$all_styles = $this->t_style['td']['all'];
					$ind_styles = isset($this->t_style['td']['ind'][$i]) ? $this->t_style['td']['ind'][$i] : array();
					echo '<td class="'.implode(' ', array_merge($all_styles, $ind_styles)).' '.'">'.$value.'</td>';
					$i++;
				}
				echo '</tr>';
			}
		}
		echo '</tbody></table>';

		$this->after_print();
	}

	public function after_print(){
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
		$this->t_header[] = $header;
		foreach ($this->t_data as $key1 => $val1){
			$new_value = $value;
			foreach ($this->t_header as $key2 => $val2)
				$new_value = str_replace('{'.$key2.'}', $this->t_data[$key1]->$val2, $new_value);
			$this->t_data[$key1]->$header = $new_value;
		}
	}

}