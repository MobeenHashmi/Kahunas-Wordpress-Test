( function( wp ) {
	const { registerBlockType } = wp.blocks;
	const { useState, useEffect } = wp.element;
	const { SelectControl } = wp.components;
	const { apiFetch } = wp;

	registerBlockType( 'plugin-test/top-users', {
		title: 'Top Users',
		icon: 'groups',
		category: 'widgets',
		attributes: {
			order: {
				type: 'string',
				default: 'desc'
			}
		},
		edit: function( props ) {
			const { attributes: { order }, setAttributes } = props;
			const [ users, setUsers ] = useState( [] );
			const [ loading, setLoading ] = useState( true );

			// Fetch top users whenever the sort order changes.
			useEffect( () => {
				setLoading( true );
				apiFetch( { path: '/plugin-test/v1/top-users?order=' + order } ).then( ( response ) => {
					setUsers( response );
					setLoading( false );
				} );
			}, [ order ] );

			return (
				<div className="plugin-test-top-users-block">
					<SelectControl
						label="Sort Order"
						value={ order }
						options={ [
							{ label: 'Descending', value: 'desc' },
							{ label: 'Ascending', value: 'asc' }
						] }
						onChange={ ( newOrder ) => setAttributes( { order: newOrder } ) }
					/>
					{ loading ? <p>Loading...</p> : (
						<ul>
							{ users.map( ( user ) => (
								<li key={ user.ID }>
									{ user.display_name } - ${ parseFloat( user.total_order_value ).toFixed( 2 ) }
								</li>
							) ) }
						</ul>
					) }
				</div>
			);
		},
		// Save is null because the block is rendered dynamically on the front-end.
		save: function() {
			return null;
		}
	} );
} )( window.wp );
