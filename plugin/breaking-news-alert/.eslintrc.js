module.exports = {
	root: true,
	extends: [
		'eslint:recommended',
		'plugin:react/recommended',
		'plugin:jsx-a11y/recommended',
		'plugin:prettier/recommended',
		'plugin:@wordpress/eslint-plugin/recommended',
	],
	plugins: ['react', 'prettier'],
	parserOptions: {
		ecmaVersion: 2020,
		sourceType: 'module',
		ecmaFeatures: {
			jsx: true,
		},
	},
	rules: {
		'react/prop-types': 'off',
		'prettier/prettier': 'error', // enforce Prettier formatting via ESLint
	},
	settings: {
		react: {
			version: 'detect',
		},
	},
};
