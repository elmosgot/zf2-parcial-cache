# zf2-parcial-cache
Cache easily the content of each page by just using the parcial ViewModel inside the Controller's actions

Activate module
------------
```
<?php
return array(
    'modules' => array(
		...
    	'Cache',
```

Configure cache settings
------------
Copy the file `config/local.php.dist` to `config/autoload/parcial.global.php`

Using the parcial cache ViewModel
------------
```
use Cache\View\Model\ParcialModel;
...
public function indexAction() {
		/**
		 * Your action code goes here as usual
		 *
		 * And instead of returning an ViewModel you return the ParcialModel and all is settled :-)
		 */
		return new ParcialModel( array( 'param1' => $param1, 'param2' => $param2 ) );
	}
```
