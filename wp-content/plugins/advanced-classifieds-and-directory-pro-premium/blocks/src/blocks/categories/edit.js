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
 * Create an ACADPCategoriesEdit Component.
 */
class ACADPCategoriesEdit extends Component {

	constructor() {
		super( ...arguments );
	}

	render() {
		return (
			<Fragment>
				<Disabled>
					<ServerSideRender block="acadp/categories" />
				</Disabled>				
			</Fragment>
		);
	}	

}

export default ACADPCategoriesEdit;