<?php
return array( 
	'view_manager' => array(
		'template_path_stack' => array( 
			'cache' => __DIR__ . '/../view',
		),
		'strategies' => array(
			'ParcialStrategy'
		),
	),
);