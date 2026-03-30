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
	const ServerSideRender = wp.serverSideRender;

	wp.blocks.registerBlockType('plaidact/timeline', {
		title: __('Timeline Agenda', 'plaidact-breves-feed'),
		icon: 'calendar-alt',
		category: 'widgets',
		attributes: {
			term: { type: 'string', default: '' },
			fillEmptyMonths: { type: 'boolean', default: false }
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
						el(ToggleControl, {
							label: __('Afficher aussi les mois vides', 'plaidact-breves-feed'),
							checked: !!props.attributes.fillEmptyMonths,
							onChange: function (v) { props.setAttributes({ fillEmptyMonths: !!v }); }
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
