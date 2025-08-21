<?php
/**
 * Do not access the page directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ATFP_Supported_Blocks' ) ) {
	/**
	 * Class ATFP_Supported_Blocks
	 *
	 * This class handles the supported blocks for the AutoPoly - AI Translation For Polylang plugin.
	 *
	 * @package ATFP
	 */
	class ATFP_Supported_Blocks {
		/**
		 * Singleton instance.
		 *
		 * @var ATFP_Supported_Blocks
		 */
		private static $instance = null;

		/**
		 * ATFP plugin category.
		 *
		 * @var array
		 */
		private $atfp_plugin_category = array();

		/**
		 * Get the singleton instance of the class.
		 *
		 * @return ATFP_Supported_Blocks
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor for the ATFP_Supported_Blocks class.
		 */
		private function __construct() {
			// wp:phpcs:ignore Wordpress.security Nonce verification is not required here
			$tab=isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : '';
			$page=isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
			if('support-blocks' === $tab && 'polylang-atfp-dashboard' === $page){
				$this->atfp_render_support_blocks_page();
				$this->enqueue_editor_assets();
			}

		}

		/**
		 * Enqueue editor CSS for the supported blocks page.
		 */
		public function enqueue_editor_assets( ) {
			wp_enqueue_script( 'atfp-datatable-script', ATFP_URL . 'assets/js/dataTables.min.js', array(), ATFP_V, true );
			wp_enqueue_script( 'atfp-datatable-style', ATFP_URL . 'assets/js/dataTables.min.js', array(), ATFP_V, true );
			wp_enqueue_style( 'atfp-editor-supported-blocks', ATFP_URL . 'assets/css/atfp-supported-blocks.min.css', array(), ATFP_V );
			wp_enqueue_script( 'atfp-editor-supported-blocks', ATFP_URL . 'assets/js/atfp-supported-block.min.js', array('atfp-datatable-script'), ATFP_V, true );
		}

		/**
		 * Render the support blocks page.
		 */
		public function atfp_render_support_blocks_page() {
			?>
		<div class="atfp-supported-blocks-wrapper">
		<h3><?php printf( esc_html__( 'Supported Blocks for %s', 'autopoly-ai-translation-for-polylang' ), 'AutoPoly - AI Translation For Polylang' ); ?></h3>
		<div class="atfp-supported-blocks-filters">
				<div class="atfp-category-tab">
					<label for="atfp-blocks-category"><?php esc_html_e( 'Block Type Category:', 'autopoly-ai-translation-for-polylang' ); ?></label>
					<select id="atfp-blocks-category" name="atfp_blocks_category">
						<option value="all"><?php esc_html_e( 'All', 'autopoly-ai-translation-for-polylang' ); ?></option>
						<option value="core">Core</option>
						<?php $this->atfp_get_blocks_category(); ?>
					</select>
				</div>
				<div class="atfp-filter-tab">
					<label for="atfp-blocks-filter"><?php esc_html_e( 'Show Blocks:', 'autopoly-ai-translation-for-polylang' ); ?></label>
					<select id="atfp-blocks-filter" name="atfp_blocks_filter">
						<option value="all"><?php esc_html_e( 'All', 'autopoly-ai-translation-for-polylang' ); ?></option>
						<option value="supported"><?php esc_html_e( 'Supported Blocks', 'autopoly-ai-translation-for-polylang' ); ?></option>
						<option value="unsupported"><?php esc_html_e( 'Unsupported Blocks', 'autopoly-ai-translation-for-polylang' ); ?></option>
					</select>
				</div>
			</div>
			<div class="atfp-blocks-section">
				<div class="atfp-blocks-lists">
					<table class="atfp-supported-blocks-table" id="atfp-supported-blocks-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Sr.No', 'autopoly-ai-translation-for-polylang' ); ?></th>
								<th><?php esc_html_e( 'Block Name', 'autopoly-ai-translation-for-polylang' ); ?></th>
								<th><?php esc_html_e( 'Block Title', 'autopoly-ai-translation-for-polylang' ); ?></th>
								<th><?php esc_html_e( 'Status', 'autopoly-ai-translation-for-polylang' ); ?></th>
								<th><?php esc_html_e( 'Modify', 'autopoly-ai-translation-for-polylang' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php 
								$this->atfp_get_supported_blocks_table()
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
			<?php
		}

		/**
		 * Get the blocks category.
		 */
		public function atfp_get_blocks_category() {
			$blocks_data                 = WP_Block_Type_Registry::get_instance()->get_all_registered();

			$filter_blocks_data = array_filter( $blocks_data, function( $block ) {
				return !in_array($block->category, array( 'media', 'reusable' ));
			} );
			foreach ( $filter_blocks_data as $block ) {
				$plugin_name = explode('/', $block->name);
				$plugin_name = isset($plugin_name[0]) ? $plugin_name[0] : '';

				if(!empty($plugin_name)){
					$filter_plugin_name = $this->atfp_supported_block_name($plugin_name);
					$filter_plugin_name=str_replace('-',' ',$filter_plugin_name);
					$filter_plugin_name=ucwords($filter_plugin_name);

					if(in_array($plugin_name, $this->atfp_plugin_category) || $plugin_name === 'core'){
						continue;
					}

					$this->atfp_plugin_category[] = $plugin_name;
					echo '<option value="' . esc_attr( $plugin_name ) . '">' . esc_html( $filter_plugin_name ) . '</option>';
				}
			}
		}

		/**
		 * Get the supported blocks.
		 */
		public function atfp_get_supported_blocks_table() {

			if ( class_exists( 'WP_Block_Type_Registry' ) && method_exists( 'WP_Block_Type_Registry', 'get_all_registered' ) ) {
				$atfp_block_parse_rules      = ATFP_Helper::get_instance()->get_block_parse_rules();

				$blocks_data                 = WP_Block_Type_Registry::get_instance()->get_all_registered();

				$atfp_supported_blocks       = isset($atfp_block_parse_rules['AtfpBlockParseRules']) ? $atfp_block_parse_rules['AtfpBlockParseRules'] : array();
				$atfp_supported_blocks_names = array_keys( $atfp_supported_blocks );
				$s_no                        = 1;
				$atfp_post_id                = ATFP_Helper::get_custom_block_post_id();
				
				// $filter_blocks_data = array_filter( $blocks_data, function( $block ) {
				// 	return !in_array($block->category, array( 'media', 'reusable' ));
				// } );

				$filter_blocks_data=$blocks_data;

				foreach ( $filter_blocks_data as $block ) {

					$block_name  = esc_html( $block->name );
					$block_title = esc_html( $block->title );
					$status      = ! in_array( $block_name, $atfp_supported_blocks_names ) ? 'Unsupported' : 'Supported'; // You can modify this logic based on your requirements
					$modify_text = ! in_array( $block_name, $atfp_supported_blocks_names ) ? esc_html__( 'Add', 'autopoly-ai-translation-for-polylang' ) : esc_html__( 'Edit', 'autopoly-ai-translation-for-polylang' );
					$modify_link = '<a href="' . esc_url( admin_url( 'post.php?post=' . esc_attr( $atfp_post_id ) . '&action=edit&atfp_new_block=' ) . esc_attr( $block_name ) ) . '">' . $modify_text . '</a>'; // Modify link
					$modify_link = '<a href="' . esc_url( admin_url( 'post.php?post=' . esc_attr( $atfp_post_id ) . '&action=edit&atfp_new_block=' ) . esc_attr( $block_name ) ) . '">' . $modify_text . '</a>'; // Modify link

					echo '<tr data-block-name="' . esc_attr( strtolower( $block_name ) ) . '" data-block-status="' . esc_attr( strtolower( $status ) ) . '" >';
					echo '<td>' . esc_html($s_no++) . '</td>';
					echo '<td>' . esc_html($block_name) . '</td>';
					echo '<td>' . esc_html($block_title) . '</td>';
					echo '<td>' . esc_html($status) . '</td>';
					echo '<td>' . wp_kses($modify_link, array('a' => array('href' => array(), 'target' => array(), 'rel' => array()))) . '</td>';
					echo '</tr>';
				}
			}

		}

		private function atfp_supported_block_name($block_name){
			$predfined_blocks = array(
				'ub' => 'Ultimate Blocks',
				'uagb' => 'Spectra',
				'themeisle-blocks' => 'Otter Blocks'
			);
			
			if(array_key_exists($block_name, $predfined_blocks)){
				return $predfined_blocks[$block_name];
			}

			return $block_name;
		}
	}

	ATFP_Supported_Blocks::get_instance();
}
