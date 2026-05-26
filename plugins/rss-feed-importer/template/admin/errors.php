<?php
defined( 'ABSPATH' ) || exit;

use WatchTheDot\Plugins\RSSImporter\Admin\Errors_ListTable;

$table = new Errors_ListTable();
$table->prepare_items();
?>

<form method="post">
	<input type="hidden" name="page" value="<?php echo esc_attr( $GLOBALS['plugin_page'] ); ?>">
	<input type="hidden" name="subpage" value="errors">
	<?php $table->display(); ?>
</form>