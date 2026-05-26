<?php
defined( 'ABSPATH' ) || exit;

use WatchTheDot\Plugins\RSSImporter\Admin\Feed_ListTable;
use WatchTheDot\Plugins\RSSImporter\Model\Feed;

$table = new Feed_ListTable();
$table->prepare_items();

$add = $_GET['add'] ?? 0;
$add = is_numeric( $add ) ? intval( $add ) : 0;
if ( $add > 0 ) {
	$_SERVER['REQUEST_URI'] = remove_query_arg( 'add' );
	$feed                   = Feed::find( $add );
	printf(
		"<div class='notice notice-success'>
			<p>%s</p>
		</div>",
		esc_html(
			sprintf(
				__( 'Successfully added %s', 'rss-feed-importer' ),
				$feed->name
			)
		)
	);
}
?>

<form method="post">
	<input type="hidden" name="page" value="<?php echo esc_attr( $GLOBALS['plugin_page'] ); ?>">
	<input type="hidden" name="subpage" value="feeds">

	<h1 class='wp-heading-inline'>RSS Feeds</h1>
	<span 
		id="add-new-feed-container" 
		data-page="<?php echo esc_attr(  $GLOBALS['plugin_page']  ); ?>"
		data-nonce="<?php echo esc_attr( wp_create_nonce( 'add-new-feed' ) ); ?>"
	>
	</span>
	<hr class="wp-header-end">

	<?php $table->display(); ?>
</form>