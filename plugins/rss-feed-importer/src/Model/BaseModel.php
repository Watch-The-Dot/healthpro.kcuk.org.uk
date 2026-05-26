<?php

namespace WatchTheDot\Plugins\RSSImporter\Model;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

use function WatchTheDot\Plugins\RSSImporter\tap;

//phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
//phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
abstract class BaseModel {

	protected static string $table_name;

	protected static $primary_key = array( 'id', true );

	protected static string|false $created_at = 'created_at';

	protected static string|false $updated_at = 'updated_at';

	protected static array $casts = array();

	protected array $attributes;

	protected array $old_attributes;

	protected bool $exists = false;

	private bool $deleted = false;

	public function __construct( $attributes = array() ) {
		$this->attributes = $attributes;
	}

	public function save() {
		if ( $this->deleted ) {
			throw new Exception( 'Model has been deleted. Cannot save.' );
		}

		if ( ! $this->dirty() ) {
			return true;
		}

		global $wpdb;
		if ( $this->exists ) {
			$this->before_update();
			$attributes = $this->cast_attributes_to_db( $this->attributes );
			$id         = $attributes[ static::$primary_key[0] ];
			unset( $attributes[ static::$primary_key[0] ] );

			$success = $wpdb->update(
				static::table_name(),
				$attributes,
				array( static::$primary_key[0] => $id ),
				array(),
				array( static::$primary_key[1] ? '%d' : '%s' ),
			);
		} else {
			$this->before_create();
			$attributes = $this->cast_attributes_to_db( $this->attributes );
			$success    = $wpdb->insert(
				static::table_name(),
				$attributes,
				array(),
			);

			if ( static::$primary_key[1] ) {
				$this->attributes[ static::$primary_key[0] ] = $wpdb->insert_id;
			}
		}

		$success = (bool) $success;
		if ( $success ) {
			$this->old_attributes = $this->attributes;
			$this->exists         = true;
		}

		return $success;
	}

	public function dirty() {
		if ( ! $this->exists ) {
			return true;
		}

		// Check if key has been added or removed
		if ( count( array_diff_key( $this->attributes, $this->old_attributes ) ) > 0 ) {
			return true;
		}
		if ( count( array_diff_key( $this->old_attributes, $this->attributes ) ) > 0 ) {
			return true;
		}

		foreach ( array_keys( $this->attributes ) as $key ) {
			// Need to check whether this can be upgraded to strict but for now,
			//phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
			if ( $this->attributes[ $key ] != $this->old_attributes[ $key ] ) {
				return true;
			}
		}

		return false;
	}

	protected function before_create() {
		if ( static::$created_at ) {
			$this->attributes[ static::$created_at ] = current_datetime();
		}

		if ( static::$updated_at ) {
			$this->attributes[ static::$updated_at ] = current_datetime();
		}
	}

	protected function before_update() {
		if ( ! static::$updated_at ) {
			return;
		}

		$this->attributes[ static::$updated_at ] = current_datetime();
	}

	public function delete() {
		if ( $this->deleted || ! $this->exists ) {
			return false;
		}

		global $wpdb;
		$this->deleted = (bool) $wpdb->delete(
			static::table_name(),
			array( static::$primary_key[0] => $this->attributes[ static::$primary_key[0] ] ),
			array( static::$primary_key[1] ? '%d' : '%s' ),
		);

		return $this->deleted;
	}

	public function __get( $name ) {
		return isset( $this->{$name} ) ? $this->attributes[ $name ] : null;
	}

	public function __set( $name, $value ) {
		if ( method_exists( $this, "set_{$name}" ) ) {
			$this->{"set_{$name}"}( $value );
		} elseif ( ( static::$casts[ $name ] ?? '' ) === DateTimeImmutable::class ) {
			if ( is_string( $value ) ) {
				$value = strtotime( $value );
			}

			if ( false === $value ) {
				throw new InvalidArgumentException( 'Invalid DateTime string.' );
			}

			if ( is_integer( $value ) ) {
				$value = DateTimeImmutable::createFromFormat( 'U', $value );
			}

			$this->attributes[ $name ] = $value;
		} else {
			$this->attributes[ $name ] = $value;
		}
	}

	public function __isset( $name ) {
		return isset( $this->attributes[ $name ] );
	}

