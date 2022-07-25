// Import block dependencies and components
import { 
	BuildTree,
	GroupByParent
 } from '../../utils/helper';

import ACADPServerSideRender from '../../components/server-side-render';

// Components
const { __ } = wp.i18n;

const {
	Disabled,
	PanelBody,
	RangeControl,
	SelectControl,
	ToggleControl
} = wp.components; 

const { 
	Component,
	Fragment
} = wp.element;

const {	InspectorControls } = wp.editor;

const { withSelect } = wp.data;

const {	applyFilters } = wp.hooks;

/**
 * Create an ACADPListingsEdit Component.
 */
class ACADPListingsEdit extends Component {

	constructor() {

		super( ...arguments );
		
		this.toggleAttribute = this.toggleAttribute.bind( this );
		this.initializeListings = this.initializeListings.bind( this );

	}

	getCategoriesTree() {

		const { categoriesList } = this.props;

		let categories = [{ 
			label: '-- ' + __( 'All Categories' ) + ' --', 
			value: 0
		}];

		if ( categoriesList && categoriesList.length > 0 ) {		
			let grouped = GroupByParent( categoriesList, 0 );
			let tree = BuildTree( grouped );
			
			categories = [ ...categories, ...tree ];
		}

		return categories;

	}

	getLocationsTree() {

		const { locationsList } = this.props;

		let locations = [{ 
			label: '-- ' + __( 'All Locations' ) + ' --', 
			value: 0
		}];

		if ( locationsList && locationsList.length > 0 ) {		
			let grouped = GroupByParent( locationsList, parseInt( acadp.base_location ) );
			let tree = BuildTree( grouped );
			
			locations = [ ...locations, ...tree ];
		}

		return locations;

	}	

	toggleAttribute( attribute ) {
		return ( newValue ) => {
			this.props.setAttributes( { [ attribute ]: newValue } );
		};
	}

	initializeListings() {
		applyFilters( 'acadp_block_listings_init', this.props.attributes );
	}

	render() {

		const { 
			attributes, 
			setAttributes 
		} = this.props;

		const {
			view,
			category,
			location,			
			filterby,
			orderby,
			order,
			columns,
			listings_per_page,
			featured,			
			header,
			pagination,
		} = attributes;

		const categories = this.getCategoriesTree();

		const locations = this.getLocationsTree();

		return (
			<Fragment>
				<InspectorControls>
					<PanelBody title={ __( 'Listings settings' ) }>
						<SelectControl
							label={ __( 'Select layout' ) }
							value={ view }
							options={ [
								{ 
									label: __( 'List' ), 
									value: 'list' 
								},
								{ 
									label: __( 'Grid' ), 
									value: 'grid' 
								},
								{ 
									label: __( 'Map' ), 
									value: 'map' 
								}
							] }
							onChange={ ( value ) => setAttributes( { view: value } ) }
						/>

						<SelectControl
							label={ __( 'Select category' ) }
							value={ category }
							options={ categories }
							onChange={ ( value ) => setAttributes( { category: Number( value ) } ) }
						/>

						<SelectControl
							label={ __( 'Select location' ) }
							value={ location }
							options={ locations }
							onChange={ ( value ) => setAttributes( { location: Number( value ) } ) }
						/>						

						<SelectControl
							label={ __( 'Filter by' ) }
							value={ filterby }
							options={ [
								{ 
									label: __( 'None' ), 
									value: '' 
								},
								{ 
									label: __( 'Featured' ), 
									value: 'featured' 
								}
							] }
							onChange={ ( value ) => setAttributes( { filterby: value } ) }
						/>

						<SelectControl
							label={ __( 'Order by' ) }
							value={ orderby }
							options={ [
								{ 
									label: __( 'Title' ), 
									value: 'title' 
								},
								{ 
									label: __( 'Date posted' ), 
									value: 'date' 
								},
								{ 
									label: __( 'Price' ), 
									value: 'price' 
								},
								{ 
									label: __( 'Views count' ), 
									value: 'views' 
								},
								{ 
									label: __( 'Random sort' ), 
									value: 'rand' 
								}
							] }
							onChange={ ( value ) => setAttributes( { orderby: value } ) }
						/>

						<SelectControl
							label={ __( 'Order' ) }
							value={ order }
							options={ [
								{ 
									label: __( 'Ascending' ), 
									value: 'asc' 
								},
								{ 
									label: __( 'Descending' ), 
									value: 'desc' 
								}
							] }
							onChange={ ( value ) => setAttributes( { order: value } ) }
						/>

						<RangeControl
							label={ __( 'Number of Columns' ) }
							value={ columns }							
							min={ 1 }
							max={ 12 }
							onChange={ ( value ) => setAttributes( { columns: value } ) }
						/>

						<RangeControl
							label={ __( 'Listings per page' ) }
							value={ listings_per_page }							
							min={ 1 }
							max={ 100 }
							onChange={ ( value ) => setAttributes( { listings_per_page: value } ) }
						/>					

						<ToggleControl
							label={ __( 'Show featured' ) }
							help={ __( 'Show or hide featured listings at the top of normal listings. This setting has no value when "Filter by" option is set to "Featured".' ) }
							checked={ featured }
							onChange={ this.toggleAttribute( 'featured' ) }
						/>						

						<ToggleControl
							label={ __( 'Show header' ) }
							help={ __( 'Header = Videos count, Views switcher, Sort by dropdown' ) }
							checked={ header }
							onChange={ this.toggleAttribute( 'header' ) }
						/>	

						<ToggleControl
							label={ __( 'Show pagination' ) }
							checked={ pagination }
							onChange={ this.toggleAttribute( 'pagination' ) }
						/>				
					</PanelBody>
				</InspectorControls>

				<Disabled>
					<ACADPServerSideRender 
						block="acadp/listings" 
						attributes={ attributes }
						onChange={ this.initializeListings() }
					/>
				</Disabled>				
			</Fragment>
		);

	}	

}

export default withSelect( ( select ) => {

	const { getEntityRecords } = select( 'core' );

	const categoriesListQuery = {
		per_page: 100
	};

	const locationsListQuery = {
		per_page: 100
	};

	return {
		categoriesList: getEntityRecords( 'taxonomy', 'acadp_categories', categoriesListQuery ),
		locationsList: getEntityRecords( 'taxonomy', 'acadp_locations', locationsListQuery )
	};

} )( ACADPListingsEdit );