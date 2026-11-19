'use strict';

const { defineConfig, globalIgnores } = require( 'eslint/config' );
const wikimedia = require( 'eslint-config-wikimedia/flat' );

// Directories with their own config, which other configs don't apply to.
// These were directories with `"root": true` in .eslintrc.json.
const rootDirs = {
	resources: [ 'resources/**', 'includes/Skins/ToggleList/**' ],
	qunit: [ 'tests/qunit/**' ]
};

// Shared by resources and tests (was resources/.eslintrc.json)
const resourcesConfig = {
	languageOptions: {
		globals: {
			// From `env: { commonjs: true }`
			global: 'readonly',
			require: 'readonly'
		}
	},
	rules: {
		'jsdoc/no-undefined-types': 'off',
		'max-len': 'error',
		'mediawiki/class-doc': 'off',
		'no-implicit-globals': 'off',
		'no-restricted-properties': [
			'error',
			{
				property: 'mobileFrontend',
				message: 'Minerva should only make use of core code. Any code using mobileFrontend should be placed inside the MobileFrontend extension'
			},
			{
				property: 'done',
				message: 'The method `done` if used with Deferred objects is incompatible with ES6 Promises. Please use `then`.'
			},
			{
				property: 'fail',
				message: 'The method `fail` if used with Deferred objects is incompatible with ES6 Promises. Please use `then`.'
			},
			{
				property: 'always',
				message: 'The method `always` if used with Deferred objects is incompatible with ES6 Promises. Please use `then`.'
			}
		],
		'object-property-newline': 'error',
		'unit-disallowed-list': 'off'
	}
};

module.exports = defineConfig( [
	globalIgnores( [
		'docs/',
		'a11y/',
		'libs/',
		'vendor/',
		'tests/resource-loader-bundlesize.js',
		'coverage/'
	] ),

	// Gruntfile.js, etc.
	{
		ignores: Object.values( rootDirs ).flat(),
		extends: [ wikimedia( 'server' ) ]
	},

	// Code which runs in MediaWiki
	{
		files: rootDirs.resources,
		extends: [ wikimedia( 'client', 'jquery', 'mediawiki' ), resourcesConfig ]
	},

	// QUnit tests
	{
		files: rootDirs.qunit,
		extends: [ wikimedia( 'client', 'jquery', 'mediawiki', 'qunit' ), resourcesConfig ],
		rules: {
			'no-jquery/no-parse-html-literal': 'off',
			'no-restricted-properties': 'off'
		}
	}
] );
