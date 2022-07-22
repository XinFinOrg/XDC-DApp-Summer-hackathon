/**
 * Helper Functions
 */

// Group terms by parent
export function GroupByParent( terms, top_level_parent_id ) {
	var map = {}, node, roots = [], i;
	
	for ( i = 0; i < terms.length; i += 1 ) {
		map[ terms[ i ].id ] = i; // initialize the map
		terms[ i ].children = []; // initialize the children		
	}	

	for ( i = 0; i < terms.length; i += 1 ) {
		node = terms[ i ];
		if ( node.parent == top_level_parent_id ) {
			roots.push( node );			
		} else if ( node.parent > 0 ) {
			terms[ map[ node.parent ] ].children.push( node );
		}
	}	

	return roots;
}

// Build tree array from flat array
export function BuildTree( terms, tree = [], prefix = '' ) {
	var i;
	
	for ( i = 0; i < terms.length; i += 1 ) {
		tree.push({
			label: prefix + terms[ i ].name,
			value: terms[ i ].id,
		});	

		if ( terms[ i ].children.length > 0 ) {
			BuildTree( terms[ i ].children, tree, prefix.trim() + '--- ' );
		}
	}	

	return tree;
}