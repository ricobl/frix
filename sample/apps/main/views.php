<?
class Views {
	
	function index () {
		
		$context = array(
		);
		
		$t = new Template('main/index');
		echo $t->render($context);
		
	}
}
?>