	private function set_existing_attributes( $attributes ) {
		$attributes = $this->cast_attributes_to_model( $attributes );

		$this->attributes     = $attributes;
		$this->old_attributes = $attributes;
		$this->exists         = true;
	}

	private function cast_attributes_to_model( $attributes ) {
		if ( static::$created_at && isset( $attributes[ static::$created_at ] ) ) {
			$attributes[ static::$created_at ] = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $attributes[ static::$created_at ] );
		}

		if ( static::$updated_at && isset( $attributes[ static::$updated_at ] ) ) {
			$attributes[ static::$updated_at ] = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $attributes[ static::$updated_at ] );
		}

		foreach ( static::$casts as $column => $type ) {
			if ( ! isset( $attributes[ $column ] ) ) {
				continue;
			}

			$attributes[ $column ] = match ( $type ) {
				DateTimeImmutable::class => DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $attributes[ $column ] ),
			};
		}

		return $attributes;
	}

	private function cast_attributes_to_db( $attributes ) {
		if ( static::$created_at && isset( $attributes[static::$created_at] ) ) {
			$attributes[static::$created_at] = $attributes[static::$created_at]->format( 'Y-m-d H:i:s' );
		}

		if ( static::$updated_at && isset( $attributes[static::$updated_at] ) ) {
			$attributes[static::$updated_at] = $attributes[static::$updated_at]->format( 'Y-m-d H:i:s' );
		}

		foreach ( static::$casts as $column => $type ) {
			if ( ! isset( $attributes[ $column ] ) ) {
				continue;
			}

			$value                 = $attributes[ $column ];
			$attributes[ $column ] = match ( $type ) {
				DateTimeImmutable::class => $value instanceof DateTimeInterface ? $value->format( 'Y-m-d H:i:s' ) : $value,
			};
		}

		return $attributes;
	}

	public static function find( $id ) {
		global $wpdb;

		$instance = new static();

		$placeholder = static::$primary_key[1] ? '%d' : '%s';

		//phpcs:disable WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT *
                FROM %i
                WHERE %i = {$placeholder}",
				static::table_name(),
				static::$primary_key[0],
				$id,
			),
			ARRAY_A
		);
		//phpcs:enable WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( is_null( $row ) ) {
			return null;
		}

		$instance->set_existing_attributes( $row );

		return $instance;
	}

	public static function first() {
		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare(#
				'SELECT * FROM %i LIMIT 0, 1',
				static::table_name()
			),
			ARRAY_A
		);

		if ( is_null( $row ) ) {
			return null;
		}

		return tap(
			new static(),
			static function ( self $instance ) use ( $row ) {
				$instance->set_existing_attributes( $row );
			}
		);
	}

	public static function all( $where = array(), $orderby = array() ) {
		global $wpdb;

		$where_stmt = static::prepare_where( $where );

		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM %i {$where_stmt}",
				static::table_name(),
			),
			ARRAY_A
		);
		//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( is_null( $rows ) ) {
			return array();
		}

		return array_map(
			static function ( $row ) {
				$instance = new static();
				$instance->set_existing_attributes( $row );
				return $instance;
			},
			$rows
		);
	}

	public static function count( $where = array() ) {
		global $wpdb;

		$where_stmt = static::prepare_where( $where );

		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM %i {$where_stmt}",
				static::table_name(),
			)
		) ?? 0;
		//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	private static function prepare_where( $where ) {
		global $wpdb;

		$where_stmts = array();
		foreach ( $where as $column => $value ) {
			if ( is_null( $value ) ) {
				$where_stmts[] = $wpdb->prepare( '%i IS NULL', $column );
			} elseif ( is_array( $value ) ) {
				$in  = implode( ',', array_pad( array(), count( $value ), '%d' ) );
				$sql = "%i IN ({$in})";
				//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$where_stmts[] = $wpdb->prepare( $sql, $value );
			} else {
				$where_stmts[] = $wpdb->prepare( '%i = %s', $column, $value );
			}
		}

		return count( $where_stmts ) ? 'WHERE ' . implode( ' AND ', $where_stmts ) : '';
	}

	public static function table_name() {
		global $wpdb;

		return $wpdb->prefix . static::$table_name;
	}
}
