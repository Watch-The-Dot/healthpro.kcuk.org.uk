<?php
add_filter(
	'wpsf_register_settings_rss-feed-importer',
	static function ( $wpsf_settings ) {
        $post_tags = get_terms([
            'taxonomy'   => 'post_tag',
            'hide_empty' => false,
        ]);

		// General Settings section.
		$wpsf_settings[] = array(
			'section_id'          => 'import_settings',
			'section_title'       => 'Importer Settings',
			'section_description' => '',
			'section_order'       => 5,
			'fields'              => array(
                array(
                    'id'      => 'max_post_content',
                    'title'   => 'Maximum Imported Post Content Length',
                    'desc'    => 'The maximum number of words that will be imported',
                    'type'    => 'number',
                    'default' => 200,
                ),
				array(
                    'id'      => 'tag',
                    'title'   => 'Tag',
                    'desc'    => 'Imported Articles will automatically have these tags',
                    'type'    => 'checkboxes',
                    'default' => array(),
                    'choices' => array_reduce(
                        $post_tags,
                        function ( $carry, WP_Term $term ) {
                            $carry[$term->term_id] = $term->name;
                            return $carry;
                        },
                        []
                    ),
                ),
			),
		);

		return $wpsf_settings;
	}
);
