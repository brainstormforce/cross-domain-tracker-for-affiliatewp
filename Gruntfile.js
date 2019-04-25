module.exports = function(grunt) {
    // Project configuration
    var autoprefixer = require('autoprefixer');
    var flexibility = require('postcss-flexibility');

	grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        postcss: {
            options: {
                map: false,
                processors: [
                    flexibility,
                    autoprefixer({
                        browsers: [
                            'Android >= 2.1',
                            'Chrome >= 21',
                            'Edge >= 12',
                            'Explorer >= 7',
                            'Firefox >= 17',
                            'Opera >= 12.1',
                            'Safari >= 6.0'
                        ],
                        cascade: false
                    })
                ]
            },
            style: {
                expand: true,
                src: [
                    'assets/css/**.css',
                    '!assets/css/**-rtl.css'
                ]
            }
        },

		
		copy: {
			main: {
				options: {
					mode: true
				},
				src: [
				 '**',
                '!node_modules/**',
                '!build/**',
                '!css/sourcemap/**',
                '!.git/**',
                '!bin/**',
                '!.gitlab-ci.yml',
                '!bin/**',
                '!tests/**',
                '!phpunit.xml.dist',
                '!*.sh',
                '!*.map',
                '!*.zip',
                '!Gruntfile.js',
                '!package.json',
                '!.gitignore',
                '!phpunit.xml',
                '!README.md',
                '!sass/**',
                '!codesniffer.ruleset.xml',
                '!vendor/**',
                '!composer.json',
                '!composer.lock',
                '!package-lock.json',
                '!phpcs.xml.dist',
				],
				dest: 'cross-domain-tracker-for-affiliatewp/'
			}
		},
		compress: {
			main: {
				options: {
					archive: 'cross-domain-tracker-for-affiliatewp-<%= pkg.version %>.zip',

					mode: 'zip'
				},
				files: [
				{
					src: [
					'./cross-domain-tracker-for-affiliatewp/**'
					]

				}
				]
			}
		},
		clean: {
			main: ["cross-domain-tracker-for-affiliatewp"],
			zip: ["*.zip"],
		},
		makepot: {
            target: {
                options: {
                    domainPath: '/',
                    mainFile: 'cross-domain-tracker-for-affiliatewp.php',
                    potFilename: 'languages/cross-domain-tracker-for-affiliatewp.pot',
                    potHeaders: {
                        poedit: true,
                        'x-poedit-keywordslist': true
                    },
                    type: 'wp-plugin',
                    updateTimestamp: true
                }
            }
        },
        addtextdomain: {
            options: {
                textdomain: 'affiliatewp-external-visits',
                updateDomains: true
            },
            target: {
                files: {
                    src: ['*.php', '**/*.php', '!node_modules/**', '!php-tests/**', '!bin/**', '!admin/bsf-core/**', '!woocommerce/**']
                }
            }
        },

        bumpup: {
            options: {
                updateProps: {
                    pkg: 'package.json'
                }
            },
            file: 'package.json'
        },

        replace: {
            plugin_main: {
                src: ['cross-domain-tracker-for-affiliatewp.php'],
                overwrite: true,
                replacements: [
                    {
                        from: /Version: \bv?(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)(?:-[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?(?:\+[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\b/g,
                        to: 'Version: <%= pkg.version %>'
                    }
                ]
            },
	

            plugin_const: {
                src: ['includes/class-affiliate-wp-track-external-visits.php'],
                overwrite: true,
                replacements: [
                    {
                        from: /CDTAWP_VERSION', '.*?'/g,
                        to: 'CDTAWP_VERSION\', \'<%= pkg.version %>\''
                    }
                ]
            }
        },
		
        uglify: {
            js: {
                options: {
                    compress: {
                        drop_console: true // <-
                    }
                },
                files: [{
                    expand: true,
                    cwd: "assets/js",
                    src: ["*.js"],
                    dest: "assets/min-js",
                    ext: '.min.js'
                }]

            }
        }
	});

    // Load grunt tasks

    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-wp-i18n');
    grunt.loadNpmTasks('grunt-bumpup');
    grunt.loadNpmTasks('grunt-text-replace');
    grunt.loadNpmTasks('grunt-postcss');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    // Autoprefix
    grunt.registerTask('style', ['postcss:style']);
    grunt.registerTask('release', ['clean:zip', 'copy','compress','clean:main']);
    grunt.registerTask('i18n', ['addtextdomain', 'makepot']);

    // min all
    grunt.registerTask( 'minify', [ 'style', 'rtlcss', 'cssmin:css', 'uglify:js' ] );

    // Bump Version - `grunt version-bump --ver=<version-number>`
    grunt.registerTask('version-bump', function (ver) {

        var newVersion = grunt.option('ver');

        if (newVersion) {
            newVersion = newVersion ? newVersion : 'patch';

            grunt.task.run('bumpup:' + newVersion);
            grunt.task.run('replace');
        }
    });

};
