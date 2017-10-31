/* eslint-env node */
module.exports = function ( grunt ) {
	var conf = grunt.file.readJSON( 'skin.json' );

	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-jsonlint' );
	grunt.loadNpmTasks( 'grunt-notify' );
	grunt.loadNpmTasks( 'grunt-stylelint' );

	grunt.initConfig( {
		eslint: {
			all: [
				'**/*.js',
				'!docs/**',
				'!libs/**',
				'!node_modules/**',
				'!vendor/**'
			]
		},
		stylelint: {
			options: {
				syntax: 'less'
			},
			all: [
				'**/*.less',
				'!docs/**',
				'!libs/**',
				'!node_modules/**',
				'!vendor/**'
			]
		},
		jsonlint: {
			all: [
				'**/*.json',
				'!docs/**',
				'!libs/**',
				'!node_modules/**',
				'!vendor/**'
			]
		},
		banana: conf.MessagesDirs,
		watch: {
			lint: {
				files: [ '{resources,tests/qunit}/**/*.{js,less}' ],
				tasks: [ 'lint' ]
			},
			scripts: {
				files: [ '{resources,tests/qunit}/**/*.js' ],
				tasks: [ 'test' ]
			},
			configFiles: {
				files: [ 'Gruntfile.js' ],
				options: {
					reload: true
				}
			}
		}
	} );

	grunt.registerTask( 'lint', [ 'eslint', 'stylelint', 'jsonlint', 'banana' ] );
	grunt.registerTask( 'test', [ 'lint' ] );

	grunt.registerTask( 'default', [ 'test' ] );
};
