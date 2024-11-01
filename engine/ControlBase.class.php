<?php
namespace ZeroWpOneClickPresets;

class ControlBase extends \WP_Customize_Control {
	public function __construct( $manager, $id, $args = array() ) {
		$this->id = $id;
		$this->args = $this->_parseArgs( $args );
		parent::__construct( $manager, $id, $args);
	}

	protected function _parseArgs( $args ){
		return wp_parse_args( $args, $this->defaultArgs() );
	}

	public function defaultArgs(){
		return array();
	}

	public function fieldContent(){}

	public function render_content(){
		?>
		<div class="<?php echo $this->id; ?>_<?php echo $this->type; ?>">
			<label>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php if ( ! empty( $this->description ) ) : ?>
					<span class="description customize-control-description"><?php echo $this->description ; ?></span>
				<?php endif; ?>
			</label>
				
			<?php 
				$this->fieldContent();
			?>

		</div>
		<?php
	}
}