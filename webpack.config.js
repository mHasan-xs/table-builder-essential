const defaultConfig = require('@wordpress/scripts/config/webpack.config.js');
const path = require('path');

module.exports = {
	...defaultConfig,
	resolve: {
		...defaultConfig.resolve,
		alias: {
			'@': path.resolve(__dirname, 'src/'),
			'assets': path.resolve(__dirname, 'assets/'),
			'@components': path.resolve(__dirname, 'src/components/'),
			'@helper': path.resolve(__dirname, 'src/helper/'),
		},
	},
	entry: {
		...defaultConfig.entry(),
		"template-library/template-library": ['./src/template-library/editor-template-library.js'],
	},
	output: {
		...defaultConfig.output,
		path: path.resolve(__dirname, 'build'),
		filename: '[name].js',
	},
};