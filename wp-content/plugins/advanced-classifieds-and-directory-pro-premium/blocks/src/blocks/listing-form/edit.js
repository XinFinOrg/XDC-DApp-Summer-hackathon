// Components
const {
	Disabled,
	ServerSideRender
} = wp.components; 

const { 
	Component,
	Fragment
} = wp.element;

/**
 * Create an ACADPListingFormEdit Component.
 */
class ACADPListingFormEdit extends Component {

	constructor() {
		super( ...arguments );
	}

	render() {
		return (
			<Fragment>
				<Disabled>
					<ServerSideRender block="acadp/listing-form" />
				</Disabled>				
			</Fragment>
		);
	}	

}

export default ACADPListingFormEdit;