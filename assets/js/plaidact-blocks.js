(function (wp) {
	if (!wp || !wp.blocks || !wp.element) {
		return;
	}

	const el = wp.element.createElement;
	const __ = wp.i18n.__;
	const InspectorControls = wp.blockEditor.InspectorControls;
	const PanelBody = wp.components.PanelBody;
	const TextControl = wp.components.TextControl;
	const RangeControl = wp.components.RangeControl;
	const ToggleControl = wp.components.ToggleControl;
	const SelectControl = wp.components.SelectControl;
	const ServerSideRender = wp.serverSideRender;

	wp.blocks.registerBlockType('plaidact/timeline', {
		title: __('Timeline Agenda', 'plaidact-breves-feed'),
		icon: 'calendar-alt',
		category: 'widgets',
		attributes: {
			term: { type: 'string', default: '' },
			title: { type: 'string', default: '' },
			layout: { type: 'string', default: 'vertical' },
			columns: { type: 'number', default: 3 },
			fillEmptyMonths: { type: 'boolean', default: false },
			eventsPerColumn: { type: 'number', default: 0 }
		},
		edit: function (props) {
			return el('div', {}, [
				el(InspectorControls, {},
					el(PanelBody, { title: __('Paramètres Timeline', 'plaidact-breves-feed') },
						el(TextControl, {
							label: __('Slug du terme timeline', 'plaidact-breves-feed'),
							value: props.attributes.term,
							onChange: function (term) { props.setAttributes({ term: term }); }
						}),
						el(TextControl, {
							label: __('Titre (optionnel)', 'plaidact-breves-feed'),
							value: props.attributes.title || '',
							onChange: function (title) { props.setAttributes({ title: title }); }
						}),
						el(SelectControl, {
							label: __('Disposition', 'plaidact-breves-feed'),
							value: props.attributes.layout || 'vertical',
							options: [
								{ label: __('Verticale', 'plaidact-breves-feed'), value: 'vertical' },
								{ label: __('Horizontale', 'plaidact-breves-feed'), value: 'horizontal' }
							],
							onChange: function (layout) { props.setAttributes({ layout: layout }); }
						}),
						el(RangeControl, {
							label: __('Nombre de colonnes (horizontal)', 'plaidact-breves-feed'),
							value: props.attributes.columns || 3,
							onChange: function (n) { props.setAttributes({ columns: n || 3 }); },
							min: 1,
							max: 6
						}),
						el(ToggleControl, {
							label: __('Afficher aussi les mois vides', 'plaidact-breves-feed'),
							checked: !!props.attributes.fillEmptyMonths,
							onChange: function (v) { props.setAttributes({ fillEmptyMonths: !!v }); }
						}),
						el(RangeControl, {
							label: __('Événements max par colonne (horizontal)', 'plaidact-breves-feed'),
							value: props.attributes.eventsPerColumn || 0,
							onChange: function (n) { props.setAttributes({ eventsPerColumn: n || 0 }); },
							min: 0,
							max: 6,
							help: __('0 = empilement classique ; 2 = deux événements par colonne.', 'plaidact-breves-feed')
						})
					)
				),
				el(ServerSideRender, {
					block: 'plaidact/timeline',
					attributes: props.attributes
				})
			]);
		},
		save: function () { return null; }
	});

	wp.blocks.registerBlockType('plaidact/asso-cause-list', {
		title: __('Répertoire Asso par cause', 'plaidact-breves-feed'),
		icon: 'groups',
		category: 'widgets',
		attributes: {
			cause: { type: 'string', default: '' },
			postsToShow: { type: 'number', default: 9 }
		},
		edit: function (props) {
			return el('div', {}, [
				el(InspectorControls, {},
					el(PanelBody, { title: __('Paramètres Asso', 'plaidact-breves-feed') },
						el(TextControl, {
							label: __('Slug de la cause', 'plaidact-breves-feed'),
							value: props.attributes.cause,
							onChange: function (cause) { props.setAttributes({ cause: cause }); }
						}),
						el(RangeControl, {
							label: __('Nombre max', 'plaidact-breves-feed'),
							value: props.attributes.postsToShow,
							onChange: function (n) { props.setAttributes({ postsToShow: n || 9 }); },
							min: 1,
							max: 24
						})
					)
				),
				el(ServerSideRender, {
					block: 'plaidact/asso-cause-list',
					attributes: props.attributes
				})
			]);
		},
		save: function () { return null; }
	});
})(window.wp);
