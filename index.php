<?php

// flaviu@cimpan.ro

// DEMO

include('tabelizer.php');


$tabel = new Tabelizer(	'wp', 
							array(	'table' 	=> 'wp_users',
									'columns'	=> array('ID', 'user_login', 'user_email', 'user_registered', 'user_status'), // default columns (optional)
									'wp_con' 	=> $wpdb,
									'editable'	=> 0)); // argument is the column with the unique ID

	// table styles (optional)
	$tabel->set_table_styles(array(	'table' 	=> 'orders', 
									'th' 		=> 'th_table',
									'td'		=> array(	'all' 	=> 'asd',
															'ind'	=> array(	0 => 'left_table', 
																				2 => 'st'))));

	// custom additional columns
	$tabel->add_column('PDF', '<a href="google.com/{0}/{1}">{2} - {3}</a>');
	// $tabel->change_column(3, date('y=m=d', )); // needs work

	$tabel->print_table();



