// Components
const { __ } = wp.i18n;

const {
	Disabled,
	PanelBody,
	SelectControl,
	ServerSideRender,
	ToggleControl
} = wp.components; 

const { 
	Component,
	Fragment
} = wp.element;

const {	InspectorControls } = wp.editor;

/**
 * Create an ACADPSearchFormEdit Component.
 */
class ACADPSearchFormEdit extends Component {

	constructor() {

		super( ...arguments );
		this.toggleAttribute = this.toggleAttribute.bind( this );

	}

	toggleAttribute( attribute ) {
		return ( newValue ) => {
			this.props.setAttributes( { [ attribute ]: newValue } );
		};
	}

	render() {

		const { 
			attributes,
			setAttributes
		} = this.props;

		const {
			style,
			location,		
			category,
			custom_fields,
			price
		} = attributes;

		return (
			<Fragment>
				<InspectorControls>
					<PanelBody title={ __( 'Search form settings' ) }>
						<SelectControl
							label={ __( 'Select layout' ) }
							value={ style }
							options={ [
								{ 
									label: __( 'Vertical' ), 
									value: 'vertical' 
								},
								{ 
									label: __( 'Inline' ), 
									value: 'inline' 
								}
							] }
							onChange={ ( value ) => setAttributes( { style: value } ) }
						/>

						<ToggleControl
							label={ __( 'Search by Category' ) }
							checked={ category }
							onChange={ this.toggleAttribute( 'category' ) }
						/>						

						<ToggleControl
							label={ __( 'Search by Location' ) }
							checked={ location }
							onChange={ this.toggleAttribute( 'location' ) }
						/>	

						<ToggleControl
							label={ __( 'Search by Custom Fields' ) }
							checked={ custom_fields }
							onChange={ this.toggleAttribute( 'custom_fields' ) }
						/>

						<ToggleControl
							label={ __( 'Search by Price' ) }
							checked={ price }
							onChange={ this.toggleAttribute( 'price' ) }
						/>				
					</PanelBody>
				</InspectorControls>

				<Disabled>
					<ServerSideRender 
						block="acadp/search-form" 
						attributes={ attributes }
					/>
				</Disabled>				
			</Fragment>
		);

	}	

}

export default ACADPSearchFormEdit;