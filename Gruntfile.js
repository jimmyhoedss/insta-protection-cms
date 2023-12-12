module.exports = function(grunt) {
	grunt.initConfig({
		//pkg: grunt.file.readJSON('package.json'),
		concurrent: {
			options: {
				logConcurrentOutput: true
			},
			tasks: ['watch:css', 'watch:js']
		},
		sync: {
			jsBackendToCms: {
				files: [{
					cwd: './backend/web/js/',
					src: [
						'**/*.*', /* Include everything */
						'!**/*.zip' /* but exclude zip files */
					],
					dest: './cms/web/js/',
				}],
				pretend: false, // Don't do any IO. Before you run the task with `updateAndDelete` PLEASE MAKE SURE it doesn't remove too much.
				verbose: true // Display log messages when copying files
			}
		},
		/*
		concat: {
			options: {
		        separator: ';',
		    },
			backend: {
                src: ['./backend/web/css/*.scss'],
                dest: './backend/web/css/build.scss2',
                nonull: true
			},
		},
		*/
		sass: {
			dist: {
		      options: {
		        style: 'expanded'
		      },
		      files: {
		      	//'./frontend/web/css/style.css': './frontend/web/css/main.scss',
		        './backend/web/css/style.css': './backend/web/css/main.scss',
		        './cms/web/css/style.css': './backend/web/css/main.scss'
		      }
		    }
		},
		watch: {
			js: {
				files: ['./backend/web/js/**/*.js'],
				tasks: ['sync']
			},
			css: {
				files: ['**/*.scss'],
				tasks: ['sass']
			}
		}
	});
	
	grunt.loadNpmTasks('grunt-concurrent');
	grunt.loadNpmTasks('grunt-sync');
	//grunt.loadNpmTasks('grunt-newer');
	//grunt.loadNpmTasks('grunt-contrib-concat');
	//grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.registerTask('js',['watch:js']);
	grunt.registerTask('default',['concurrent']);
}