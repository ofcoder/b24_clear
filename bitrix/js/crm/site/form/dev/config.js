;(function () {

	window.b24formDev = window.b24formDev || {};
	window.b24formDev.config = {};

	window.b24formDev.config.main = {
		node: null,
		/*
		provider: {
			form: {
				id: 40,
				sec: 'xxxx',
				lang: 'ru',
			},
			user: {
				id: 40,
				sec: 'xxxx',
				lang: 'ru',
			},
			entities: {

			},
			submit: null,
		},
		*/

		language: 'ru',
		title: 'Регистрация участника',
		desc: 'Мы заботимся о качестве нашего пространства',
		useSign: true,
		date: {
			dateFormat: 'MM/DD/YYYY',
			dateTimeFormat: 'MM/DD/YYYY HH:mm:ss',
			sundayFirstly: true,
		},
		currency: {
			code: 'USD',
			title: 'doll.',
			format: '$#',
		},

		agreements: [
			{
				id: 'mailings',
				name: 'AGR_MAIL',
				label: 'Я согласен получать рассылки',
				value: 'Y',
				required: true,
				checked: true,
				content: 'http://cp.silaev.bx/test/',
			},
			{
				id: '152-fz',
				name: 'AGR_FZ',
				label: 'Нажимая на кнопку я соглашаюсь не читая',
				value: 'Y',
				required: true,
				checked: false,
				content: {
					title: 'Согласие на обработку данных',
					text: 'Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста ',
				},
			},
			{
				id: '152-fz',
				name: 'AGR_FZ',
				label: 'Нажимая на кнопку я соглашаюсь не читая',
				required: false,
				checked: false,
				content: {
					title: 'Согласие на обработку данных',
					html: '<p>Много текста Много текста Много текста Много</p>\n								<br><br><a href="/test/">ссылка</a><br><br>\n								текста Много текста Много текста Много текста Много\n								текста Много текста Много текста Много текста Много\n								текста Много текста Много текста Много текста Много\n								текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста Много текста ',
				},
			}
		],

		fields: [
			{
				type: 'page',
				label: 'String fields',
			},
			{
				id: 'DATETIME_1',
				name: 'DATETIME_1',
				type: 'datetime',
				required: false,
				multiple: false,
				label: 'Поле Дата-время',
			},
			{
				id: 'LAYOUT_1',
				name: 'LAYOUT_1',
				type: 'layout',
				content: {
					type: 'hr',
				}
			},
			{
				id: 'DATE_1',
				name: 'DATE_1',
				type: 'date',
				required: false,
				multiple: false,
				label: 'Поле Дата',
			},
			{
				id: 'LAYOUT_2',
				name: 'LAYOUT_2',
				type: 'layout',
				content: {
					type: 'br',
				}
			},
			{
				id: 'LIST_1',
				name: 'LIST_1',
				type: 'list',
				required: false,
				multiple: true,
				label: 'Виды отдыха',
				items: [
					{
						label: 'Горнолыжный отдых',
						value: 'act',
						selected: false,
						price: 100,
						quantity: {min: 2, max: 50, step: 2, unit: 'шт.'},
					},
					{
						label: 'Пляжный отдых',
						value: 'beach',
						selected: false,
						pics: [
							'https://bipbap.ru/wp-content/uploads/2017/10/0_8eb56_842bba74_XL-640x400.jpg',
							'https://24tv.ua/resources/photos/news/201810/1044696.jpg',
						],
						price: 100,
						discount: 10,
						quantity: {step: 100, unit: 'грамм'},
					},
					{
						label: 'Диванный отдых',
						value: 'home',
						selected: false,
						pics: [
							'https://images2.alphacoders.com/765/thumb-1920-765642.png',
							'https://cdn.humoraf.ru/wp-content/uploads/2017/08/23-14.jpg',
							'https://img.fonwall.ru/o/22/lodka_bereg_voda_more_okean_12.jpg',
						],
						price: 95,
						discount: 7,
						quantity: {max: 3},
					},
					{label: 'Отдых #1',value: 'r1',},
					{label: 'Отдых #2',value: 'r2',},
					{label: 'Отдых #3',value: 'r3',},
					{label: 'Отдых #4',value: 'r4',},
					{label: 'Отдых #5',value: 'r5',},
					{label: 'Отдых #6',value: 'r6',},
					{label: 'Отдых #7',value: 'r7',},
					{label: 'Отдых #8',value: 'r8',},
					{label: 'Отдых #9',value: 'r9',},
					{label: 'Отдых #10',value: 'r10',},
					{label: 'Отдых #11',value: 'r11',},
				]
			},
			{
				id: 'LAYOUT_3',
				name: 'LAYOUT_3',
				type: 'layout',
				label: 'Section #1',
				content: {
					type: 'section',
				}
			},
			{
				id: 'PRODUCT_1',
				name: 'PRODUCT_1',
				type: 'product',
				required: false,
				multiple: true,
				label: 'Выберите товары',
				bigPic: true,
				items: [
					{
						label: 'Товар без фото',
						value: '111111',
						selected: false,
						price: 100,
						quantity: {min: 2, max: 50, step: 2, unit: 'шт.'},
					},
					{
						label: 'Flowers',
						value: '111111',
						selected: false,
						pics: [
							'https://bipbap.ru/wp-content/uploads/2017/10/0_8eb56_842bba74_XL-640x400.jpg',
							'https://24tv.ua/resources/photos/news/201810/1044696.jpg',
						],
						price: 0.87,
						discount: 0.11,
						quantity: {step: 100, unit: 'грамм'},
					},
					{
						label: 'Moon night',
						value: '222222',
						selected: false,
						pics: [
							'https://images2.alphacoders.com/765/thumb-1920-765642.png',
							'https://cdn.humoraf.ru/wp-content/uploads/2017/08/23-14.jpg',
							'https://img.fonwall.ru/o/22/lodka_bereg_voda_more_okean_12.jpg',
						],
						price: 95,
						discount: 7,
						quantity: {max: 3},
					},
				]
			},

			{
				id: 'NAME_SINGLE_FILLED',
				name: 'NAME_SINGLE_FILLED',
				type: 'name',
				label: 'String single filled',
				values: [''],
			},
			{
				id: 'EMAIL_SINGLE_EMPTY',
				name: 'EMAIL_SINGLE_EMPTY',
				type: 'email',
				label: 'Email single empty',
				required: true,
				values: [''],
			},

			{
				id: 'PHONE_MULTI_FILLED',
				name: 'PHONE_MULTI_FILLED',
				type: 'phone',
				multiple: true,
				label: 'Phone multi filled',
				values: ['+7'],
			},
			{
				id: 'INTEGER_MULTI_EMPTY',
				name: 'INTEGER_MULTI_EMPTY',
				type: 'integer',
				multiple: true,
				label: 'Integer multi empty',
			},
			{
				id: 'DOUBLE_SINGLE_EMPTY',
				name: 'DOUBLE_SINGLE_EMPTY',
				type: 'double',
				label: 'Double multi empty',
			},
			{
				type: 'page',
				label: 'Text fields',
			},
			{
				id: 'TEXT_SINGLE_FILLED',
				name: 'TEXT_SINGLE_FILLED',
				type: 'text',
				//required: true,
				label: 'Text single filled',
				value: 'Мой коммент 1',
			},
			{
				id: 'TEXT_SINGLE_EMPTY',
				name: 'TEXT_SINGLE_EMPTY',
				type: 'text',
				multiple: false,
				//required: true,
				label: 'Text single empty',
				values: [],
			},
			{
				id: 'TEXT_MULTI_FILLED',
				name: 'TEXT_MULTI_FILLED',
				type: 'text',
				multiple: true,
				//required: true,
				label: 'Text multi filled',
				values: ['Мой коммент 1', 'Мой коммент 2'],
			},
			{
				id: 'TEXT_MULTI_EMPTY',
				name: 'TEXT_MULTI_EMPTY',
				type: 'text',
				multiple: true,
				required: true,
				label: 'Text multi empty',
				values: [],
			},
			{
				type: 'page',
				label: 'List fields',
			},
			{
				id: 'LEAD_SOURCE',
				name: 'LEAD_SOURCE',
				type: 'select',
				multiple: true,
				//required: true,
				label: 'Source',
				items: [
					{
						label: 'Instagram',
						value: 'ig',
						selected: false,
					},
					{label: 'Facebook', value: 'fb', selected: true},
					{label: 'VKontakte', value: 'vk', selected: true},
					{label: 'Google', value: 'go', selected: false},
					{label: 'Yandex', value: 'ya', selected: false},
				]
			},
			{
				id: 'LEAD_TYPE',
				name: 'LEAD_TYPE',
				type: 'select',
				label: 'Type',
				items: [
					{
						label: 'Flowers',
						value: 'a',
						selected: false,
						pics: [
							'https://bipbap.ru/wp-content/uploads/2017/10/0_8eb56_842bba74_XL-640x400.jpg',
							'https://24tv.ua/resources/photos/news/201810/1044696.jpg',
							'http://napirse.ru/pic/9501242711032516.jpg',
							'https://img.fonwall.ru/o/47/podsolnuh-lepestki-jeltyiy-semechki.jpg',
							'http://foto-cvetov.com/barhatcy/barhatcy_1.jpg',
							'https://img11.postila.ru/data/74/4a/97/8a/744a978ae00f369fe32c5853aaaf79fcadafc9f0990e47ca7ab09a571f732bfe.jpg',
						],
					},
					{
						label: 'Moon night',
						value: 'b',
						selected: true,
						pics: [
							'https://images2.alphacoders.com/765/thumb-1920-765642.png',
							'https://cdn.humoraf.ru/wp-content/uploads/2017/08/23-14.jpg',
							'https://img.fonwall.ru/o/22/lodka_bereg_voda_more_okean_12.jpg',
						],
					},
					{label: 'No pics', value: 'c', selected: false},
				]
			},
			{
				id: 'LEAD_TYPE_RADIO',
				name: 'LEAD_TYPE_RADIO',
				type: 'radio',
				label: 'Type radio',
				items: [
					{
						label: 'Flowers',
						value: 'a',
						selected: false,
						pics: [
							'https://bipbap.ru/wp-content/uploads/2017/10/0_8eb56_842bba74_XL-640x400.jpg',
							'https://24tv.ua/resources/photos/news/201810/1044696.jpg',
							'http://napirse.ru/pic/9501242711032516.jpg',
							'https://img.fonwall.ru/o/47/podsolnuh-lepestki-jeltyiy-semechki.jpg',
							'http://foto-cvetov.com/barhatcy/barhatcy_1.jpg',
							'https://img11.postila.ru/data/74/4a/97/8a/744a978ae00f369fe32c5853aaaf79fcadafc9f0990e47ca7ab09a571f732bfe.jpg',
						],
					},
					{
						label: 'Moon night',
						value: 'b',
						selected: true,
						pics: [
							'https://images2.alphacoders.com/765/thumb-1920-765642.png',
							'https://cdn.humoraf.ru/wp-content/uploads/2017/08/23-14.jpg',
							'https://img.fonwall.ru/o/22/lodka_bereg_voda_more_okean_12.jpg',
						],
					},
					{
						label: 'No images',
						value: 'c',
						selected: true
					},
				]
			},
			{
				id: 'CHECKBOX_MULTI',
				name: 'CHECKBOX_MULTI',
				type: 'checkbox',
				label: 'Checkbox multi',
				multiple: true,
				items: [
					{label: 'Type A', value: 'a', selected: true},
					{label: 'Type B', value: 'b', selected: false},
					{label: 'Type C', value: 'c', selected: true},
				]
			},
			{
				id: 'CHECKBOX_SINGLE_CHECKED',
				name: 'CHECKBOX_SINGLE_CHECKED',
				type: 'bool',
				label: 'Checkbox single checked',
				value: 'Y',
				checked: true,
			},
			{
				id: 'CHECKBOX_SINGLE',
				name: 'CHECKBOX_SINGLE',
				type: 'bool',
				label: 'Checkbox single',
				value: 'Y',
				checked: false,
			},
			{
				id: 'CHECKBOX_SINGLE_ITEMS_CHECKED',
				name: 'CHECKBOX_SINGLE_ITEMS_CHECKED',
				type: 'bool',
				label: 'Checkbox single items checked',
				items: [{
					value: 'Y',
					selected: true
				}],
			},
			{
				id: 'CHECKBOX_SINGLE_ITEMS',
				name: 'CHECKBOX_SINGLE_ITEMS',
				type: 'bool',
				label: 'Checkbox single items',
				items: [{
					value: 'Y',
					selected: false
				}],
				required: true,
			},
			{
				type: 'page',
				label: 'File fields',
			},
			{
				id: 'LEAD_LOGO',
				name: 'LEAD_LOGO',
				type: 'file',
				label: 'Company logo',
				required: true,
			},
			{
				id: 'LEAD_PICTURE',
				name: 'LEAD_PICTURE',
				type: 'file',
				label: 'Picture',
				multiple: true,
				required: true,
				values: [
					{
						size: 60000,
						name: 'моя иконка-аватарка.png',
						content: 'data:image/png;base64,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'
					}
				]
			},
		]
	};
})();