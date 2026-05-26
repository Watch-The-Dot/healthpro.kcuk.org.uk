<?php

namespace WatchTheDot\Plugins\RSSImporter\Actions;

use DateInterval;
use DateTimeImmutable;
use WatchTheDot\Plugins\RSSImporter\Model\FeedPost;

class CleanupPosts
{
    public static function run() {
        self::cleanup_rejected_posts();
        self::cleanup_pending_posts();
    }

    private static function cleanup_rejected_posts() {
        global $wpdb;

        $now = new DateTimeImmutable();
        $a_month_ago = $now->sub(DateInterval::createFromDateString('1 month'));
        dd($wpdb->prepare(
            "DELETE FROM %i WHERE `status` = 'rejected' AND `synced_at` < %s",
            FeedPost::table_name(),
            $a_month_ago->format('Y-m-d H:i:s')
        ));

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM %i WHERE `status` = 'rejected' AND `synced_at` < %s",
                FeedPost::table_name(),
                $a_month_ago->format('Y-m-d H:i:s')
            )
        );
    }

    private static function cleanup_pending_posts() {
        global $wpdb;

        $now = new DateTimeImmutable();
        $three_months_ago = $now->sub(DateInterval::createFromDateString('3 months'));

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM %i WHERE `status` = 'pending' AND `synced_at` < %s",
                FeedPost::table_name(),
                $three_months_ago->format('Y-m-d H:i:s')
            )
        );
    }
}