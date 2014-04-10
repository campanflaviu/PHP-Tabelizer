<?php

// flaviu@cimpan.ro
// 2014

class Tabelizer{
	public $con_type	= FALSE;
	public $wp_con		= FALSE;
	public $t_table 	= FALSE;
	public $t_where		= '';
	public $t_data 		= FALSE;
	public $t_header 	= FALSE;
	public $t_query 	= FALSE;
	public $t_style 	= array('table' => array(), 
								'tr' 	=> array(), 
								'th' 	=> array(), 
								'td' 	=> array(	'all' => array(),
													'ind' => array()));

	

	public function __construct($con_type = FALSE, $args = FALSE){
		if($con_type) $this->sql_connect($con_type);
		if(isset($args['table']))	$this->t_table 	= $args['table'];
		if(isset($args['wp_con'])) 	$this->wp_con 	= $args['wp_con'];
		if(isset($args['where']))	$this->t_where 	= $args['where'];
		$this->get_header();
		$this->get_data();
	}

	public function get_header(){
		if($this->con_type == 'wp')
			$this->t_header = $this->wp_con->get_col("DESC " . $this->t_table, 0);
	}

	public function get_sql_data($table, $args = FALSE){
		
	}

	public function sql_connect($con_type = FALSE){
		$this->con_type = $con_type;
	}

	public function get_data(){
		if($this->con_type == 'wp')
			$this->t_data = $this->wp_con->get_results('SELECT * FROM '.$this->t_table.$this->t_where);
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

	public function print_table(){
		$td_styles = $this->t_style['td'];

		if(count($this->t_header) <= 0) return;
		echo '<table border="0" cellspacing="0" cellpadding="0" class="'.implode(' ', $this->t_style['table']).'"><thead><tr>';
		foreach ($this->t_header as $key => $value) {
			echo '<th class="'.implode(' ', $this->t_style['th']).'">'.$value.'</th>';
		}
		echo '</tr></thead><tbody>';
		foreach ($this->t_data as $row) {
			if(count($this->t_header) == count((array)$row)){
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
	}
}