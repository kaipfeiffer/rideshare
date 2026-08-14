(() => {
	const widgets = document.querySelectorAll('[data-rideshare-riding-widget]');

	const create_element = (tag, attributes = {}, children = []) => {
		const element = document.createElement(tag);

		Object.entries(attributes).forEach(([key, value]) => {
			if (value === false || value === null || value === undefined) {
				return;
			}

			if (key === 'className') {
				element.className = value;
				return;
			}

			if (key === 'text') {
				element.textContent = value;
				return;
			}

			if (key.startsWith('on') && typeof value === 'function') {
				element.addEventListener(key.slice(2).toLowerCase(), value);
				return;
			}

			element.setAttribute(key, value);
		});

		children.forEach((child) => {
			if (typeof child === 'string') {
				element.appendChild(document.createTextNode(child));
				return;
			}

			if (child) {
				element.appendChild(child);
			}
		});

		return element;
	};

	const render_options = (stops, selected_id) => [
		create_element('option', { value: '', text: '' }),
		...stops.map((stop) =>
			create_element('option', {
				value: stop.id,
				text: stop.title,
				selected: Number(selected_id) === Number(stop.id),
			})
		),
	];

	const render_notice = (message, type = 'info') => {
		if (!message) {
			return null;
		}

		return create_element('div', {
			className: `rideshare-riding-widget__notice rideshare-riding-widget__notice--${type}`,
			role: 'status',
			text: message,
		});
	};

	const render_login_prompt = (state) => create_element('div', {
		className: 'rideshare-riding-widget__notice rideshare-riding-widget__notice--info rideshare-riding-widget__login-prompt',
	}, [
		create_element('p', { text: state.labels.login_required }),
		create_element('a', {
			className: 'rideshare-riding-widget__secondary-button',
			href: state.login_url,
			text: state.labels.login,
		}),
	]);

	const get_booking_data = (item, state) => {
		const data = new FormData();
		data.set('action', state.booking_action);
		data.set('nonce', state.booking_nonce);
		data.set('rideshare_riding_id', item.id);
		data.set('rideshare_booking_seats', 1);

		return data;
	};

	const book_riding = async (item, state, set_state) => {
		set_state({ booking_busy: item.id, notice: null });

		try {
			const response = await fetch(state.ajax_url, {
				method: 'POST',
				credentials: 'same-origin',
				body: get_booking_data(item, state),
			});
			const payload = await response.json();
			const data = payload.data || {};

			if (!payload.success) {
				set_state({
					booking_busy: null,
					riding_items: data.riding_items || state.riding_items,
					user_riding_items: data.user_riding_items || state.user_riding_items,
					notice: { type: 'error', message: data.message || '' },
				});
				return;
			}

			set_state({
				booking_busy: null,
				riding_items: data.riding_items || state.riding_items,
				user_riding_items: data.user_riding_items || state.user_riding_items,
				notice: { type: 'success', message: data.message || '' },
			});
		} catch (error) {
			set_state({
				booking_busy: null,
				notice: { type: 'error', message: error.message },
			});
		}
	};

	const render_booking_action = (item, state, set_state) => {
		if (!state.can_create || !['offer', 'request'].includes(item.type)) {
			return null;
		}

		const is_busy = Number(state.booking_busy) === Number(item.id);
		const label = is_busy ? state.labels.booking : (item.booking_status_label || state.labels.book);

		return create_element('div', { className: 'rideshare-riding-widget__booking-actions' }, [
			create_element('button', {
				className: 'rideshare-riding-widget__secondary-button',
				type: 'button',
				disabled: is_busy || !item.can_book,
				text: label,
				onClick: () => book_riding(item, state, set_state),
			}),
		]);
	};

	const render_riding_items = (items, state, set_state, show_booking_action = true) => create_element(
		'ol',
		{ className: 'rideshare-riding-widget__offer-list' },
		items.map((item) => create_element(
			'li',
			{},
			[
				create_element('details', { className: 'rideshare-riding-widget__ride' }, [
					create_element('summary', { className: 'rideshare-riding-widget__ride-summary' }, [
						create_element('span', {
							className: `rideshare-riding-widget__type-icon rideshare-riding-widget__type-icon--${item.type}`,
							role: 'img',
							'aria-label': item.type_label,
							title: item.type_label,
							text: item.type === 'offer' ? '+' : '?',
						}),
						create_element('span', { className: 'rideshare-riding-widget__summary-text' }, [
							create_element('span', {
								className: 'rideshare-riding-widget__route',
								text: `${item.origin_label || ''} -> ${item.destination_label || ''}`,
							}),
							create_element('span', {
								className: 'rideshare-riding-widget__period',
								text: item.period_label || '',
							}),
						]),
					]),
					create_element('div', { className: 'rideshare-riding-widget__ride-details' }, [
						create_element('dl', {}, [
							create_element('div', {}, [
								create_element('dt', { text: state.labels.type }),
								create_element('dd', { text: item.type_label || '' }),
							]),
							item.is_past ? create_element('div', {}, [
								create_element('dt', { text: state.labels.status }),
								create_element('dd', { text: state.labels.past }),
							]) : null,
							item.passengers ? create_element('div', {}, [
								create_element('dt', { text: item.passengers_label || state.labels.passengers }),
								create_element('dd', { text: String(item.passengers) }),
							]) : null,
							item.description ? create_element('div', {}, [
								create_element('dt', { text: state.labels.description }),
								create_element('dd', { text: item.description }),
							]) : null,
						]),
						show_booking_action ? render_booking_action(item, state, set_state) : null,
					]),
				]),
			]
		))
	);

	const render_riding_section = (title, empty_label, items, state, set_state, show_booking_action = true) => {
		const children = [
			create_element('h3', { text: title }),
		];

		if (!items.length) {
			children.push(create_element('p', {
				className: 'rideshare-riding-widget__empty',
				text: empty_label,
			}));
		} else {
			children.push(render_riding_items(items, state, set_state, show_booking_action));
		}

		return create_element('section', { className: 'rideshare-riding-widget__offers' }, children);
	};

	const render_tabs = (state, set_state) => {
		if (!state.can_create) {
			return render_riding_section(
				state.labels.rides,
				state.labels.no_items,
				state.riding_items,
				state,
				set_state
			);
		}

		const active_tab = state.active_tab || 'rides';
		const current_items = active_tab === 'my_rides' ? state.user_riding_items : state.riding_items;
		const current_title = active_tab === 'my_rides' ? state.labels.my_rides : state.labels.rides;
		const current_empty = active_tab === 'my_rides' ? state.labels.no_user_items : state.labels.no_items;

		return create_element('div', { className: 'rideshare-riding-widget__tabs' }, [
			create_element('div', { className: 'rideshare-riding-widget__tab-buttons', role: 'tablist' }, [
				create_element('button', {
					className: active_tab === 'rides' ? 'rideshare-riding-widget__tab-button rideshare-riding-widget__tab-button--active' : 'rideshare-riding-widget__tab-button',
					type: 'button',
					role: 'tab',
					'aria-selected': active_tab === 'rides' ? 'true' : 'false',
					text: state.labels.rides,
					onClick: () => set_state({ active_tab: 'rides' }),
				}),
				create_element('button', {
					className: active_tab === 'my_rides' ? 'rideshare-riding-widget__tab-button rideshare-riding-widget__tab-button--active' : 'rideshare-riding-widget__tab-button',
					type: 'button',
					role: 'tab',
					'aria-selected': active_tab === 'my_rides' ? 'true' : 'false',
					text: state.labels.my_rides,
					onClick: () => set_state({ active_tab: 'my_rides' }),
				}),
			]),
			render_riding_section(
				current_title,
				current_empty,
				current_items,
				state,
				set_state,
				active_tab === 'rides'
			),
		]);
	};

	const render_field = (label, input) => create_element(
		'label',
		{ className: 'rideshare-riding-widget__field' },
		[
			create_element('span', { text: label }),
			input,
		]
	);

	const get_form_data = (form, state) => {
		const data = new FormData(form);
		data.set('action', state.action);
		data.set('nonce', state.nonce);

		return data;
	};

	const render_form = (state, set_state) => {
		const labels = state.labels;
		const mode = state.mode || 'search';
		const submit = async (event) => {
			event.preventDefault();
			set_state({ busy: true, notice: null });

			try {
				const response = await fetch(state.ajax_url, {
					method: 'POST',
					credentials: 'same-origin',
					body: get_form_data(event.currentTarget, state),
				});
				const payload = await response.json();
				const data = payload.data || {};

				if (!payload.success) {
					set_state({
						busy: false,
						notice: { type: 'error', message: data.message || '' },
					});
					return;
				}

				set_state({
					busy: false,
					mode: null,
					riding_items: data.riding_items || state.riding_items,
					user_riding_items: data.user_riding_items || state.user_riding_items,
					active_tab: data.user_riding_items ? 'my_rides' : state.active_tab,
					notice: { type: 'success', message: data.message || '' },
				});
			} catch (error) {
				set_state({
					busy: false,
					notice: { type: 'error', message: error.message },
				});
			}
		};

		const mode_field = create_element('fieldset', { className: 'rideshare-riding-widget__mode' }, [
			create_element('legend', { text: labels.mode_label }),
			create_element('label', {}, [
				create_element('input', {
					type: 'radio',
					name: 'rideshare_riding_request_type',
					value: 'search',
					checked: mode === 'search',
					onChange: () => set_state({ mode: 'search' }),
				}),
				labels.search_label,
			]),
			create_element('label', {}, [
				create_element('input', {
					type: 'radio',
					name: 'rideshare_riding_request_type',
					value: 'offer',
					checked: mode === 'offer',
					onChange: () => set_state({ mode: 'offer' }),
				}),
				labels.offer_label,
			]),
		]);

		return create_element('form', { className: 'rideshare-riding-widget__form', onSubmit: submit }, [
			mode_field,
			render_field(labels.origin, create_element('select', {
				name: 'rideshare_riding_origin_id',
				required: true,
				'aria-label': labels.origin,
			}, render_options(state.stops))),
			render_field(labels.destination, create_element('select', {
				name: 'rideshare_riding_destination_id',
				required: true,
				'aria-label': labels.destination,
			}, render_options(state.stops))),
			render_field(labels.start_date, create_element('input', {
				type: 'datetime-local',
				name: 'rideshare_riding_start_date',
				required: true,
			})),
			render_field(labels.end_date, create_element('input', {
				type: 'datetime-local',
				name: 'rideshare_riding_end_date',
			})),
			render_field(labels.passengers, create_element('input', {
				type: 'number',
				name: 'rideshare_riding_passengers',
				min: '1',
				max: '255',
				value: '1',
				required: true,
			})),
			create_element('label', { className: 'rideshare-riding-widget__field rideshare-riding-widget__field--wide' }, [
				create_element('span', { text: labels.description }),
				create_element('textarea', {
					name: 'rideshare_riding_description',
					rows: '4',
				}),
			]),
			create_element('div', { className: 'rideshare-riding-widget__form-actions' }, [
				create_element('button', {
					className: 'rideshare-riding-widget__submit',
					type: 'submit',
					disabled: state.busy,
					text: state.busy ? labels.saving : labels.save,
				}),
				create_element('button', {
					className: 'rideshare-riding-widget__secondary-button',
					type: 'button',
					text: labels.cancel,
					onClick: () => set_state({ mode: null }),
				}),
			]),
		]);
	};

	const init_widget = (root) => {
		const data_element = root.querySelector('.rideshare-riding-widget__data');
		if (!data_element) {
			return;
		}

		let state;
		try {
			state = JSON.parse(data_element.textContent);
		} catch (error) {
			return;
		}

		state = {
			...state,
			busy: false,
			booking_busy: null,
			mode: null,
			notice: null,
			active_tab: 'rides',
			user_riding_items: state.user_riding_items || [],
		};

		const set_state = (changes) => {
			state = { ...state, ...changes };
			render();
		};

		const render = () => {
			const labels = state.labels;
			const children = [
				create_element('h2', { text: labels.title }),
				render_notice(state.notice?.message, state.notice?.type),
				!state.can_create ? render_login_prompt(state) : null,
				state.mode && state.can_create ? render_form(state, set_state) : null,
				render_tabs(state, set_state),
				state.can_create ? create_element('div', { className: 'rideshare-riding-widget__actions' }, [
					create_element('button', {
						className: 'rideshare-riding-widget__submit',
						type: 'button',
						text: labels.create_offer,
						onClick: () => set_state({ mode: 'offer' }),
					}),
					create_element('button', {
						className: 'rideshare-riding-widget__secondary-button',
						type: 'button',
						text: labels.create_request,
						onClick: () => set_state({ mode: 'search' }),
					}),
				]) : null,
			];

			root.replaceChildren(create_element('div', { className: 'rideshare-riding-widget__client-content' }, children));
		};

		render();
	};

	widgets.forEach(init_widget);
})();
