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
 * Create an ACADPLocationsEdit Component.
 */
class ACADPLocationsEdit extends Component {

	constructor() {
		super( ...arguments );
	}

	render() {
		return (
			<Fragment>
				<Disabled>
					<ServerSideRender block="acadp/locations" />
				</Disabled>				
			</Fragment>
		);
	}	

}

export default ACADPLocationsEdit;