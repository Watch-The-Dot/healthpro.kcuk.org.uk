<?php
defined( 'ABSPATH' ) || exit;

use WatchTheDot\Plugins\RSSImporter\Admin\Posts_ListTable;

$table = new Posts_ListTable();
$table->prepare_items();
?>

<form method="post">
	<input type="hidden" name="page" value="<?php echo esc_attr( $GLOBALS['plugin_page'] ); ?>">
	<input type="hidden" name="subpage" value="list">
	<?php
	$table->views();
	$table->display();
	?>
</form>

<div id="import-new-post"></div>