module.exports = function(grunt) {

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		uglify: {
			options: {
				banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
			},
			build: {
				expand: true,
				ext: '.min.js',
          		cwd: 'js/dev',
				src: '*.js',
				dest: 'js'
			}
		},
		less: {
			build: {
    			files: {
    			  	'css/comment-images-admin.css': 'css/less/comment-images-admin.less',
    			  	'css/comment-images.css': 'css/less/comment-images.less'
    			}
			}
		},
		watch: {
			less: {
				files: ['css/less/*.less'],
				tasks: ['less'],
				options: {
					debounceDelay: 500
				}
			},

			scripts: {
				files: ['js/dev/*.js'],
				tasks: ['uglify'],
				options: {
					debounceDelay: 500
				}
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-watch');

	// Default task(s).
	grunt.registerTask('default', ['uglify', 'less']);

};
