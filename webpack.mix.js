const path = require("path");
const mix = require('laravel-mix');
//const pluginSetUrl = "https://z52g81mkyvmd.c01-17.plentymarkets.com/";
//const pluginSetUrl = 'https://mwzi7q2a40rn.c01-15.plentymarkets.com'
const pluginSetUrl ='https://rlxh5qb8uqve.c01-17.plentymarkets.com'
const pluginPath = "netseasypay";
//4925000000000004

mix
    .js('./resources/js/src/app.js', './resources/js/dist/main.js').vue()
    .sass('./resources/scss/app.scss', './resources/css/app-scss.css')


    .alias({
        Ceres: path.join(__dirname, 'node_modules/Ceres/resources/js/src'),
        
    })
    
    .browserSync({
        port: 80,
        // Enter the remote URL of the plugin on your plentymarkets system
        proxy: pluginSetUrl,

        // Directories to watch for changes
        // The browser refreshes whenever a file in this directory is changed
        files: [
            "resources/js/**",
            "resources/css/**"
        ],

        // Add rewrite rules for CSS and JS
        // This will make it look for CSS and JS files in the plugin directory
        rewriteRules: [
            {
                match: new RegExp("https.*\\/"+ pluginPath +"\\/js\\/(.*.js)", "g"),
                replace: "/resources/js/$1"
            },
            {
                match: new RegExp("https.*\\/"+ pluginPath +"\\/css\\/(.*.css)", "g"),
                replace: "/resources/css/$1"
            }
        ],

        // Instruct Browsersync to provide static resources for JS and CSS files
        // This way, your browser will load the local resources instead of remote ones
        serveStatic: [
            {
                route: ["/resources/js"],
                dir: "resources/js"
            },
            {
                route: ["/resources/css"],
                dir: "resources/css"
            },
            {
                route: ["/resources/scss"],
                dir: "resources/scss"
            },
            {
                route: ["/resources/fonts"],
                dir: "resources/fonts"
            },
            {
                route: ["/resources/documents"],
                dir: "resources/documents"
            },
            {
                route: ["/resources/images"],
                dir: "resources/images"
            }
        ]
    });

