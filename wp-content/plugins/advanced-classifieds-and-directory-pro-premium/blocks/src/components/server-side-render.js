// Import block dependencies and components
import isEqual from 'lodash/isEqual';

// Components
const { 
	__, 
	sprintf 
} = wp.i18n;

const {
	Placeholder,
	Spinner
} = wp.components; 

const {
	Component,
	RawHTML,
} = wp.element;

const { addQueryArgs } = wp.url;

/**
 * Create an ACADPServerSideRender Component.
 */
export function ACADPRendererPath( block, attributes = null, urlQueryArgs = {} ) {
	return addQueryArgs( `/wp/v2/block-renderer/${ block }`, {
		context: 'edit',
		...( null !== attributes ? { attributes } : {} ),
		...urlQueryArgs,
	} );
}

export class ACADPServerSideRender extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			response: null,
		};
	}

	componentDidMount() {
		this.isStillMounted = true;
		this.fetch( this.props );
	}

	componentWillUnmount() {
		this.isStillMounted = false;
	}

	componentDidUpdate( prevProps, prevState ) {
		if ( ! isEqual( prevProps, this.props ) ) {
			this.fetch( this.props );
		}
		
		if ( this.state.response !== prevState.response ) {
			if ( this.props.onChange ) {
				this.props.onChange();				
			}
		}
	}

	fetch( props ) {
		if ( null !== this.state.response ) {
			this.setState( { response: null } );
		}
		const { block, attributes = null, urlQueryArgs = {} } = props;

		const path = ACADPRendererPath( block, attributes, urlQueryArgs );

		return wp.apiFetch( { path } )
			.then( ( response ) => {
				if ( this.isStillMounted && response && response.rendered ) {
					this.setState( { response: response.rendered } );
				}
			} )
			.catch( ( error ) => {
				if ( this.isStillMounted ) {
					this.setState( { response: {
						error: true,
						errorMsg: error.message,
					} } );
				}
			} );
	}

	render() {
		const response = this.state.response;
		if ( ! response ) {
			return (
				<Placeholder><Spinner /></Placeholder>
			);
		} else if ( response.error ) {
			// translators: %s: error message describing the problem
			const errorMessage = sprintf( __( 'Error loading block: %s' ), response.errorMsg );
			return (
				<Placeholder>{ errorMessage }</Placeholder>
			);
		} else if ( ! response.length ) {
			return (
				<Placeholder>{ __( 'No results found.' ) }</Placeholder>
			);
		}

		return (
			<RawHTML key="html">{ response }</RawHTML>
		);
	}
}

export default ACADPServerSideRender;
