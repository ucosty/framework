<?php
	$map->root(array('controller' => 'homecontroller', 'action' => 'index'));

    // The default route
    $map->connect('/:controller/[:action]/[:id]');
?>