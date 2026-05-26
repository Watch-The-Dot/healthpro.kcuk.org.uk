<?php
declare(strict_types=1);

namespace WatchTheDot\Plugins\DivibbPress\Settings;

class SettingsBuilder {

	private array $settings = array();

	public function __construct() {
	}

	private string $tab_slug;
	public function tab( string $tab_slug ): self {
		$this->tab_slug = $tab_slug;

		return $this;
	}

	private string $section_slug;
	public function section( string $section_slug ): self {
		$this->section_slug = $section_slug;

		return $this;
	}

	private string $prefix;
	public function prefix( string $prefix ): self {
		$this->prefix = $prefix;

		return $this;
	}

	private array $if = array();
	public function if( array $show_if ): self {
		$this->if = $show_if;

		return $this;
	}

	public function and( array $show_if ): self {
		$this->if = array_merge(
			$this->if,
			$show_if
		);

		return $this;
	}

	public function tap( \Closure $tap ): self {
		$builder = $this->clone();

		$this->settings = array_merge(
			$this->settings,
			$tap( $builder )->settings,
		);

		return $this;
	}

	public function add( array $settings ): self {
		foreach ( $settings as $key => $setting ) {
			$this->add_setting( $key, $setting );
		}

		return $this;
	}

	private function add_setting( string $key, array $setting ) {
		if ( isset( $this->prefix ) ) {
			$key = $this->prefix . $key;
		}

		if ( isset( $this->if ) && ! empty( $this->if ) ) {
			$show_if = array();
			foreach ( $this->if as $if_key => $value ) {
				$show_if[ ( $this->prefix ?? '' ) . $if_key ] = $value;
			}

			$setting['show_if'] = $show_if;
		}

		if ( isset( $this->tab_slug ) ) {
			$setting['tab_slug'] = $this->tab_slug;
		}

		if ( isset( $this->section_slug ) ) {
			$setting['toggle_slug'] = $this->section_slug;
		}

		$this->settings[ $key ] = $setting;
	}

	public function build() {
		return $this->settings;
	}

	public function clone() {
		$builder           = clone $this;
		$builder->settings = array();

		return $builder;
	}
}
