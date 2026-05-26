<?php

namespace WatchTheDot\Plugins\RSSImporter\Fetch\Media;

use InvalidArgumentException;
use WP_Error;
use stdClass;

use function WatchTheDot\Plugins\RSSImporter\array_kmap;

class ImageDownloader {
    public function __construct( private FetchedMedia $media ) {
    }

    private int $max_width;
    private int $max_height;
    public function set_max_dimensions( ?int $width = null, ?int $height = null ) {
        if ( is_null( $width ) && is_null( $height ) ) {
            throw new InvalidArgumentException();
        }

        if ( ! is_null( $width ) ) {
            $this->max_width = $width;
        }

        if ( ! is_null( $height ) ) {
            $this->max_height = $height;
        }

        return $this;
    }

    private array $metadata = [];
    public function set_metadata( string $key, string $value ) {
        $this->metadata[$key] = $value;

        return $this;
    }

    /**
     * @return stdClass|\WP_Error
     */
    public function save() {
        require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

        // Check if file exists by metadata
        if ( $attachment_id = $this->is_image_a_duplicate() ) {
            $result = new stdClass;
            $result->downloaded = false;
            $result->attachment_id = $attachment_id;

            return $result;
        }

        $file_tmp = $this->download_file();
        if ( is_wp_error( $file_tmp ) ) {
            return $file_tmp;
        }

        // Update Image Size
        $this->update_image_size( $file_tmp );

        // Move to media library
        $attachment_id = $this->move_to_media_library( $file_tmp );
        if ( is_wp_error( $attachment_id ) ) {
            return $attachment_id;
        }

        $this->update_attachment_metadata( $attachment_id );

        $result = new stdClass;
        $result->downloaded = true;
        $result->attachment_id = $attachment_id;

        return $result;
    }

    private function download_file() {
        return download_url( $this->media->get_url() );
    }

    private function is_image_a_duplicate() {
        $search_for_duplicate = get_posts([
            'post_type' => 'attachment',
            'numberposts' => 1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'OR',
                ...array_kmap(fn ( $k, $v ) => [ 'key' => $k, 'value' => $v, ], $this->metadata)
            ]
        ]);

        if ( count( $search_for_duplicate ) ) {
            return current( $search_for_duplicate );
        }

        return false;
    }

    private function update_image_size( string $file ) {
        if ( ! isset( $this->max_height ) && ! isset( $this->max_width ) ) {
            return $file;
        }

        [$width, $height] = getimagesize($file);
        $image_ratio = $width / $height;
        $new_width = $width;
        $new_height = $height;
        if ( isset( $this->max_width ) && isset( $this->max_height ) ) {
            $wanted_ratio = $this->max_width / $this->max_height;
            if ($wanted_ratio > $image_ratio) {
                $new_width = $h * $image_ratio;
                $new_height = $h;
            } else {
                $new_height = $w / $image_ratio;
                $new_width = $w;
            }
        } else if ( isset( $this->max_width ) ) {
            $new_width = $this->max_width;
            $new_height = $this->max_width / $image_ratio;
        } else if ( isset( $this->max_height ) ) {
            $new_height = $this->max_height;
            $new_width = $this->max_height * $image_ratio;
        }
        
        $src = imagecreatefromjpeg($file);
        $dst = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        $file_name = wp_tempnam();
        
        if ( ! imagepng( $dst, $file_name ) ) {
            wp_delete_file( $file_name );
            return new WP_Error('', "Couldn't alter image");
        }
        wp_delete_file( $file );

        return $file_name;
    }

    private function move_to_media_library( string $file_tmp ) {
        $image_ext = match ( mime_content_type( $file_tmp ) ) {
			'image/apng' => 'apng',
			'image/avif' => 'avif',
			'image/gif' => 'gif',
			'image/jpeg' => 'jpg',
			'image/png' => 'png',
			'image/svg+xml' => 'svg',
			'image/webp' => 'webp',
			default => null
		};
		if ( is_null( $image_ext ) ) {
			wp_delete_file( $file_tmp );
            return new WP_Error('', "Invalid Extension");
		}
		$image_name = "{$this->filename}.{$image_ext}";

		$file_array = array(
			'name'     => $image_name,
			'type'     => mime_content_type( $file_tmp ),
			'tmp_name' => $file_tmp,
		);

		$thumbnail_id = media_handle_sideload( $file_array, $post_id );
		wp_delete_file( $file_tmp );
		if ( is_wp_error( $thumbnail_id ) ) {
            return new WP_Error('', "Couldn't sideload image");
		}

        return $thumbnail_id;
    }

    private function update_attachment_metadata( int $attachment_id ) {
        foreach ( $this->metadata as $key => $value ) {
            update_post_meta( $attachment_id, $key, $value );
        }
    }
}